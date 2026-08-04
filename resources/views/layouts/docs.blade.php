<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ isset($title) ? $title.' · ' : '' }}oblok Docs</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-100 antialiased bg-gray-950 min-h-screen">
        <header class="border-b border-gray-800 bg-gray-900/60 backdrop-blur sticky top-0 z-10">
            <div class="w-full max-w-7xl mx-auto px-5 lg:px-8 h-16 flex items-center justify-between gap-4">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                    <span class="w-7 h-7 bg-indigo-600 rounded-md flex items-center justify-center font-extrabold text-white text-sm">O</span>
                    <span class="font-semibold tracking-tight">oblok</span>
                    <span class="text-gray-600">/</span>
                    <span class="text-gray-400 text-sm">Docs</span>
                </a>
                <nav class="flex items-center gap-5 text-sm">
                    <a href="https://github.com/fazleyrabby/oblok" target="_blank" rel="noopener" class="text-gray-400 hover:text-gray-200 transition">GitHub</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-200 transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-400 hover:text-gray-200 transition">Log in</a>
                    @endauth
                </nav>
            </div>
        </header>

        <div class="w-full max-w-7xl mx-auto px-5 lg:px-8 grid grid-cols-1 md:grid-cols-[16rem_1fr] gap-10 py-10">
            <aside class="md:sticky md:top-24 md:self-start">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Documentation</p>
                <nav class="flex flex-col gap-1">
                    <a href="{{ route('docs.index') }}"
                       class="rounded-md px-3 py-2 text-sm transition {{ !isset($current) ? 'bg-gray-800 text-gray-100' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900' }}">
                        Overview
                    </a>
                    @foreach(config('docs.pages') as $slug => $page)
                        <a href="{{ route('docs.show', $slug) }}"
                           class="rounded-md px-3 py-2 text-sm transition {{ (isset($current) && $current === $slug) ? 'bg-gray-800 text-gray-100' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900' }}">
                            {{ $page['title'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <main class="min-w-0">
                @yield('content')
            </main>
        </div>

        <style>
            .docs-prose { color: #d1d5db; line-height: 1.7; font-size: 0.95rem; }
            .docs-prose h1 { font-size: 1.875rem; font-weight: 700; color: #f3f4f6; margin: 0 0 1rem; letter-spacing: -0.01em; }
            .docs-prose h2 { font-size: 1.4rem; font-weight: 600; color: #e5e7eb; margin: 2.25rem 0 0.85rem; padding-top: 0.5rem; border-top: 1px solid #1f2937; }
            .docs-prose h3 { font-size: 1.125rem; font-weight: 600; color: #e5e7eb; margin: 1.75rem 0 0.6rem; }
            .docs-prose p { margin: 0.85rem 0; }
            .docs-prose a { color: #2dd4bf; text-decoration: underline; text-underline-offset: 2px; }
            .docs-prose a:hover { color: #5eead4; }
            .docs-prose ul, .docs-prose ol { margin: 0.85rem 0; padding-left: 1.4rem; }
            .docs-prose ul { list-style: disc; }
            .docs-prose ol { list-style: decimal; }
            .docs-prose li { margin: 0.35rem 0; }
            .docs-prose strong { color: #f3f4f6; font-weight: 600; }
            .docs-prose code { font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 0.85em; background: #111827; border: 1px solid #1f2937; border-radius: 0.3rem; padding: 0.1rem 0.35rem; color: #e5e7eb; }
            .docs-prose pre { background: #0b0f17; border: 1px solid #1f2937; border-radius: 0.6rem; padding: 1rem 1.1rem; overflow-x: auto; margin: 1.1rem 0; }
            .docs-prose pre code { background: transparent; border: 0; padding: 0; color: #cbd5e1; font-size: 0.85rem; line-height: 1.6; }
            .docs-prose blockquote { border-left: 3px solid #0f766e; background: #0b1413; padding: 0.6rem 1rem; margin: 1.1rem 0; border-radius: 0 0.4rem 0.4rem 0; color: #9ca3af; }
            .docs-prose table { width: 100%; border-collapse: collapse; margin: 1.1rem 0; font-size: 0.875rem; }
            .docs-prose th, .docs-prose td { border: 1px solid #1f2937; padding: 0.5rem 0.75rem; text-align: left; }
            .docs-prose th { background: #111827; color: #e5e7eb; font-weight: 600; }
            .docs-prose hr { border: 0; border-top: 1px solid #1f2937; margin: 2rem 0; }
            .docs-prose > :first-child { margin-top: 0; }
        </style>
    </body>
</html>
