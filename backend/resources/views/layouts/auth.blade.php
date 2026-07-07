<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') — Formulir Online KBB</title>
    <link rel="icon" type="image/png" href="{{ asset('images/kbb-logo.png') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script>
        tailwind.config = {
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
    <style>
        .blob-1 { animation: float1 20s ease-in-out infinite; }
        .blob-2 { animation: float2 25s ease-in-out infinite; }
        .blob-3 { animation: float3 18s ease-in-out infinite; }
        .blob-4 { animation: float1 22s ease-in-out infinite reverse; }
        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -40px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-40px, -20px) scale(1.15); }
            66% { transform: translate(30px, 30px) scale(0.85); }
        }
        @keyframes float3 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(20px, 40px) scale(0.9); }
            66% { transform: translate(-30px, -10px) scale(1.1); }
        }
    </style>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-kbb-600 via-kbb-700 to-kbb-900 overflow-hidden relative">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="blob-1 absolute -top-32 -left-32 w-[500px] h-[500px] rounded-full bg-gradient-to-br from-kbb-400/20 to-transparent blur-3xl"></div>
        <div class="blob-2 absolute -bottom-40 -right-32 w-[600px] h-[600px] rounded-full bg-gradient-to-br from-gold-400/15 to-transparent blur-3xl"></div>
        <div class="blob-3 absolute top-1/3 -right-20 w-[400px] h-[400px] rounded-full bg-gradient-to-br from-kbb-300/15 to-transparent blur-3xl"></div>
        <div class="blob-4 absolute bottom-1/4 -left-20 w-[350px] h-[350px] rounded-full bg-gradient-to-br from-gold-300/10 to-transparent blur-3xl"></div>
    </div>
    @yield('content')
    @livewireScripts
</body>
</html>
