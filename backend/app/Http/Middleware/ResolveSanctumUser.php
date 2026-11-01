<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * REA S-03: autentikasi Sanctum opsional untuk route publik.
 *
 * Route submit form publik tidak memakai middleware `auth:sanctum` agar
 * responden anonim tetap bisa mengirim. Middleware ini hanya mencoba
 * membaca bearer token bila ada, sehingga opsi form `require_auth` dan
 * `limit_one_response` kembali berfungsi tanpa memblokir tamu.
 */
class ResolveSanctumUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() && ($plainTextToken = $request->bearerToken())) {
            $accessToken = PersonalAccessToken::findToken($plainTextToken);

            if ($accessToken && $this->isValid($accessToken) && $accessToken->tokenable) {
                $accessToken->forceFill(['last_used_at' => now()])->save();

                $user = $accessToken->tokenable;
                Auth::guard('sanctum')->setUser($user);
                Auth::shouldUse('sanctum');
                $request->setUserResolver(fn () => $user);
            }
        }

        return $next($request);
    }

    private function isValid(PersonalAccessToken $accessToken): bool
    {
        if ($accessToken->expires_at) {
            return $accessToken->expires_at->isFuture();
        }

        $expiration = config('sanctum.expiration');

        return ! $expiration
            || ! $accessToken->created_at
            || $accessToken->created_at->gt(now()->subMinutes($expiration));
    }
}
