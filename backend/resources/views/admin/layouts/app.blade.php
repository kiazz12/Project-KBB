<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KBB Admin') — Panel Super Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/kbb-logo.png') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=plus+jakarta+Sans:500,600,700,800" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        kbb: { '50': '#eef3ff', '100': '#dce5f5', '200': '#b8cceb', '300': '#8aa8db', '400': '#5a83c8', '500': '#3a6bb5', '600': '#1a4a8a', '700': '#003778', '800': '#002a5c', '900': '#001e42', '950': '#00122a' },
                        gold: { '50': '#fdf8ed', '100': '#f9efd0', '200': '#f2dda0', '300': '#e9c76b', '400': '#C8A45C', '500': '#b8913e', '600': '#a07d30', '700': '#856928', '800': '#6d5422', '900': '#5a451c' },
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        .bg-grid {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.04) 1px, transparent 0);
            background-size: 40px 40px;
        }
        .blob-1 { animation: float1 25s ease-in-out infinite; }
        .blob-2 { animation: float2 30s ease-in-out infinite; }
        .blob-3 { animation: float3 22s ease-in-out infinite; }
        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.5; }
            33% { transform: translate(40px, -50px) scale(1.15); opacity: 0.7; }
            66% { transform: translate(-30px, 30px) scale(0.9); opacity: 0.4; }
        }
        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.4; }
            33% { transform: translate(-50px, -30px) scale(1.2); opacity: 0.6; }
            66% { transform: translate(40px, 40px) scale(0.85); opacity: 0.3; }
        }
        @keyframes float3 {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.3; }
            33% { transform: translate(30px, 50px) scale(1.1); opacity: 0.5; }
            66% { transform: translate(-40px, -20px) scale(0.9); opacity: 0.2; }
        }
    </style>
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    @if (auth()->check())
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col flex-shrink-0">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100">
                <img src="{{ asset('images/kbb-logo.png') }}" alt="KBB" class="w-8 h-8">
                <div>
                    <h2 class="font-semibold text-sm leading-tight">KBB Admin</h2>
                    <p class="text-xs text-gray-400">Panel Super Admin</p>
                </div>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.users.*') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    Users
                </a>
                <a href="{{ route('admin.forms.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.forms.*') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Forms
                </a>
                <hr class="my-2 border-gray-200">
                <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('notifications.*') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifikasi
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Dashboard
                </a>
            </nav>
            <div class="border-t border-gray-100 px-4 py-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full bg-kbb-700 flex items-center justify-center text-white text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">Super Admin</p>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-red-500 hover:text-red-600 px-2 py-1.5 rounded hover:bg-red-50 transition">Logout</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto bg-gray-50">
            <div class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b border-gray-200 px-6 py-3 flex items-center gap-4">
                <h1 class="text-lg font-semibold text-gray-900 flex-1">@yield('title', 'Admin Panel')</h1>
                @auth
                    @livewire('notification-bell')
                @endauth
            </div>
            <div class="max-w-7xl mx-auto px-6 py-8">
                @if (session('success'))
                    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    @else
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-kbb-800 via-[#001a3a] to-kbb-950 overflow-hidden relative">
            <div class="absolute inset-0 bg-grid"></div>
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="blob-1 absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full bg-gradient-to-br from-kbb-500/20 via-transparent to-transparent blur-3xl"></div>
                <div class="blob-2 absolute -bottom-48 -left-40 w-[700px] h-[700px] rounded-full bg-gradient-to-br from-gold-500/8 via-transparent to-transparent blur-3xl"></div>
            </div>
            @yield('content')
        </div>
    @endif

    @if (session('sessions_terminated'))
        <div id="sessions-terminated-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center" style="animation: bIn 0.3s ease-out">
                <div class="w-14 h-14 mx-auto mb-4 bg-amber-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1112 3a9 9 0 017.364 4.636z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Session Diterminasi</h3>
                <p class="text-sm text-gray-600 mb-6">{{ session('sessions_terminated') }}</p>
                <button onclick="this.closest('#sessions-terminated-modal').remove()" class="w-full bg-kbb-700 hover:bg-kbb-800 text-white font-medium py-2.5 rounded-lg transition">Mengerti</button>
            </div>
        </div>
        <style>
            @keyframes bIn { 0%{transform:scale(0.9) translateY(10px);opacity:0} 100%{transform:scale(1) translateY(0);opacity:1} }
        </style>
    @endif

    @livewireScripts
    @auth
        @livewire('session-timeout')
    @endauth
</body>
</html>