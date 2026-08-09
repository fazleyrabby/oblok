<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-project-switcher :projects="$projects" :current="$project" :route="'projects.runbooks.index'" />
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Runbooks & Self-Healing for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Predefined operational procedures triggered manually or automatically on service failures and alerts</p>
            </div>
            <a href="{{ route('projects.runbooks.create', $project) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                + New Runbook
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('status'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm rounded-xl flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($runbooks->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                </svg>
                <h3 class="mt-3 text-base font-semibold text-gray-200">No runbooks configured</h3>
                <p class="mt-1 text-sm text-gray-400">Automate incident response with Artisan commands, HTTP webhooks, or shell scripts that trigger on check failures or on demand.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.runbooks.create', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-wider">
                        + Create First Runbook
                    </a>
                </div>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Runbook</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Trigger</th>
                                <th class="py-3 px-4">Cooldown / Timeout</th>
                                <th class="py-3 px-4">Runs</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($runbooks as $runbook)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4">
                                        <a href="{{ route('projects.runbooks.show', [$project, $runbook]) }}" class="font-semibold text-gray-200 hover:text-indigo-400">
                                            {{ $runbook->name }}
                                        </a>
                                        @if($runbook->description)
                                            <span class="block text-xs text-gray-500 mt-0.5 max-w-xs truncate">{{ $runbook->description }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $runbook->type->badgeColor() }}">
                                            {{ $runbook->type->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400 uppercase font-mono">
                                        {{ $runbook->trigger_type }}
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-400">
                                        {{ $runbook->cooldown_minutes }}m cooldown / {{ $runbook->timeout_seconds }}s limit
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-300">
                                        {{ $runbook->runs_count }} executions
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($runbook->isInCooldown())
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full">Cooldown</span>
                                        @elseif($runbook->enabled)
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">Ready</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-gray-800 text-gray-400 border border-gray-700 rounded-full">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <form action="{{ route('projects.runbooks.execute', [$project, $runbook]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-indigo-600/80 hover:bg-indigo-600 text-white rounded text-xs font-semibold transition">
                                                ⚡ Run
                                            </button>
                                        </form>
                                        <a href="{{ route('projects.runbooks.show', [$project, $runbook]) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                            Details &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $runbooks->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
