<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Incidents for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Operational outages and incident management log</p>
            </div>
            <a href="{{ route('projects.incidents.create', $project) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                + Log New Incident
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($incidents->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-3 text-base font-semibold text-gray-200">No active or past incidents</h3>
                <p class="mt-1 text-sm text-gray-400">All services and systems are operating normally.</p>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Severity</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Incident Title</th>
                                <th class="py-3 px-4">Associated Service</th>
                                <th class="py-3 px-4">Started At</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($incidents as $incident)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4">
                                        @if($incident->severity === 'critical')
                                            <span class="px-2.5 py-1 text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/20 rounded-full uppercase">
                                                Critical
                                            </span>
                                        @elseif($incident->severity === 'high')
                                            <span class="px-2.5 py-1 text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full uppercase">
                                                High
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-bold bg-gray-500/10 text-gray-300 border border-gray-500/20 rounded-full uppercase">
                                                {{ $incident->severity }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($incident->isResolved())
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">
                                                Resolved
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20 rounded-full animate-pulse">
                                                {{ ucfirst($incident->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-gray-200">
                                        <a href="{{ route('projects.incidents.show', [$project, $incident]) }}" class="hover:text-indigo-400">
                                            {{ $incident->title }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">
                                        {{ $incident->service?->name ?? 'System-wide' }}
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $incident->started_at->diffForHumans() }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('projects.incidents.show', [$project, $incident]) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                            Manage &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $incidents->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
