<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('admin.login'));
    }
}
