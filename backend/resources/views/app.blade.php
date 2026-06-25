<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title inertia>{{ config('app.name', 'KBB Forms') }}</title>
    <link rel="icon" type="image/png" href="/images/kbb-logo.png" />
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script>if (window.matchMedia('(prefers-color-scheme:dark)').matches) document.documentElement.classList.add('dark')</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              kbb: {
                '50': '#eef3ff', '100': '#dce5f5', '200': '#b8cceb',
                '300': '#8aa8db', '400': '#5a83c8', '500': '#3a6bb5',
                '600': '#1a4a8a', '700': '#003778', '800': '#002a5c',
                '900': '#001e42', '950': '#00122a',
              },
              gold: {
                '50': '#fdf8ed', '100': '#f9efd0', '200': '#f2dda0',
                '300': '#e9c76b', '400': '#C8A45C', '500': '#b8913e',
                '600': '#a07d30', '700': '#856928', '800': '#6d5422',
                '900': '#5a451c',
              },
            },
            fontFamily: {
              sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
          },
        },
      }
    </script>
    <style>
      [type="text"]:focus, [type="email"]:focus, [type="password"]:focus, select:focus, textarea:focus {
        --tw-ring-color: #003778;
        border-color: #003778;
      }
    </style>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @inertiaHead
  </head>
  <body>
    @inertia
  </body>
</html>
