<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Alerts for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Alert events fired by rules and their notification deliveries</p>
            </div>
            <a href="{{ route('projects.alert-rules.index', $project) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                Manage Rules
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($events->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-3 text-base font-semibold text-gray-200">No alerts fired</h3>
                <p class="mt-1 text-sm text-gray-400">Alert rules have not breached any thresholds for this project.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.alert-rules.create', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-wider">
                        + Create Alert Rule
                    </a>
                </div>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Severity</th>
                                <th class="py-3 px-4">Alert</th>
                                <th class="py-3 px-4">Rule</th>
                                <th class="py-3 px-4">Deliveries</th>
                                <th class="py-3 px-4">Triggered At</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($events as $event)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $event->severity->color() }}">
                                            {{ $event->severity->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-gray-200">
                                        <a href="{{ route('projects.alerts.show', [$project, $event]) }}" class="hover:text-indigo-400">
                                            {{ $event->subject }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $event->alertRule?->name ?? '—' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="text-xs font-mono {{ $event->deliveries->contains(fn ($d) => $d->status->isActionable()) ? 'text-amber-400' : 'text-emerald-400' }}">
                                            {{ $event->deliveries->count() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $event->triggered_at->diffForHumans() }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('projects.alerts.show', [$project, $event]) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                            View &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
