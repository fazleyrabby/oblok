<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    {{ $alertEvent->subject }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Triggered by {{ $alertEvent->alertRule?->name ?? 'a deleted rule' }} &middot; {{ $alertEvent->triggered_at->diffForHumans() }}</p>
            </div>
            <a href="{{ route('projects.alerts.index', $project) }}" class="px-3 py-1.5 bg-gray-800 text-gray-300 rounded-lg text-xs font-semibold uppercase tracking-wider hover:bg-gray-700 transition">
                &larr; Back to Alerts
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Event Summary -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $alertEvent->severity->color() }}">
                    {{ $alertEvent->severity->label() }}
                </span>
                <span class="text-xs text-gray-400">Project: {{ $project->name }}</span>
            </div>
            @if($alertEvent->context)
                <h3 class="text-sm font-semibold text-gray-300 mb-2">Alert Context</h3>
                <pre class="bg-gray-950 border border-gray-800 rounded-lg p-4 text-xs font-mono text-gray-300 overflow-x-auto">{{ json_encode($alertEvent->context, JSON_PRETTY_PRINT) }}</pre>
            @endif
        </div>

        <!-- Deliveries -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-4">Notification Deliveries</h3>
            @if($alertEvent->deliveries->isEmpty())
                <div class="text-center py-8 border border-dashed border-gray-800 rounded-lg">
                    <p class="text-sm text-gray-400">No delivery was created for this alert event.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Channel</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Attempts</th>
                                <th class="py-3 px-4">Delivered At</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($alertEvent->deliveries as $delivery)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4 text-xs font-mono text-gray-300">{{ $delivery->channel?->name ?? '—' }}</td>
                                    <td class="py-3 px-4">
                                        @if($delivery->status->value === 'delivered')
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">Delivered</span>
                                        @elseif($delivery->status->value === 'failed')
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20 rounded-full">Failed</span>
                                        @elseif($delivery->status->value === 'acknowledged')
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-full">Acknowledged</span>
                                        @elseif($delivery->status->value === 'snoozed')
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full">Snoozed</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-gray-800 text-gray-300 border border-gray-700 rounded-full animate-pulse">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $delivery->attempts }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $delivery->delivered_at?->diffForHumans() ?? '—' }}</td>
                                    <td class="py-3 px-4 text-right">
                                        @if($delivery->status->isActionable() && $delivery->status->value !== 'snoozed')
                                            <form method="POST" action="{{ route('projects.alerts.acknowledge', [$project, $alertEvent, $delivery]) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 px-2 py-1 rounded hover:bg-emerald-500/10">
                                                    Acknowledge
                                                </button>
                                            </form>
                                        @endif
                                        @if($delivery->status->isActionable())
                                            <form method="POST" action="{{ route('projects.alerts.snooze', [$project, $alertEvent, $delivery]) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="until" value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}">
                                                <button type="submit" class="text-xs font-semibold text-amber-400 hover:text-amber-300 px-2 py-1 rounded hover:bg-amber-500/10">
                                                    Snooze 2h
                                                </button>
                                            </form>
                                        @endif
                                        @if($delivery->status->value === 'failed' && $delivery->last_error)
                                            <span class="block text-xs text-red-400 mt-1 max-w-xs truncate" title="{{ $delivery->last_error }}">{{ $delivery->last_error }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
