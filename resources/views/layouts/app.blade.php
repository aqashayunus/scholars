<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Olympian Scholars' }} | Malaysia's Premium Digital Learning Platform</title>
    <style>[x-cloak] { display: none !important; }</style>
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
