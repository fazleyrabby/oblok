<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-project-switcher :projects="$projects" :current="$project" :route="'projects.request-analytics.index'" />
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Request Analytics — {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">HTTP web traffic, status distribution, and request volume analytics</p>
            </div>
            <div class="flex rounded-lg overflow-hidden border border-gray-700">
                @foreach(['1h' => '1H', '6h' => '6H', '24h' => '24H', '7d' => '7D'] as $value => $label)
                    <button type="button" data-range="{{ $value }}"
                            class="range-btn px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-white hover:bg-gray-800 transition {{ $value === '24h' ? 'bg-gray-800 text-white' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Top Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Requests</span>
                <p id="stat-total" class="text-2xl font-bold text-white mt-1">0</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Success Rate</span>
                <p id="stat-success" class="text-2xl font-bold text-emerald-400 mt-1">100%</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">4xx Client Errors</span>
                <p id="stat-4xx" class="text-2xl font-bold text-amber-400 mt-1">0</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">5xx Server Errors</span>
                <p id="stat-5xx" class="text-2xl font-bold text-red-400 mt-1">0</p>
            </div>
        </div>

        <!-- Request Volume & Status Code Distribution Chart -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-white">HTTP Request Volume & Status Distribution</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Aggregated HTTP traffic grouped by response status code</p>
                </div>
            </div>
            <div id="requests-chart" class="h-80"></div>
        </div>

        <!-- Method Breakdown Card -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-3">HTTP Methods Breakdown</h3>
            <div id="method-breakdown" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-950 border border-gray-800 rounded-lg p-3 text-center">
                    <span class="text-xs font-bold text-indigo-400">GET</span>
                    <p id="method-GET" class="text-lg font-semibold text-gray-200 mt-1">0</p>
                </div>
                <div class="bg-gray-950 border border-gray-800 rounded-lg p-3 text-center">
                    <span class="text-xs font-bold text-emerald-400">POST</span>
                    <p id="method-POST" class="text-lg font-semibold text-gray-200 mt-1">0</p>
                </div>
                <div class="bg-gray-950 border border-gray-800 rounded-lg p-3 text-center">
                    <span class="text-xs font-bold text-amber-400">PUT / PATCH</span>
                    <p id="method-PUT" class="text-lg font-semibold text-gray-200 mt-1">0</p>
                </div>
                <div class="bg-gray-950 border border-gray-800 rounded-lg p-3 text-center">
                    <span class="text-xs font-bold text-red-400">DELETE</span>
                    <p id="method-DELETE" class="text-lg font-semibold text-gray-200 mt-1">0</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const dataUrl = @json(route('projects.request-analytics.data', $project));
        let currentRange = '24h';
        let chart = null;

        async function fetchAnalytics() {
            const res = await fetch(`${dataUrl}?range=${currentRange}`);
            const data = await res.json();

            // Update stats
            document.getElementById('stat-total').innerText = data.total_requests.toLocaleString();
            document.getElementById('stat-success').innerText = `${data.success_rate}%`;
            document.getElementById('stat-4xx').innerText = (data.status_counts['4xx'] || 0).toLocaleString();
            document.getElementById('stat-5xx').innerText = (data.status_counts['5xx'] || 0).toLocaleString();

            // Update methods
            document.getElementById('method-GET').innerText = (data.method_counts['GET'] || 0).toLocaleString();
            document.getElementById('method-POST').innerText = (data.method_counts['POST'] || 0).toLocaleString();
            document.getElementById('method-PUT').innerText = ((data.method_counts['PUT'] || 0) + (data.method_counts['PATCH'] || 0)).toLocaleString();
            document.getElementById('method-DELETE').innerText = (data.method_counts['DELETE'] || 0).toLocaleString();

            // Render ApexChart
            if (chart) chart.destroy();
            chart = new ApexCharts(document.getElementById('requests-chart'), {
                chart: {
                    type: 'bar',
                    stacked: true,
                    height: 320,
                    toolbar: { show: false },
                    animations: { enabled: true }
                },
                series: data.series,
                colors: ['#10b981', '#6366f1', '#f59e0b', '#ef4444'],
                stroke: { width: 1 },
                xaxis: { type: 'datetime' },
                yaxis: {
                    labels: {
                        style: { colors: '#9ca3af' },
                        formatter: val => Number.isInteger(val) ? val : val.toFixed(1)
                    }
                },
                tooltip: {
                    y: {
                        formatter: val => Number.isInteger(val) ? val : Number(val.toFixed(1))
                    }
                },
                grid: { borderColor: '#1f2937' },
                legend: { labels: { colors: '#9ca3af' } }
            });
            chart.render();
        }

        document.querySelectorAll('.range-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.range-btn').forEach(b => b.classList.remove('bg-gray-800', 'text-white'));
                btn.classList.add('bg-gray-800', 'text-white');
                currentRange = btn.dataset.range;
                fetchAnalytics();
            });
        });

        fetchAnalytics();
    </script>
</x-app-layout>
