<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ $service->name }}
                    </h2>
                    @if($service->status === 'healthy')
                        <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">
                            Healthy
                        </span>
                    @elseif($service->status === 'failing')
                        <span class="px-2.5 py-1 text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20 rounded-full">
                            Failing
                        </span>
                    @else
                        <span class="px-2.5 py-1 text-xs font-semibold bg-gray-800 text-gray-400 border border-gray-700 rounded-full">
                            Unknown
                        </span>
                    @endif
                </div>
                <p class="text-xs font-mono text-gray-400 mt-1">{{ $service->target }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <form method="POST" action="{{ route('projects.services.ping', [$project, $service]) }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                        ⚡ Check Now
                    </button>
                </form>
                <a href="{{ route('projects.services.edit', [$project, $service]) }}" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-200 rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                    Edit Service
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Latency Chart Container -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-1">Response Latency (ms)</h3>
            <p class="text-xs text-gray-400 mb-4">Historical ping probe execution metrics</p>
            <div id="latency-chart" class="h-64"></div>
        </div>

        <!-- Recent Ping Executions History Table -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-4">Probe Check History</h3>
            @if($results->isEmpty())
                <div class="text-center py-6 text-sm text-gray-500">
                    No health checks recorded yet. Click "⚡ Check Now" to dispatch an instant probe.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Timestamp</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">HTTP Code</th>
                                <th class="py-3 px-4">Response Time</th>
                                <th class="py-3 px-4">Error / Output</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($results as $res)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4 text-xs font-mono text-gray-300">{{ $res->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-3 px-4">
                                        @if($res->status === 'healthy')
                                            <span class="px-2 py-0.5 text-xs font-medium bg-emerald-500/10 text-emerald-400 rounded">Healthy</span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-medium bg-red-500/10 text-red-400 rounded">Failing</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-300">{{ $res->status_code ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-300">{{ $res->response_time_ms }} ms</td>
                                    <td class="py-3 px-4 text-xs text-gray-400 truncate max-w-xs">{{ $res->error_message ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- ApexCharts JS Integration -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof ApexCharts === 'undefined') return;

            const rawData = @json($results);
            const seriesData = rawData.slice().reverse().map(r => ({
                x: new Date(r.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
                y: r.response_time_ms
            }));

            const options = {
                chart: {
                    type: 'area',
                    height: 250,
                    toolbar: { show: false },
                    background: 'transparent'
                },
                theme: { mode: 'dark' },
                series: [{ name: 'Latency (ms)', data: seriesData }],
                colors: ['#46e1d5'],
                fill: {
                    type: 'gradient',
                    gradient: { opacityFrom: 0.4, opacityTo: 0.05 }
                },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { type: 'category', labels: { style: { colors: '#9ca3af' } } },
                yaxis: { labels: { style: { colors: '#9ca3af' } } },
                grid: { borderColor: '#1f2937' },
                tooltip: { theme: 'dark' }
            };

            const chart = new ApexCharts(document.querySelector("#latency-chart"), options);
            chart.render();
        });
    </script>
</x-app-layout>
