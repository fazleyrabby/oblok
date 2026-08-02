<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Webhook Delivery
                </h2>
                <p class="text-xs text-gray-400 mt-1 font-mono">{{ $webhookCall->method }} {{ $webhookCall->url ?? '—' }}</p>
            </div>
            <a href="{{ route('projects.webhooks.index', $project) }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                &larr; Back
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Event</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-200">{{ $webhookCall->event ?? 'webhook' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</dt>
                    <dd class="mt-1 font-mono text-xs {{ $webhookCall->status_code !== null && $webhookCall->status_code < 400 ? 'text-emerald-400' : 'text-gray-200' }}">
                        {{ $webhookCall->status_code ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Processing Time</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-200">{{ $webhookCall->processing_time_ms !== null ? $webhookCall->processing_time_ms.'ms' : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Replays</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-200">{{ $webhookCall->replay_count }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Source IP</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-200">{{ $webhookCall->ip_address ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">User Agent</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-400 truncate max-w-xs">{{ $webhookCall->user_agent ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Received At</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-200">{{ $webhookCall->created_at->toIso8601String() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Replayed At</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-200">{{ $webhookCall->replayed_at?->toIso8601String() ?? 'Never' }}</dd>
                </div>
            </dl>
        </div>

        @if($webhookCall->event === 'deployment' && $webhookCall->replay_count > 0)
            <div class="bg-amber-950 border border-amber-800 rounded-xl p-4 text-sm text-amber-200">
                This webhook has been replayed {{ $webhookCall->replay_count }} time{{ $webhookCall->replay_count === 1 ? '' : 's' }}.
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-gray-900 border border-gray-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-200">Request Payload</h3>
                        <span class="text-xs font-mono text-gray-500">{{ count($webhookCall->request_payload ?? []) }} fields</span>
                    </div>
                    <div class="p-6">
                        <pre class="text-xs font-mono text-gray-300 overflow-x-auto whitespace-pre-wrap">{{ json_encode($webhookCall->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>

                <div class="bg-gray-900 border border-gray-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-200">Response Payload</h3>
                    </div>
                    <div class="p-6">
                        @if($webhookCall->response_payload)
                            <pre class="text-xs font-mono text-gray-300 overflow-x-auto whitespace-pre-wrap">{{ json_encode($webhookCall->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @else
                            <p class="text-sm text-gray-500">No response payload captured.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-gray-900 border border-gray-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-200">Request Headers</h3>
                    </div>
                    <div class="p-6">
                        @if($webhookCall->request_headers)
                            <pre class="text-xs font-mono text-gray-400 overflow-x-auto whitespace-pre-wrap">{{ json_encode($webhookCall->request_headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @else
                            <p class="text-sm text-gray-500">No headers captured.</p>
                        @endif
                    </div>
                </div>

                @can('replay', $webhookCall)
                    <form method="POST" action="{{ route('projects.webhooks.replay', [$project, $webhookCall]) }}" class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                        @csrf
                        <h3 class="text-sm font-semibold text-gray-200">Replay Webhook</h3>
                        <p class="mt-1 text-xs text-gray-400">Re-process this payload through its registered processor.</p>
                        <button type="submit" class="mt-4 w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                            Replay
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
