<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Formulir Online KBB</title>
    <link rel="icon" type="image/png" href="{{ asset('images/kbb-logo.png') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        kbb: { '50': '#eef3ff', '100': '#dce5f5', '200': '#b8cceb', '300': '#8aa8db', '400': '#5a83c8', '500': '#3a6bb5', '600': '#1a4a8a', '700': '#003778', '800': '#002a5c', '900': '#001e42', '950': '#00122a' },
                        gold: { '50': '#fdf8ed', '100': '#f9efd0', '200': '#f2dda0', '300': '#e9c76b', '400': '#C8A45C', '500': '#b8913e', '600': '#a07d30', '700': '#856928', '800': '#6d5422', '900': '#5a451c' },
                    },
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>[wire\:loading], [wire\:loading\.delay] { display: none; }</style>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside id="sidebar" class="w-64 bg-white border-r border-gray-200 flex flex-col flex-shrink-0 transition-all ml-0">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100">
                <img src="{{ asset('images/kbb-logo.png') }}" alt="KBB" class="w-8 h-8">
                <div>
                    <h2 class="font-semibold text-sm leading-tight">Formulir Online KBB</h2>
                    <p class="text-xs text-gray-400">{{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</p>
                </div>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('dashboard') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('forms.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('forms.*') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Forms
                </a>
                @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('users.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('users.*') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    Users
                </a>
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.*') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Admin Panel
                </a>
                <a href="{{ route('notifications.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('notifications.*') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifikasi
                </a>
                @endif
                <a href="{{ route('change-password') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('change-password') ? 'bg-kbb-50 text-kbb-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Ubah Password
                </a>
            </nav>
            <div class="border-t border-gray-100 px-4 py-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full bg-kbb-700 flex items-center justify-center text-white text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-red-500 hover:text-red-600 hover:bg-red-50 px-2 py-1.5 rounded transition">Logout</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto">
            <div class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b border-gray-200 px-6 py-3 flex items-center gap-4">
                <button onclick="document.getElementById('sidebar').classList.toggle('-ml-64')" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-900 flex-1">@yield('title', 'Dashboard')</h1>
                @auth
                    @livewire('notification-bell')
                @endauth
            </div>
            <div class="max-w-7xl mx-auto px-6 py-8">
                @if (session('success'))
                    <div id="flash-success" class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">
                        {{ session('success') }}
                        <button onclick="this.parentElement.remove()" class="float-right">&times;</button>
                    </div>
                    <script>setTimeout(() => { const e = document.getElementById('flash-success'); if(e) e.remove(); }, 5000);</script>
                @endif
                @if (session('error'))
                    <div id="flash-error" class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        {{ session('error') }}
                        <button onclick="this.parentElement.remove()" class="float-right">&times;</button>
                    </div>
                    <script>setTimeout(() => { const e = document.getElementById('flash-error'); if(e) e.remove(); }, 5000);</script>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    @if (session('sessions_terminated'))
        <div id="sessions-terminated-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center animate-bounce-in">
                <div class="w-14 h-14 mx-auto mb-4 bg-amber-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1112 3a9 9 0 017.364 4.636z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Session Diterminasi</h3>
                <p class="text-sm text-gray-600 mb-6">{{ session('sessions_terminated') }}</p>
                <button onclick="this.closest('#sessions-terminated-modal').remove()" class="w-full bg-kbb-700 hover:bg-kbb-800 text-white font-medium py-2.5 rounded-lg transition">Mengerti</button>
            </div>
        </div>
        <style>
            @keyframes bounceIn { 0%{transform:scale(0.9);opacity:0} 50%{transform:scale(1.02)} 100%{transform:scale(1);opacity:1} }
            .animate-bounce-in { animation: bounceIn 0.3s ease-out; }
        </style>
    @endif

    @livewireScripts
</body>
</html>
