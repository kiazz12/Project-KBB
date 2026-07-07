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
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-kbb-600 via-kbb-700 to-kbb-900">
    @yield('content')
    @livewireScripts
</body>
</html>
