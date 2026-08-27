<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Soukelkom') }} — {{ __('The Local Marketplace Where Everyone Wins') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        @livewireScripts

        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            @auth
                <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    @if (session('success'))
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
                    @endif
                </div>
            @endauth

            @if (session('success') && !auth()->check())
                <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-sm mt-4">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-gray-900 text-gray-400 mt-12">
                <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8 text-center sm:text-left">
                    <p class="text-orange-400 font-black text-lg">SOUK<span class="text-white">ELKOM</span></p>
                    <p class="text-sm mt-1">{{ __('The Local Marketplace Where Everyone Wins — Lebanon & MENA.') }}</p>
                    <p class="text-xs mt-4 text-gray-500">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </body>
</html>
