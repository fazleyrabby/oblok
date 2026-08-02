<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Queue & Worker Operations
                </h2>
                <p class="text-xs text-gray-400 mt-1">Real-time Redis job throughput and worker status</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="/horizon" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Open Horizon Dashboard &rarr;
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- 3 Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Horizon Status Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Horizon Worker</span>
                    <span class="p-2 bg-indigo-500/10 text-indigo-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $metrics['horizon_status'] === 'running' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                    <span class="text-2xl font-bold text-white uppercase">{{ $metrics['horizon_status'] }}</span>
                </div>
            </div>

            <!-- Pending Jobs Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Pending Jobs</span>
                    <span class="p-2 bg-blue-500/10 text-blue-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-bold text-white">{{ $metrics['pending_jobs'] }}</span>
                    <span class="text-xs text-gray-400">queued</span>
                </div>
            </div>

            <!-- Failed Jobs Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Failed Jobs</span>
                    <span class="p-2 bg-red-500/10 text-red-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-bold text-white">{{ $metrics['failed_jobs'] }}</span>
                    <span class="text-xs text-gray-400">exceptions</span>
                </div>
            </div>
        </div>

        <!-- Recent Failed Jobs Table -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-4">Recent Exception Log</h3>
            @if($metrics['recent_failed_jobs']->isEmpty())
                <div class="text-center py-8 border border-dashed border-gray-800 rounded-lg">
                    <p class="text-sm text-emerald-400 font-semibold">✨ Zero failed queue jobs!</p>
                    <p class="text-xs text-gray-500 mt-1">All background queue workers are executing cleanly without exception.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Connection</th>
                                <th class="py-3 px-4">Queue</th>
                                <th class="py-3 px-4">Exception Output</th>
                                <th class="py-3 px-4">Failed At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($metrics['recent_failed_jobs'] as $failedJob)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4 text-xs font-mono text-gray-300">{{ $failedJob->connection }}</td>
                                    <td class="py-3 px-4 text-xs font-mono text-indigo-400">{{ $failedJob->queue }}</td>
                                    <td class="py-3 px-4 text-xs font-mono text-red-400 max-w-md truncate">{{ $failedJob->exception }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $failedJob->failed_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
