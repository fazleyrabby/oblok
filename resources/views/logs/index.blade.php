<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Log Stream — {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Real-time log ingestion & error inspector</p>
            </div>
            <div class="text-xs text-gray-400 bg-gray-900 border border-gray-800 px-3 py-1.5 rounded-lg font-mono">
                API Endpoint: <span class="text-indigo-400 font-semibold">POST /api/v1/projects/{{ $project->id }}/logs</span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Search & Filter Controls Bar -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow-sm">
            <form method="GET" action="{{ route('projects.logs.index', $project) }}" class="flex flex-col sm:flex-row items-center gap-3">
                <!-- Search Input -->
                <div class="flex-1 w-full relative">
                    <svg class="w-4 h-4 text-gray-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Filter log messages..."
                           class="w-full pl-9 pr-4 py-2 bg-gray-950 border border-gray-800 text-gray-100 placeholder-gray-500 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Level Dropdown Filter -->
                <div class="w-full sm:w-48">
                    <select name="level" onchange="this.form.submit()" class="w-full py-2 px-3 bg-gray-950 border border-gray-800 text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Severity Levels</option>
                        <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>Error / Critical</option>
                        <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>Info</option>
                        <option value="debug" {{ request('level') === 'debug' ? 'selected' : '' }}>Debug</option>
                    </select>
                </div>

                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                    Filter Logs
                </button>
            </form>
        </div>

        <!-- Log Entry Stream Table -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            @if($logs->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-3 text-base font-semibold text-gray-200">No log entries found</h3>
                    <p class="mt-1 text-sm text-gray-400">Ingest logs using the REST API endpoint above.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm font-mono">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase font-sans tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Level</th>
                                <th class="py-3 px-4">Channel</th>
                                <th class="py-3 px-4">Message</th>
                                <th class="py-3 px-4">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($logs as $log)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if(in_array($log->level, ['error', 'critical', 'alert', 'emergency']))
                                            <span class="px-2.5 py-0.5 text-xs font-bold font-sans bg-red-500/10 text-red-400 border border-red-500/20 rounded-full uppercase">
                                                {{ $log->level }}
                                            </span>
                                        @elseif($log->level === 'warning')
                                            <span class="px-2.5 py-0.5 text-xs font-bold font-sans bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full uppercase">
                                                {{ $log->level }}
                                            </span>
                                        @elseif($log->level === 'debug')
                                            <span class="px-2.5 py-0.5 text-xs font-bold font-sans bg-gray-500/10 text-gray-400 border border-gray-500/20 rounded-full uppercase">
                                                {{ $log->level }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-xs font-bold font-sans bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-full uppercase">
                                                {{ $log->level }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400 whitespace-nowrap">{{ $log->channel }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-200 break-all">{{ $log->message }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-400 whitespace-nowrap font-sans">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
