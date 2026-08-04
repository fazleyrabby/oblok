<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    {{ $incident->title }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Project: {{ $project->name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @unless($incident->isResolved())
                    <form method="POST" action="{{ route('projects.incidents.resolve', [$project, $incident]) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                            ✓ Mark as Resolved
                        </button>
                    </form>
                @endunless
                <a href="{{ route('projects.incidents.index', $project) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                    &larr; Back to Incidents
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('status'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm rounded-xl">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-4">Incident Overview</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs text-gray-400 mb-6">
                <div>
                    <span class="block text-gray-500 font-medium">Status</span>
                    @if($incident->isResolved())
                        <span class="font-semibold text-emerald-400">Resolved</span>
                    @else
                        <span class="font-semibold text-red-400 animate-pulse">{{ ucfirst($incident->status) }}</span>
                    @endif
                </div>
                <div>
                    <span class="block text-gray-500 font-medium">Severity</span>
                    <span class="font-semibold uppercase text-indigo-400">{{ $incident->severity }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 font-medium">Associated Service</span>
                    <span class="text-gray-300">{{ $incident->service?->name ?? 'System-wide' }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 font-medium">Started At</span>
                    <span class="text-gray-300">{{ $incident->started_at->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-4">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Description & Investigation Notes</h4>
                <div class="p-4 bg-gray-950 border border-gray-850 rounded-lg text-sm text-gray-200 whitespace-pre-wrap">
                    {{ $incident->description ?? 'No detailed description provided.' }}
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm" x-data="incidentSuggestion()">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600/20 text-indigo-300 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider">AI Insight</h3>
                        <p class="text-xs text-gray-500">Hypothesis &amp; next steps from live project context</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="generate()"
                    :disabled="loading"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition flex items-center gap-2"
                >
                    <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    <span x-text="loading ? 'Analyzing…' : (suggestion ? 'Regenerate' : 'Generate')"></span>
                </button>
            </div>

            <div x-show="loading" class="flex items-center gap-2.5 px-1 py-2">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="text-xs text-gray-500 ml-1">Thinking…</span>
            </div>

            <div x-show="!loading && suggestion" x-cloak class="p-4 bg-gray-950 border border-gray-800 rounded-lg text-sm text-gray-200 whitespace-pre-wrap" x-text="suggestion"></div>

            <p x-show="error" x-cloak x-text="error" class="mt-3 text-xs text-red-400"></p>
        </div>
    </div>

    <style>
        .typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background-color: #46e1d5;
            display: inline-block;
            animation: typing-bounce 1.2s infinite ease-in-out;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.15s; }
        .typing-dot:nth-child(3) { animation-delay: 0.3s; }
        @keyframes typing-bounce {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-4px); opacity: 1; }
        }
    </style>

    <script>
        function incidentSuggestion() {
            return {
                loading: false,
                suggestion: null,
                error: null,

                async generate() {
                    if (this.loading) {
                        return;
                    }

                    this.loading = true;
                    this.error = null;

                    try {
                        const response = await fetch(@json(route('projects.incidents.suggest', [$project, $incident])), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            throw new Error(payload.message ?? 'The request failed.');
                        }

                        this.suggestion = payload.data.suggestion;
                    } catch (e) {
                        this.error = e.message || 'Something went wrong.';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
