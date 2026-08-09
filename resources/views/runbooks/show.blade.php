<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ $runbook->name }}
                    </h2>
                    <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $runbook->type->badgeColor() }}">
                        {{ $runbook->type->label() }}
                    </span>
                    @if($runbook->isInCooldown())
                        <span class="px-2.5 py-1 text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full">Cooldown</span>
                    @elseif($runbook->enabled)
                        <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">Active</span>
                    @else
                        <span class="px-2.5 py-1 text-xs font-semibold bg-gray-800 text-gray-400 border border-gray-700 rounded-full">Disabled</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $runbook->description ?? 'No description provided' }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <form action="{{ route('projects.runbooks.execute', [$project, $runbook]) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition flex items-center space-x-1 shadow-sm">
                        <span>⚡ Execute Runbook Now</span>
                    </button>
                </form>
                <a href="{{ route('projects.runbooks.edit', [$project, $runbook]) }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                    Edit Configuration
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

        <!-- Configuration Card -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-200 uppercase tracking-wider">Runbook Configuration</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                <div class="bg-gray-950 p-3 rounded-lg border border-gray-800">
                    <span class="text-gray-500 block uppercase tracking-wider">Trigger Mode</span>
                    <span class="font-semibold text-gray-200 uppercase">{{ $runbook->trigger_type }}</span>
                </div>
                <div class="bg-gray-950 p-3 rounded-lg border border-gray-800">
                    <span class="text-gray-500 block uppercase tracking-wider">Cooldown Period</span>
                    <span class="font-semibold text-gray-200">{{ $runbook->cooldown_minutes }} minutes</span>
                </div>
                <div class="bg-gray-950 p-3 rounded-lg border border-gray-800">
                    <span class="text-gray-500 block uppercase tracking-wider">Process Timeout</span>
                    <span class="font-semibold text-gray-200">{{ $runbook->timeout_seconds }} seconds</span>
                </div>
                <div class="bg-gray-950 p-3 rounded-lg border border-gray-800">
                    <span class="text-gray-500 block uppercase tracking-wider">Last Executed</span>
                    <span class="font-semibold text-gray-200">{{ $runbook->last_executed_at?->diffForHumans() ?? 'Never' }}</span>
                </div>
            </div>

            <div class="bg-gray-950 p-4 rounded-lg border border-gray-800 font-mono text-xs text-gray-300">
                <span class="text-gray-500 uppercase tracking-wider block mb-1">Execution Config</span>
                <pre class="whitespace-pre-wrap">{{ json_encode($runbook->config, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>

        <!-- Execution Run Log History -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-100 mb-4">Execution History & Logs</h3>

            @if($runs->isEmpty())
                <div class="text-center py-8 border border-dashed border-gray-800 rounded-lg">
                    <p class="text-sm text-gray-500">No execution logs recorded yet.</p>
                    <p class="text-xs text-gray-600 mt-1">Execute the runbook manually or trigger an assigned service failure to generate logs.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Run ID</th>
                                <th class="py-3 px-4">Trigger Source</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Duration</th>
                                <th class="py-3 px-4">Exit Code</th>
                                <th class="py-3 px-4">Timestamp</th>
                                <th class="py-3 px-4">Output Log</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($runs as $run)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4 text-xs font-mono text-gray-400">
                                        {{ substr($run->id, 0, 8) }}...
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-300 uppercase">
                                        {{ $run->triggered_by_type }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $run->status->badgeColor() }}">
                                            {{ $run->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-400">
                                        {{ $run->duration_ms ? $run->duration_ms.'ms' : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono {{ $run->exit_code === 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $run->exit_code ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">
                                        {{ $run->created_at->diffForHumans() }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <details class="text-xs">
                                            <summary class="cursor-pointer font-mono text-indigo-400 hover:text-indigo-300">View Output Log</summary>
                                            <div class="mt-2 p-3 bg-gray-950 rounded border border-gray-800 font-mono text-xs text-gray-300 max-h-48 overflow-y-auto whitespace-pre-wrap">
                                                {{ $run->output ?: 'No output recorded.' }}
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $runs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
