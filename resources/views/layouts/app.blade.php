<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Atlas Developer Platform') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-900 text-gray-100 min-h-screen">
        <div x-data="{ sidebarCollapsed: localStorage.getItem('atlas_sidebar_collapsed') === 'true', animated: false }"
             x-init="$watch('sidebarCollapsed', val => localStorage.setItem('atlas_sidebar_collapsed', val)); setTimeout(() => animated = true, 100)"
             class="min-h-screen flex bg-gray-950">

            <!-- Persistent Sidebar Component -->
            @include('layouts.sidebar')

            <!-- Main Layout Wrapper (statically md:ml-64 with delayed transition to prevent refresh jump) -->
            <div :class="{ 'transition-all duration-200 ease-in-out': animated, 'md:ml-20': sidebarCollapsed, 'md:ml-64': !sidebarCollapsed }"
                 class="flex-1 flex flex-col min-w-0 md:ml-64">

                <!-- Top Navbar -->
                @include('layouts.navigation')

                <!-- Page Header -->
                @isset($header)
                    <header class="bg-gray-900 border-b border-gray-800">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
