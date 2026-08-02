<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Project Atlas') }} — Developer Operations Platform</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white">
        <!-- Top Navigation -->
        <header class="w-full max-w-7xl mx-auto px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-extrabold text-white text-xl shadow-lg shadow-indigo-600/30">
                    A
                </div>
                <span class="font-bold text-xl text-white tracking-tight">Project Atlas</span>
            </div>

            <nav class="flex items-center space-x-4 text-sm font-medium">
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition shadow-sm">
                        Go to Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-gray-300 hover:text-white transition text-xs font-semibold">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition shadow-sm">
                            Get Started
                        </a>
                    @endif
                @endauth
            </nav>
        </header>

        <!-- Main Hero Section -->
        <main class="flex-1 flex items-center justify-center px-6 py-16 lg:px-8">
            <div class="max-w-4xl text-center space-y-8">
                <!-- Pill Badge -->
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-gray-900 border border-gray-800 text-xs font-medium text-indigo-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Self-Hosted Developer Operations Platform</span>
                </div>

                <!-- Hero Headline -->
                <h1 class="text-4xl sm:text-6xl font-extrabold text-white tracking-tight leading-tight">
                    Unified Operations for <br class="hidden sm:inline" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">Modern Backend Services</span>
                </h1>

                <!-- Hero Subtitle -->
                <p class="max-w-2xl mx-auto text-base sm:text-lg text-gray-400 leading-relaxed">
                    Operate service health probes, Horizon queues, CI/CD webhook deployments, real-time log aggregation, and incident alerts in one high-performance dashboard.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/30 transition">
                            Open Console Dashboard &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/30 transition">
                            Sign In to Console
                        </a>
                        <a href="https://github.com/fazleyrabby/project-atlas" target="_blank" class="w-full sm:w-auto px-8 py-3.5 bg-gray-900 hover:bg-gray-800 text-gray-200 border border-gray-800 text-sm font-semibold rounded-xl transition">
                            View on GitHub
                        </a>
                    @endauth
                </div>

                <!-- Core Capability Badges -->
                <div class="pt-12 border-t border-gray-900 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-xs text-gray-400">
                    <div class="p-3 bg-gray-900/60 border border-gray-800/80 rounded-xl text-center">
                        <span class="block text-emerald-400 font-bold mb-1">🟢 Probes</span>
                        Health Checks
                    </div>
                    <div class="p-3 bg-gray-900/60 border border-gray-800/80 rounded-xl text-center">
                        <span class="block text-indigo-400 font-bold mb-1">⚡ Queues</span>
                        Laravel Horizon
                    </div>
                    <div class="p-3 bg-gray-900/60 border border-gray-800/80 rounded-xl text-center">
                        <span class="block text-purple-400 font-bold mb-1">🚀 Webhooks</span>
                        Deploy Tracking
                    </div>
                    <div class="p-3 bg-gray-900/60 border border-gray-800/80 rounded-xl text-center">
                        <span class="block text-amber-400 font-bold mb-1">📋 Inspector</span>
                        Log Aggregation
                    </div>
                    <div class="p-3 bg-gray-900/60 border border-gray-800/80 rounded-xl text-center">
                        <span class="block text-red-400 font-bold mb-1">🚨 Incidents</span>
                        Automated Outages
                    </div>
                    <div class="p-3 bg-gray-900/60 border border-gray-800/80 rounded-xl text-center">
                        <span class="block text-cyan-400 font-bold mb-1">👥 Teams</span>
                        Role Access
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-6 lg:px-8 py-6 border-t border-gray-900 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-2">
            <div>
                &copy; {{ date('Y') }} Project Atlas. Built with Laravel 13 & PHP 8.4.
            </div>
            <div>
                <a href="https://github.com/fazleyrabby/project-atlas" target="_blank" class="hover:text-gray-300 transition">GitHub Repository</a>
            </div>
        </footer>
    </body>
</html>
