<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Scheduled Tasks for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Cron jobs tracked for execution and missed runs</p>
            </div>
            <a href="{{ route('projects.scheduled-tasks.create', $project) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                + New Task
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($scheduledTasks->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-3 text-base font-semibold text-gray-200">No scheduled tasks</h3>
                <p class="mt-1 text-sm text-gray-400">Register cron jobs to monitor their executions and detect missed runs.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.scheduled-tasks.create', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-wider">
                        + Register Task
                    </a>
                </div>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Task</th>
                                <th class="py-3 px-4">Schedule</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Last Run</th>
                                <th class="py-3 px-4">Next Run</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($scheduledTasks as $task)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4">
                                        <a href="{{ route('projects.scheduled-tasks.show', [$project, $task]) }}" class="font-semibold text-gray-200 hover:text-indigo-400">
                                            {{ $task->name }}
                                        </a>
                                        <p class="text-xs font-mono text-gray-500 mt-0.5">{{ $task->command }}</p>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs text-gray-400">{{ $task->cron_expression }}</td>
                                    <td class="py-3 px-4">
                                        @if(! $task->enabled)
                                            <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase text-gray-400 border-gray-800">Paused</span>
                                        @else
                                            @php $latestRun = $task->runs->first(); @endphp
                                            @if($latestRun)
                                                <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $latestRun->status->color() }}">
                                                    {{ $latestRun->status->label() }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase text-gray-500 border-gray-800">Never Run</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $task->last_run_at?->diffForHumans() ?? '—' }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $task->next_run_at?->diffForHumans() ?? '—' }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('projects.scheduled-tasks.show', [$project, $task]) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                            View &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $scheduledTasks->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
