<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Olympian Scholars' }} | Malaysia's Premium Digital Learning Platform</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ time() }}">

    <style>
        [x-cloak] { display: none !important; }
        select.appearance-none, select[class*="appearance-none"] { background-image: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-charcoal overflow-x-hidden antialiased relative">

    {{-- ── NAVBAR ── --}}
    <x-navbar />

    {{-- ── MAIN CONTENT ── --}}
    <main class="overflow-hidden">
        {{ $slot }}
    </main>

    {{-- ── FOOTER ── --}}
    <x-footer />

    {{-- ── GLOBAL SUBJECT PREVIEW POPUP MODAL ── --}}
    <x-subject-modal />

    @auth
        <x-floating-chatbox />
    @endauth

    {{-- Chart.js CDN (used on public pages that need it) --}}
    @stack('scripts')
</body>
</html>
