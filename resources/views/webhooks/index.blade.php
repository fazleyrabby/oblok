<x-app-layout>
    <x-slot name="header">
    <div class="mb-4">
        <x-project-switcher :projects="$projects" :current="$project" :route="'projects.webhooks.index'" />
    </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Webhooks for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Incoming webhook deliveries captured with full payloads</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($webhookCalls->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <h3 class="mt-3 text-base font-semibold text-gray-200">No webhooks captured</h3>
                <p class="mt-1 text-sm text-gray-400">Incoming webhook requests sent to this project will appear here.</p>
                <p class="mt-4 text-xs font-mono text-gray-500">POST /api/v1/webhooks/deployments/{{ $project->slug }}</p>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Event</th>
                                <th class="py-3 px-4">Method</th>
                                <th class="py-3 px-4">Endpoint</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Time</th>
                                <th class="py-3 px-4">Replays</th>
                                <th class="py-3 px-4">Received</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($webhookCalls as $call)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $call->event === 'deployment' ? 'text-indigo-400 border-indigo-900 bg-indigo-950' : 'text-gray-400 border-gray-800' }}">
                                            {{ $call->event ?? 'webhook' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs {{ $call->method === 'POST' ? 'text-emerald-400' : 'text-gray-400' }}">{{ $call->method }}</td>
                                    <td class="py-3 px-4 font-mono text-xs text-gray-400 truncate max-w-xs">{{ $call->url ?? '—' }}</td>
                                    <td class="py-3 px-4">
                                        @if($call->status_code === null)
                                            <span class="text-xs text-gray-500">—</span>
                                        @else
                                            <span class="text-xs font-semibold {{ $call->status_code < 400 ? 'text-emerald-400' : 'text-rose-400' }}">
                                                {{ $call->status_code }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $call->processing_time_ms !== null ? $call->processing_time_ms.'ms' : '—' }}</td>
                                    <td class="py-3 px-4">
                                        @if($call->replay_count > 0)
                                            <span class="text-xs font-semibold text-amber-400">{{ $call->replay_count }}×</span>
                                        @else
                                            <span class="text-xs text-gray-500">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $call->created_at->diffForHumans() }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('projects.webhooks.show', [$project, $call]) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                            Inspect &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $webhookCalls->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
