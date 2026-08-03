<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'oblok') }} — Self-Hosted Developer Operations Platform</title>
        <meta name="description" content="oblok is a self-hosted developer operations platform. Service monitoring, deployments, queues, logs, webhooks, and incidents — in one dashboard. No telemetry. No lock-in.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                color-scheme: dark;
            }

            body {
                font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            }

            .font-mono {
                font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
            }

            /* Subtle grid backdrop for the hero */
            .hero-grid {
                background-image:
                    linear-gradient(to right, rgba(148, 163, 184, 0.06) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(148, 163, 184, 0.06) 1px, transparent 1px);
                background-size: 44px 44px;
                mask-image: radial-gradient(ellipse 90% 80% at 50% 0%, #000 55%, transparent 100%);
            }

            /* Load-in entrance for the hero */
            @keyframes fade-up {
                from { opacity: 0; transform: translateY(16px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            .fade-up {
                opacity: 0;
                animation: fade-up 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }

            .delay-1 { animation-delay: 80ms; }
            .delay-2 { animation-delay: 160ms; }
            .delay-3 { animation-delay: 240ms; }
            .delay-4 { animation-delay: 320ms; }

            /* Scroll reveal */
            .reveal {
                opacity: 0;
                transform: translateY(24px);
                transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .reveal.revealed {
                opacity: 1;
                transform: translateY(0);
            }

            @media (prefers-reduced-motion: reduce) {
                .fade-up {
                    opacity: 1;
                    animation: none;
                }

                .reveal {
                    opacity: 1;
                    transform: none;
                    transition: none;
                }

                .animate-ping {
                    animation: none;
                }
            }

            /* Slow, calm status pulse */
            @keyframes pulse-ring {
                0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.35); }
                70%  { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
                100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
            }

            .pulse-dot {
                animation: pulse-ring 2.4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }

            @media (prefers-reduced-motion: reduce) {
                .pulse-dot {
                    animation: none;
                }
            }
        </style>
    </head>

    <body class="bg-gray-950 text-gray-100 antialiased selection:bg-indigo-500 selection:text-white">
        <!-- Top Navigation -->
        <header class="sticky top-0 z-50 h-16 bg-gray-950/80 backdrop-blur border-b border-gray-800/70">
            <div class="w-full max-w-7xl mx-auto px-5 lg:px-8 h-16 flex items-center justify-between gap-4">
                <a href="/" class="flex items-center space-x-2.5" aria-label="oblok home">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center font-extrabold text-white text-base shadow-sm shadow-indigo-500/20">
                        O
                    </div>
                    <span class="font-bold text-lg text-white tracking-tight">oblok</span>
                </a>

                <nav class="flex items-center gap-2">
                    <a href="https://github.com/fazleyrabby/oblok" target="_blank" rel="noopener"
                       class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 text-sm text-gray-300 hover:text-white transition rounded-lg">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4" aria-hidden="true"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                        GitHub
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition active:scale-[0.98]">
                            Go to dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm text-gray-300 hover:text-white transition rounded-lg">
                            Log in
                        </a>
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition active:scale-[0.98]">
                            Open console
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <main>
            <section class="relative overflow-hidden">
                <div class="hero-grid absolute inset-0 pointer-events-none" aria-hidden="true"></div>

                <div class="relative w-full max-w-7xl mx-auto px-5 lg:px-8 pt-14 lg:pt-20 pb-16 lg:pb-24 grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <!-- Copy -->
                    <div class="max-w-xl">
                        <div class="fade-up inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-900 border border-gray-800 text-xs font-medium text-gray-300">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                            </span>
                            Self-hosted · Open source
                        </div>

                        <h1 class="fade-up delay-1 mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.05]">
                            One console for your entire backend.
                        </h1>

                        <p class="fade-up delay-2 mt-6 text-base sm:text-lg text-gray-400 leading-relaxed max-w-[54ch]">
                            Service monitoring, deployments, queues, logs, webhooks, and incidents — unified in a dashboard you host yourself. No telemetry. No lock-in.
                        </p>

                        <div class="fade-up delay-3 mt-8 flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg shadow-sm transition active:scale-[0.98]">
                                Open the console
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 ml-2" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                            <a href="https://github.com/fazleyrabby/oblok" target="_blank" rel="noopener"
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-900 hover:bg-gray-800 text-gray-200 border border-gray-700 text-sm font-semibold rounded-lg transition active:scale-[0.98]">
                                View on GitHub
                            </a>
                        </div>
                    </div>

                    <!-- Real console preview -->
                    <div class="fade-up delay-4 relative">
                        <div class="absolute -inset-px rounded-2xl bg-gradient-to-b from-gray-700/60 to-transparent" aria-hidden="true"></div>
                        <div class="relative rounded-2xl border border-gray-800 bg-gray-950/90 overflow-hidden shadow-2xl shadow-black/40">
                            <!-- Preview window bar -->
                            <div class="flex items-center justify-between px-4 h-11 border-b border-gray-800 bg-gray-900/60">
                                <div class="flex items-center gap-2">
                                    <div class="w-2.5 h-2.5 rounded-full bg-gray-700"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-gray-700"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-gray-700"></div>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500 font-mono">
                                    oblok · acme-prod
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-medium">
                                        <span class="pulse-dot w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Live
                                    </span>
                                </div>
                            </div>

                            <div class="p-4 space-y-4">
                                <!-- Stat strip -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <div class="rounded-xl border border-gray-800 bg-gray-900/50 px-3 py-2.5">
                                        <p class="text-[11px] text-gray-500 font-medium">Services</p>
                                        <p class="text-sm font-bold text-emerald-400 mt-0.5">4/4 healthy</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-800 bg-gray-900/50 px-3 py-2.5">
                                        <p class="text-[11px] text-gray-500 font-medium">Queues</p>
                                        <p class="text-sm font-bold text-indigo-400 mt-0.5">Running</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-800 bg-gray-900/50 px-3 py-2.5">
                                        <p class="text-[11px] text-gray-500 font-medium">Deployments</p>
                                        <p class="text-sm font-bold text-gray-200 mt-0.5">v2.1.4</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-800 bg-gray-900/50 px-3 py-2.5">
                                        <p class="text-[11px] text-gray-500 font-medium">Incidents</p>
                                        <p class="text-sm font-bold text-gray-200 mt-0.5">0 open</p>
                                    </div>
                                </div>

                                <!-- Real chart (bundled ApexCharts) -->
                                <div id="hero-chart" class="rounded-xl border border-gray-800 bg-gray-900/50 px-3 pt-3"></div>

                                <!-- Event feed -->
                                <div class="rounded-xl border border-gray-800 divide-y divide-gray-800/70 overflow-hidden">
                                    <div class="flex items-center gap-3 px-3 py-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                        <span class="text-xs text-gray-300 font-mono truncate">web · health check ok</span>
                                        <span class="ml-auto text-[11px] text-gray-600 font-mono shrink-0">12s ago</span>
                                    </div>
                                    <div class="flex items-center gap-3 px-3 py-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                                        <span class="text-xs text-gray-300 font-mono truncate">worker · queue drained</span>
                                        <span class="ml-auto text-[11px] text-gray-600 font-mono shrink-0">48s ago</span>
                                    </div>
                                    <div class="flex items-center gap-3 px-3 py-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                                        <span class="text-xs text-gray-300 font-mono truncate">deploy · acme-api succeeded</span>
                                        <span class="ml-auto text-[11px] text-gray-600 font-mono shrink-0">3m ago</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Capabilities -->
            <section class="w-full max-w-7xl mx-auto px-5 lg:px-8 py-16 lg:py-24">
                <div class="reveal max-w-2xl">
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
                        Everything your backend needs, in one place
                    </h2>
                    <p class="mt-4 text-gray-400 leading-relaxed">
                        oblok replaces the separate tools teams currently stitch together to monitor, deploy, and operate backend services.
                    </p>
                </div>

                <div class="reveal mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <!-- Spotlight cell -->
                    <div class="lg:col-span-2 rounded-2xl border border-gray-800 bg-gray-900/60 p-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-indigo-600/10 blur-3xl" aria-hidden="true"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-600/15 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                            <h3 class="text-base font-semibold text-white">Service monitoring</h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed max-w-md">
                            Health probes with configurable intervals, response latency, and full status history for every service you run.
                        </p>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="flex -space-x-1.5">
                                <span class="w-6 h-6 rounded-full border border-gray-950 bg-emerald-500/90 text-[10px] font-bold flex items-center justify-center text-gray-950">web</span>
                                <span class="w-6 h-6 rounded-full border border-gray-950 bg-emerald-500/90 text-[10px] font-bold flex items-center justify-center text-gray-950">wkr</span>
                                <span class="w-6 h-6 rounded-full border border-gray-950 bg-amber-500/90 text-[10px] font-bold flex items-center justify-center text-gray-950">sck</span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-mono">healthy</span>
                                <span class="px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[11px] font-mono">degraded</span>
                                <span class="px-2 py-0.5 rounded-full bg-red-500/10 border border-red-500/20 text-red-400 text-[11px] font-mono">down</span>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduler -->
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-600/15 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><line x1="10" y1="2" x2="14" y2="2"/><line x1="12" y1="14" x2="15" y2="11"/><circle cx="12" cy="14" r="8"/></svg>
                            </div>
                            <h3 class="text-base font-semibold text-white">Scheduled tasks</h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed">
                            See every scheduled task and its recent runs — pass or fail — without grepping crontabs.
                        </p>
                    </div>

                    <!-- Deployments -->
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-600/15 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>
                            </div>
                            <h3 class="text-base font-semibold text-white">Deployments</h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed">
                            Track CI/CD pushes from webhooks — commit, status, and environment, tied to monitoring.
                        </p>
                    </div>

                    <!-- Queues -->
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-600/15 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                            </div>
                            <h3 class="text-base font-semibold text-white">Queues</h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed">
                            Watch queue throughput, pending jobs, and worker load in real time.
                        </p>
                    </div>

                    <!-- Logs -->
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-600/15 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a2 2 0 0 0-2 2v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>
                            </div>
                            <h3 class="text-base font-semibold text-white">Logs</h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed">
                            Aggregate, stream, and inspect application logs from every service.
                        </p>
                    </div>

                    <!-- Webhooks -->
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-600/15 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><path d="M18 16.98h-5.99c-1.1 0-1.95.94-2.48 1.9A4 4 0 0 1 2 17c.01-.7.2-1.4.57-2"/><path d="m6 17 3.13-5.78c.53-.97.1-2.18-.5-3.1a4 4 0 1 1 6.89-4.06"/><path d="m12 6 3.13 5.73C15.66 12.7 16.9 13 18 13a4 4 0 0 1 0 8"/></svg>
                            </div>
                            <h3 class="text-base font-semibold text-white">Webhook inspector</h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed">
                            Inspect inbound payloads, headers, and replay them for debugging.
                        </p>
                    </div>

                    <!-- Incidents -->
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-600/15 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><path d="M7 18v-6a5 5 0 1 1 10 0v6"/><path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z"/><path d="M12 2v1"/><path d="M12 12v6"/></svg>
                            </div>
                            <h3 class="text-base font-semibold text-white">Incidents &amp; alerts</h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed">
                            Open incidents and page your team through Discord, email, or webhooks.
                        </p>
                    </div>

                    <!-- Metrics -->
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-6">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-600/15 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                            </div>
                            <h3 class="text-base font-semibold text-white">Metrics &amp; analytics</h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed">
                            Push metrics or scrape Prometheus-compatible endpoints — charted instantly, alongside request analytics.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Self-hosted trust -->
            <section class="w-full max-w-7xl mx-auto px-5 lg:px-8 pb-16 lg:pb-24">
                <div class="reveal grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-7">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-white">No telemetry. Ever.</h3>
                        <p class="mt-2 text-sm text-gray-400 leading-relaxed">
                            oblok runs entirely inside your infrastructure. No hidden network calls, no data leaving your environment.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-7">
                        <div class="w-10 h-10 rounded-lg bg-indigo-600/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-white">You own the data</h3>
                        <p class="mt-2 text-sm text-gray-400 leading-relaxed">
                            Self-hosted means fully auditable. No vendor lock-in, no per-seat pricing, no third-party SaaS dependency.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-7">
                        <div class="w-10 h-10 rounded-lg bg-indigo-600/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-white">Open source</h3>
                        <p class="mt-2 text-sm text-gray-400 leading-relaxed">
                            Built with Laravel, freely available on GitHub. Deploy it next to your stack in minutes.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Install / consolidate -->
            <section class="w-full max-w-7xl mx-auto px-5 lg:px-8 pb-16 lg:pb-24">
                <div class="reveal grid lg:grid-cols-2 gap-12 lg:gap-16 items-center rounded-2xl border border-gray-800 bg-gray-900/40 p-8 lg:p-12">
                    <div class="min-w-0">
                        <h2 class="text-3xl font-bold text-white tracking-tight">
                            One deployment. One source of truth.
                        </h2>
                        <p class="mt-4 text-gray-400 leading-relaxed max-w-md">
                            Deploy oblok alongside your application stack and immediately get visibility into service health, deployments, queue throughput, and incidents.
                        </p>
                        <ul class="mt-6 space-y-3 text-sm text-gray-300">
                            <li class="flex items-center gap-3">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                API-first — every feature exposes a versioned REST API
                            </li>
                            <li class="flex items-center gap-3">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Team roles and members built in from day one
                            </li>
                            <li class="flex items-center gap-3">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Works with any backend — Laravel, Rails, Django, Node, Go
                            </li>
                        </ul>
                    </div>

                    <div class="min-w-0">
                        <div class="rounded-xl border border-gray-800 bg-gray-950 overflow-hidden">
                            <div class="flex items-center gap-2 px-4 h-10 border-b border-gray-800 bg-gray-900/60">
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-700"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-700"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-700"></span>
                            </div>
                            <pre class="p-5 text-sm leading-relaxed text-gray-300 font-mono overflow-x-auto max-w-full"><span class="text-gray-600"># from your server</span>
<span class="text-indigo-400">git clone</span> <span class="text-emerald-400">https://github.com/fazleyrabby/oblok.git</span>
<span class="text-indigo-400">cd</span> oblok
<span class="text-indigo-400">cp</span> .env.example .env
<span class="text-indigo-400">docker compose</span> up -d

<span class="text-gray-600"># open http://your-host:8080</span></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA band -->
            <section class="w-full max-w-7xl mx-auto px-5 lg:px-8 pb-20 lg:pb-28">
                <div class="reveal relative rounded-2xl border border-gray-800 bg-gray-900/60 overflow-hidden px-8 py-14 text-center">
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-72 h-72 rounded-full bg-indigo-600/10 blur-3xl" aria-hidden="true"></div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
                            Run it in your own infrastructure.
                        </h2>
                        <p class="mt-4 text-gray-400 max-w-xl mx-auto">
                            Self-hosted, open source, and yours. No SaaS account required.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg shadow-sm transition active:scale-[0.98]">
                                Open the console
                            </a>
                            <a href="https://github.com/fazleyrabby/oblok" target="_blank" rel="noopener"
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-900 hover:bg-gray-800 text-gray-200 border border-gray-700 text-sm font-semibold rounded-lg transition active:scale-[0.98]">
                                Star on GitHub
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-900">
            <div class="w-full max-w-7xl mx-auto px-5 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 bg-indigo-600 rounded-md flex items-center justify-center font-extrabold text-white text-[11px]">O</div>
                    <span>&copy; {{ date('Y') }} oblok. Built with Laravel &amp; PHP.</span>
                </div>
                <div class="flex items-center gap-5">
                    <a href="https://github.com/fazleyrabby/oblok" target="_blank" rel="noopener" class="hover:text-gray-300 transition">GitHub</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-gray-300 transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-gray-300 transition">Log in</a>
                    @endauth
                </div>
            </div>
        </footer>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Scroll reveal (respects reduced-motion via CSS)
                const revealEls = document.querySelectorAll('.reveal');
                if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('revealed');
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.12 });
                    revealEls.forEach((el) => observer.observe(el));
                } else {
                    revealEls.forEach((el) => el.classList.add('revealed'));
                }

                // Real chart rendered with the bundled ApexCharts
                if (typeof ApexCharts === 'undefined' || !document.getElementById('hero-chart')) {
                    return;
                }

                const now = Date.now();
                const minutes = 24;
                const data = Array.from({ length: minutes }, (_, i) => ({
                    x: now - (minutes - i) * 60000,
                    y: Math.round(40 + Math.sin(i / 2.6) * 18 + Math.random() * 14),
                }));

                const chart = new ApexCharts(document.getElementById('hero-chart'), {
                    chart: {
                        type: 'area',
                        height: 140,
                        toolbar: { show: false },
                        sparkline: { enabled: false },
                        parentHeightOffset: 0,
                        animations: { enabled: true, easing: 'easeout' },
                        background: 'transparent',
                    },
                    series: [{ name: 'requests / min', data }],
                    colors: ['#6366f1'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 100] },
                    },
                    xaxis: {
                        type: 'datetime',
                        labels: { show: false },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        tooltip: { enabled: false },
                    },
                    yaxis: { labels: { show: false } },
                    grid: { show: false },
                    legend: { show: false },
                    tooltip: {
                        theme: 'dark',
                        x: { show: true, format: 'HH:mm' },
                        y: { formatter: (v) => `${v} req/min` },
                    },
                });
                chart.render();
            });
        </script>
    </body>
</html>
