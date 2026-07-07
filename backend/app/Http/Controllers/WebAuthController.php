<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Services\AuditService;
use App\Services\SessionLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            $request->session()->regenerate();

            if (!SessionLimitService::canLogin($user)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $max = $user->isSuperAdmin() ? 1 : 3;
                return back()->with('login_limit_error', "Anda telah mencapai batas maksimal {$max} session login. Silakan logout dari perangkat lain terlebih dahulu.");
            }

            $deletedSessions = SessionLimitService::limit($user);

            if ($deletedSessions > 0) {
                session()->flash('sessions_terminated', "{$deletedSessions} session lama telah diterminasi karena login dari perangkat baru.");
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        AuditService::log('password.changed', $user, "User '{$user->name}' changed password");

        User::where('role', 'super_admin')->get()->each(fn($sa) =>
            Notification::create([
                'user_id' => $sa->id,
                'type' => 'password_changed',
                'message' => "Admin {$user->name} ({$user->email}) mengubah password akun mereka.",
                'data' => ['admin_id' => $user->id, 'admin_name' => $user->name, 'admin_email' => $user->email],
            ])
        );

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.',
            ]);
        }

        return redirect('/login');
    }
}
