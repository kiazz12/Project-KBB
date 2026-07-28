<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Services\AuditService;
use App\Services\SessionLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if ($user->role->value !== 'super_admin') {
                Auth::logout();

                return back()->withErrors(['email' => 'Hanya Super Admin yang dapat mengakses panel ini.']);
            }

            $request->session()->regenerate();
            $request->session()->put('last_activity_at', now()->timestamp);

            if (! SessionLimitService::canLogin($user)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->with('login_limit_error', 'Anda telah mencapai batas maksimal 1 session login. Silakan logout dari perangkat lain terlebih dahulu.');
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

            AuditService::log('auth.login', $user, "User '{$user->name}' logged in via admin panel");

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    public function logout(Request $request)
    {
        $sessionId = session('user_session_id');
        if ($sessionId) {
            UserSession::where('id', $sessionId)->update(['logged_out_at' => now()]);
        }

        $user = Auth::user();
        AuditService::log('auth.logout', $user, "User '{$user?->name}' logged out from admin panel");

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $cookieName = config('session.cookie');
        if ($cookieName) {
            cookie()->forget($cookieName);
        }

        return redirect(route('admin.login'))->withCookie(cookie()->forget($cookieName));
    }
}
