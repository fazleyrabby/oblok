<x-app-layout>
    <x-slot name="header">
    <div class="mb-4">
        <x-project-switcher :projects="$projects" :current="$project" :route="'projects.alert-rules.index'" />
    </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Alert Rules for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Threshold-based rules that trigger notifications when metrics breach</p>
            </div>
            <a href="{{ route('projects.alert-rules.create', $project) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                + New Alert Rule
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($alertRules->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="mt-3 text-base font-semibold text-gray-200">No alert rules configured</h3>
                <p class="mt-1 text-sm text-gray-400">Create rules that fire notifications when service health, queue backlog, deployments, or incidents breach thresholds.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.alert-rules.create', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-wider">
                        + Create First Rule
                    </a>
                </div>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Rule</th>
                                <th class="py-3 px-4">Metric</th>
                                <th class="py-3 px-4">Condition</th>
                                <th class="py-3 px-4">Severity</th>
                                <th class="py-3 px-4">Channels</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($alertRules as $alertRule)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-gray-200">{{ $alertRule->name }}</span>
                                        @if($alertRule->description)
                                            <span class="block text-xs text-gray-500 mt-0.5 max-w-xs truncate">{{ $alertRule->description }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $alertRule->metric->label() }}</td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-300">
                                        {{ $alertRule->comparison->label() }}
                                        {{ $alertRule->metric->requiresThreshold() ? $alertRule->threshold : ($alertRule->consecutive_failures ?? 1) }}
                                        <span class="text-gray-500">/ {{ $alertRule->window_minutes }}m</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $alertRule->severity->color() }}">
                                            {{ $alertRule->severity->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">
                                        @if($alertRule->channels->isEmpty())
                                            <span class="text-gray-600">None</span>
                                        @else
                                            {{ $alertRule->channels->pluck('name')->implode(', ') }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($alertRule->enabled)
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">Enabled</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-gray-800 text-gray-400 border border-gray-700 rounded-full">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('projects.alert-rules.edit', [$project, $alertRule]) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                            Manage &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
