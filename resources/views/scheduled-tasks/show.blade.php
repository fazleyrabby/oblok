<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    {{ $scheduledTask->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1 font-mono">{{ $scheduledTask->cron_expression }} &middot; {{ $scheduledTask->timezone }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('projects.scheduled-tasks.edit', [$project, $scheduledTask]) }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                    Edit
                </a>
                <a href="{{ route('projects.scheduled-tasks.index', $project) }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                    &larr; Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Command</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-200">{{ $scheduledTask->command }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</dt>
                    <dd class="mt-1">
                        @if($scheduledTask->enabled)
                            <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase text-emerald-400 border-emerald-900 bg-emerald-950">Active</span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase text-gray-400 border-gray-800">Paused</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Run</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-200">{{ $scheduledTask->last_run_at?->toIso8601String() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Next Run</dt>
                    <dd class="mt-1 font-mono text-xs text-gray-200">{{ $scheduledTask->next_run_at?->toIso8601String() ?? '—' }}</dd>
                </div>
            </dl>
            @if($scheduledTask->description)
                <p class="mt-4 text-sm text-gray-400">{{ $scheduledTask->description }}</p>
            @endif
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-200">Run History</h3>
                @if($runs->isEmpty())
                    <p class="mt-4 text-sm text-gray-500">No runs recorded yet. Runs are tracked when the task executes or a run is missed.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                                <tr>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4">Started</th>
                                    <th class="py-3 px-4">Duration</th>
                                    <th class="py-3 px-4">Exit</th>
                                    <th class="py-3 px-4">Output</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @foreach($runs as $run)
                                    <tr class="hover:bg-gray-850 transition">
                                        <td class="py-3 px-4">
                                            <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $run->status->color() }}">
                                                {{ $run->status->label() }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-gray-400">{{ $run->started_at?->diffForHumans() ?? '—' }}</td>
                                        <td class="py-3 px-4 font-mono text-xs text-gray-400">{{ $run->duration_ms !== null ? $run->duration_ms.'ms' : '—' }}</td>
                                        <td class="py-3 px-4 font-mono text-xs {{ $run->exit_code === 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $run->exit_code ?? '—' }}</td>
                                        <td class="py-3 px-4 text-xs text-gray-400 truncate max-w-xs" title="{{ $run->output ?? $run->error ?? '' }}">{{ $run->output ?? $run->error ?? '—' }}</td>
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

            <div class="space-y-6">
                @can('recordRun', $scheduledTask)
                    <form method="POST" action="{{ route('projects.scheduled-tasks.runs', [$project, $scheduledTask]) }}" class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                        @csrf
                        <h3 class="text-sm font-semibold text-gray-200">Record Run</h3>
                        <p class="mt-1 text-xs text-gray-400">Log a successful execution manually.</p>
                        <div class="mt-4">
                            <x-input-label for="duration_ms" :value="__('Duration (ms)')" />
                            <x-text-input id="duration_ms" name="duration_ms" type="number" class="mt-1 block w-full" min="0" />
                        </div>
                        <button type="submit" class="mt-4 w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                            Record Run
                        </button>
                    </form>
                @endcan

                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-200">Cron Endpoint</h3>
                    <p class="mt-1 text-xs text-gray-400">Post a run report from your cron job to record executions.</p>
                    <pre class="mt-3 text-xs font-mono text-gray-400 overflow-x-auto whitespace-pre-wrap break-all">curl -X POST "{{ url('/api/v1/projects/'.$project->id.'/scheduled-tasks/'.$scheduledTask->id.'/runs') }}" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"status":"success"}'</pre>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
