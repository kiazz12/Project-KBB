<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class TrackSessionActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && Session::has('last_activity_at')) {
            Session::put('last_activity_at', now()->timestamp);
        }

        return $next($request);
    }
}
