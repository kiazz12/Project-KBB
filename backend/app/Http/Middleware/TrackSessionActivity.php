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
        // REA D-01: hanya navigasi halaman (GET/HEAD/OPTIONS) yang me-refresh
        // idle timer. Sebelumnya semua request — termasuk polling Livewire
        // wire:poll.30s — me-refresh timer sehingga sesi tidak pernah kedaluwarsa.
        if ($request->user()
            && $request->isMethod('GET')
            && ! $request->header('X-Livewire')) {
            Session::put('last_activity_at', now()->timestamp);
        }

        return $next($request);
    }
}
