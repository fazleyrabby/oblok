<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    {{ __('Operational Dashboard') }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Real-time system overview and project status</p>
            </div>
            <div>
                <a href="{{ route('projects.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold uppercase tracking-wider transition">
                    + New Project
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- 6 Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Total Projects Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Projects</span>
                    <span class="p-2 bg-indigo-500/10 text-indigo-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-bold text-white">{{ $overview['total_projects'] }}</span>
                    <span class="text-xs text-gray-400">projects</span>
                </div>
            </div>

            <!-- Active Projects Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Projects</span>
                    <span class="p-2 bg-emerald-500/10 text-emerald-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-bold text-white">{{ $overview['active_projects'] }}</span>
                    <span class="text-xs text-emerald-400">active</span>
                </div>
            </div>

            <!-- System Uptime Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">System Uptime</span>
                    <span class="p-2 bg-blue-500/10 text-blue-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-bold text-white">{{ number_format($overview['uptime_percentage'], 1) }}%</span>
                    <span class="text-xs text-blue-400">last 24h</span>
                </div>
            </div>

            <!-- Open Incidents Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Open Incidents</span>
                    <span class="p-2 {{ $overview['active_incidents'] > 0 ? 'bg-red-500/10 text-red-400' : 'bg-amber-500/10 text-amber-400' }} rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-bold text-white">{{ $overview['active_incidents'] }}</span>
                    <span class="text-xs {{ $overview['active_incidents'] > 0 ? 'text-red-400' : 'text-gray-400' }}">issues</span>
                </div>
            </div>

            <!-- Active Alerts Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Firing Alerts</span>
                    <span class="p-2 {{ $overview['active_alerts'] > 0 ? 'bg-red-500/10 text-red-400' : 'bg-emerald-500/10 text-emerald-400' }} rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-bold text-white">{{ $overview['active_alerts'] }}</span>
                    <span class="text-xs {{ $overview['active_alerts'] > 0 ? 'text-red-400' : 'text-emerald-400' }}">{{ $overview['active_alerts'] > 0 ? 'firing' : 'clear' }}</span>
                </div>
            </div>

            <!-- Flapping Services Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Flapping Services</span>
                    <span class="p-2 {{ $overview['flapping_services'] > 0 ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-400' }} rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-bold text-white">{{ $overview['flapping_services'] }}</span>
                    <span class="text-xs {{ $overview['flapping_services'] > 0 ? 'text-amber-400 animate-pulse' : 'text-emerald-400' }}">{{ $overview['flapping_services'] > 0 ? 'unstable' : 'stable' }}</span>
                </div>
            </div>
        </div>

        <!-- Uptime Graph Placeholder Container -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-white">System Performance & Response Time</h3>
                    <p class="text-xs text-gray-400">ApexCharts graph visualization container (Phase 4 Monitoring)</p>
                </div>
                <span class="px-2.5 py-1 bg-gray-800 text-xs font-medium text-gray-400 rounded-full">24 Hour Window</span>
            </div>
            <div class="h-44 border border-dashed border-gray-800 rounded-lg flex items-center justify-center bg-gray-950/50">
                <p class="text-xs text-gray-500">Service Uptime Graph ApexChart will render here when Phase 4 Monitoring starts pinging endpoints.</p>
            </div>
        </div>

        <!-- Recent Projects Activity Table -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-white">Recent Active Projects</h3>
                <a href="{{ route('projects.index') }}" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">View All Projects &rarr;</a>
            </div>

            @if($overview['recent_projects']->isEmpty())
                <div class="text-center py-8 border border-dashed border-gray-800 rounded-lg">
                    <p class="text-sm text-gray-500">No active projects created yet.</p>
                    <a href="{{ route('projects.create') }}" class="mt-3 inline-block text-xs text-indigo-400 font-semibold">+ Create Project</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950/60 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Project Name</th>
                                <th class="py-3 px-4">Slug</th>
                                <th class="py-3 px-4">Environment</th>
                                <th class="py-3 px-4">Last Updated</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($overview['recent_projects'] as $project)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4 font-medium text-white">
                                        <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-400">
                                            {{ $project->name }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-400">{{ $project->slug }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-400">
                                        <span class="px-2 py-0.5 rounded text-xs bg-gray-800 text-gray-300">
                                            {{ $project->metadata['environment'] ?? 'production' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $project->updated_at->diffForHumans() }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('projects.show', $project) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Open Dashboard</a>
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
