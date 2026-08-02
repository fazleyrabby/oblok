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
    </div>
</x-app-layout>
