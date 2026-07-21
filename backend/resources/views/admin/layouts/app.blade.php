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
    <script>
        (function () {
            try {
                var t = localStorage.getItem('kbb-theme');
                if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    <style>
        [wire\:loading], [wire\:loading\.delay] { display: none; }

        /* Mobile: overlay sidebar */
        #admin-sidebar {
            width: 256px;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            transform: translateX(-100%);
            transition: transform 300ms ease-in-out;
            z-index: 50;
        }
        #admin-sidebar.sidebar-open { transform: translateX(0); }

        /* Desktop: push sidebar (in document flow) */
        @media (min-width: 1024px) {
            #admin-sidebar {
                position: relative;
                transform: none;
                flex-shrink: 0;
                transition: width 300ms ease-in-out, min-width 300ms ease-in-out;
                overflow: hidden;
            }
            #admin-sidebar.sidebar-collapsed {
                width: 0;
                min-width: 0;
                border-right-color: transparent;
            }
        }
    </style>
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
<body class="bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100 antialiased">
    <div wire:loading
         class="fixed inset-0 z-[60] flex items-center justify-center bg-white/40 dark:bg-slate-900/40 backdrop-blur-[1px] pointer-events-none">
        <div class="w-9 h-9 border-2 border-kbb-200 border-t-kbb-700 rounded-full animate-spin"></div>
    </div>
    @if (auth()->check())
    <div class="flex h-screen overflow-hidden">
        <div id="admin-sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeAdminSidebar()"></div>
        <aside id="admin-sidebar" class="bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 flex flex-col flex-shrink-0" aria-hidden="true">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100 dark:border-slate-700">
                <img src="{{ asset('images/kbb-logo.png') }}" alt="KBB" class="w-8 h-8">
                <div>
                    <h2 class="font-semibold text-sm leading-tight">KBB Admin</h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Panel Super Admin</p>
                </div>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-kbb-50 text-kbb-700 font-medium dark:bg-kbb-500/15 dark:text-kbb-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-slate-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.users.*') ? 'bg-kbb-50 text-kbb-700 font-medium dark:bg-kbb-500/15 dark:text-kbb-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-slate-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    Users
                </a>
                <a href="{{ route('admin.forms.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.forms.*') ? 'bg-kbb-50 text-kbb-700 font-medium dark:bg-kbb-500/15 dark:text-kbb-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-slate-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Forms
                </a>
                <hr class="my-2 border-gray-200 dark:border-slate-700">
                <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('notifications.*') ? 'bg-kbb-50 text-kbb-700 font-medium dark:bg-kbb-500/15 dark:text-kbb-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-slate-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifikasi
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-slate-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Dashboard
                </a>
            </nav>
            <div class="border-t border-gray-100 dark:border-slate-700 px-4 py-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full bg-kbb-700 flex items-center justify-center text-white text-sm font-semibold">{{ substr(auth()->user()->currentDisplayName(), 0, 1) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate dark:text-gray-100">{{ auth()->user()->currentDisplayName() }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">Super Admin</p>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-red-500 hover:text-red-600 px-2 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-500/10 transition">Logout</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-slate-900 min-w-0">
            <div class="sticky top-0 z-10 bg-white/80 dark:bg-slate-900/85 backdrop-blur border-b border-gray-200 dark:border-slate-700 px-4 sm:px-6 py-3 flex items-center gap-4">
                <button id="admin-sidebar-toggle" type="button" class="text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex-1 min-w-0 truncate">@yield('title', 'Admin Panel')</h1>
                <button onclick="toggleTheme()" title="Ubah tema" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-lg hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                    <svg id="theme-icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg id="theme-icon-moon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                @auth
                    @livewire('notification-bell')
                @endauth
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
                <x-toast />
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleTheme() {
            const root = document.documentElement;
            const isDark = root.classList.toggle('dark');
            try { localStorage.setItem('kbb-theme', isDark ? 'dark' : 'light'); } catch (e) {}
            syncThemeIcons();
        }
        function syncThemeIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            const sun = document.getElementById('theme-icon-sun');
            const moon = document.getElementById('theme-icon-moon');
            if (sun && moon) {
                sun.classList.toggle('hidden', !isDark);
                moon.classList.toggle('hidden', isDark);
            }
        }
        document.addEventListener('DOMContentLoaded', syncThemeIcons);

        function toggleAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');
            const isDesktop = window.innerWidth >= 1024;
            if (isDesktop) {
                const collapsed = sidebar.classList.toggle('sidebar-collapsed');
                sidebar.setAttribute('aria-hidden', collapsed ? 'true' : 'false');
            } else {
                const isOpen = sidebar.classList.contains('sidebar-open');
                if (isOpen) {
                    closeAdminSidebar();
                } else {
                    sidebar.classList.add('sidebar-open');
                    overlay.classList.remove('hidden');
                    sidebar.setAttribute('aria-hidden', 'false');
                }
            }
        }
        function closeAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');
            sidebar.classList.remove('sidebar-open');
            overlay.classList.add('hidden');
            sidebar.setAttribute('aria-hidden', 'true');
        }
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');
            if (window.innerWidth >= 1024) {
                overlay.classList.add('hidden');
                sidebar.classList.remove('sidebar-open');
                const collapsed = sidebar.classList.contains('sidebar-collapsed');
                sidebar.setAttribute('aria-hidden', collapsed ? 'true' : 'false');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.setAttribute('aria-hidden', sidebar.classList.contains('sidebar-open') ? 'false' : 'true');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('admin-sidebar-toggle');
            if (btn) btn.addEventListener('click', toggleAdminSidebar);
            const overlay = document.getElementById('admin-sidebar-overlay');
            if (overlay) overlay.addEventListener('click', closeAdminSidebar);
        });
    </script>
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
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center" style="animation: bIn 0.3s ease-out">
                <div class="w-14 h-14 mx-auto mb-4 bg-amber-100 dark:bg-amber-500/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1112 3a9 9 0 017.364 4.636z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Session Diterminasi</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">{{ session('sessions_terminated') }}</p>
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
        @livewire('display-name-prompt')
    @endauth
</body>
</html>