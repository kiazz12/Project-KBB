<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KBB Admin') — Panel Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
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
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    Users
                </a>
                <a href="{{ route('admin.forms.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.forms.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Forms
                </a>
            </nav>
            <div class="border-t border-gray-100 px-4 py-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</div>
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
            <div class="max-w-7xl mx-auto px-6 py-8">
                @if (session('success'))
                    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    @else
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-900">
            @yield('content')
        </div>
    @endif
</body>
</html>
