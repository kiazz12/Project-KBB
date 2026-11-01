<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Formulir Online KBB') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/kbb-logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">

    <script>
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/vilt.css', 'resources/js/vilt/app.js'])
    @inertiaHead
</head>
<body class="h-full font-sans">
    @inertia
</body>
</html>
