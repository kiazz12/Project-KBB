<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditService;
use App\Services\NotificationService;
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
            $request->session()->put('last_activity_at', now()->timestamp);

            if (! SessionLimitService::canLogin($user)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                /** @var User $user */
                $max = $user->isSuperAdmin() ? 1 : 3;

                return back()->with('login_limit_error', "Anda telah mencapai batas maksimal {$max} session login. Silakan logout dari perangkat lain terlebih dahulu.");
            }

            $deletedSessions = SessionLimitService::limit($user);

            if ($deletedSessions > 0) {
                session()->flash('sessions_terminated', "{$deletedSessions} session lama telah diterminasi karena login dari perangkat baru.");
            }

            $userSession = UserSession::create([
                'user_id' => $user->id,
                'display_name' => '',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_in_at' => now(),
            ]);
            session()->put('user_session_id', $userSession->id);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $rules = [
            'new_password' => 'required|string|min:8|confirmed',
        ];

        if (! $user->isSuperAdmin()) {
            $rules['current_password'] = 'required|string';
        }

        $request->validate($rules);

        if (! $user->isSuperAdmin()) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
            }
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        AuditService::log('password.changed', $user, "User '{$user->name}' changed password");
        NotificationService::notifySuperAdmins('password_changed', "mengubah password akun mereka.");

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function logout(Request $request)
    {
        $sessionId = session('user_session_id');
        if ($sessionId) {
            UserSession::where('id', $sessionId)->update(['logged_out_at' => now()]);
        }

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
