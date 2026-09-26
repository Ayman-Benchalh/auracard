<!DOCTYPE html>
<html lang="{{ $locale ?? 'fr' }}" dir="{{ $direction ?? 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'AURA CARD') }}</title>

        <!-- AURA CARD Theme Bootstrap: Prevent theme flash & enforce Light default -->
        <script>
            (function() {
                try {
                    if (window.location.pathname.startsWith('/p/')) {
                        return;
                    }
                    var saved = localStorage.getItem('aura-theme');
                    if (saved === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        if (!saved) {
                            localStorage.setItem('aura-theme', 'light');
                        }
                    }
                } catch (e) {}
            })();
        </script>

        <!-- Google Fonts: Plus Jakarta Sans (Latin/FR/EN) + Tajawal (Arabic) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-[#F7F8FC] dark:bg-[#0B1020] text-[#111827] dark:text-slate-100 min-h-screen">
        @inertia
    </body>
</html>
