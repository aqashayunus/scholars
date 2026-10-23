<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Olympian Scholars') }}</title>
        
        {{-- Favicon --}}
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ time() }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ time() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}?v={{ time() }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ time() }}">

        <!-- Fonts & Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-surface font-sans text-charcoal antialiased min-h-screen flex items-center justify-center py-12 px-4 selection:bg-primary-100 selection:text-primary">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-3 group">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="{{ config('app.name', 'Olympian Scholars') }}" class="h-20 w-20 object-contain transition-transform group-hover:scale-105 drop-shadow-sm">
                </a>
            </div>

            <div class="card relative overflow-hidden border border-gray-100/80 shadow-card">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-600 via-primary to-accent"></div>
                {{ $slot }}
            </div>

            <div class="text-center text-xs text-muted mt-6">
                <a href="{{ route('home') }}" class="hover:text-primary transition-colors">
                    ← Back to Homepage
                </a>
            </div>
        </div>
    </body>
</html>
