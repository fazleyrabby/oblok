<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-project-switcher :projects="$projects" :current="$project" :route="'projects.resources.index'" />
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Server Resources — {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Host & Container CPU, Memory consumption, and Disk utilization</p>
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
        <!-- Resource Gauge Overview Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- CPU Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider truncate">Host CPU</span>
                        <span id="badge-cores" class="px-1.5 py-0.5 text-[10px] font-mono font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded flex-shrink-0">⚡ 8 Cores</span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-xs text-gray-500 font-medium">Load</span>
                        <span id="stat-cpu" class="text-sm font-bold font-mono text-indigo-400">0%</span>
                    </div>
                    <div class="w-full bg-gray-950 h-2 rounded-full mt-2 overflow-hidden border border-gray-800">
                        <div id="bar-cpu" class="bg-indigo-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-800/60 text-[11px] font-mono text-gray-500">
                    <span>avg <strong id="sub-cpu-avg" class="text-gray-400">0%</strong></span>
                    <span>pk <strong id="sub-cpu-pk" class="text-gray-400">0%</strong></span>
                </div>
            </div>

            <!-- Container Memory Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider truncate">Container RAM</span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-xs text-gray-500 font-medium">Usage</span>
                        <span id="stat-container-mem" class="text-sm font-bold font-mono text-cyan-400">0%</span>
                    </div>
                    <div class="w-full bg-gray-950 h-2 rounded-full mt-2 overflow-hidden border border-gray-800">
                        <div id="bar-container-mem" class="bg-cyan-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-800/60 text-[11px] font-mono text-gray-500">
                    <span>avg <strong id="sub-cmem-avg" class="text-gray-400">0%</strong></span>
                    <span>pk <strong id="sub-cmem-pk" class="text-gray-400">0%</strong></span>
                </div>
            </div>

            <!-- Host Memory Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider truncate">Host System RAM</span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-xs text-gray-500 font-medium">Usage</span>
                        <span id="stat-mem" class="text-sm font-bold font-mono text-emerald-400">0%</span>
                    </div>
                    <div class="w-full bg-gray-950 h-2 rounded-full mt-2 overflow-hidden border border-gray-800">
                        <div id="bar-mem" class="bg-emerald-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-800/60 text-[11px] font-mono text-gray-500">
                    <span>avg <strong id="sub-mem-avg" class="text-gray-400">0%</strong></span>
                    <span>pk <strong id="sub-mem-pk" class="text-gray-400">0%</strong></span>
                </div>
            </div>

            <!-- Disk Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider truncate">Host Disk</span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-xs text-gray-500 font-medium">Usage</span>
                        <span id="stat-disk" class="text-sm font-bold font-mono text-amber-400">0%</span>
                    </div>
                    <div class="w-full bg-gray-950 h-2 rounded-full mt-2 overflow-hidden border border-gray-800">
                        <div id="bar-disk" class="bg-amber-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-800/60 text-[11px] font-mono text-gray-500">
                    <span>avg <strong id="sub-disk-avg" class="text-gray-400">0%</strong></span>
                    <span>pk <strong id="sub-disk-pk" class="text-gray-400">0%</strong></span>
                </div>
            </div>
        </div>

        <!-- Resource Performance Line Chart -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-white">Resource Metrics History</h3>
                    <p class="text-xs text-gray-400 mt-0.5">CPU, Memory, and Disk usage trends over time</p>
                </div>
            </div>
            <div class="relative min-h-[320px]">
                <div id="resources-loader" class="absolute inset-0 flex flex-col items-center justify-center space-y-2 text-indigo-400 bg-gray-900 z-10">
                    <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs text-gray-400 font-mono">Loading resource metrics...</span>
                </div>
                <div id="resources-chart" class="h-80"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const dataUrl = @json(route('projects.resources.data', $project));
        let currentRange = '24h';
        let chart = null;

        async function fetchResources() {
            const res = await fetch(`${dataUrl}?range=${currentRange}`);
            const data = await res.json();

            // Hide loader
            const loaderEl = document.getElementById('resources-loader');
            if (loaderEl) loaderEl.style.display = 'none';

            // Update stats & progress bars
            const latest = data.latest || { cpu_percent: 0, memory_percent: 0, container_memory_percent: 0, disk_percent: 0, cpu_cores: 1 };
            const stats = data.stats || { cpu: {avg: 0, peak: 0}, memory: {avg: 0, peak: 0}, container_memory: {avg: 0, peak: 0}, disk: {avg: 0, peak: 0} };
            
            document.getElementById('badge-cores').innerText = `⚡ ${latest.cpu_cores || 1} CPU Cores`;

            document.getElementById('stat-cpu').innerText = `${latest.cpu_percent}%`;
            document.getElementById('bar-cpu').style.width = `${Math.min(latest.cpu_percent, 100)}%`;
            document.getElementById('sub-cpu-avg').innerText = `${stats.cpu.avg}%`;
            document.getElementById('sub-cpu-pk').innerText = `${stats.cpu.peak}%`;

            document.getElementById('stat-container-mem').innerText = `${latest.container_memory_percent}%`;
            document.getElementById('bar-container-mem').style.width = `${Math.min(latest.container_memory_percent, 100)}%`;
            document.getElementById('sub-cmem-avg').innerText = `${stats.container_memory.avg}%`;
            document.getElementById('sub-cmem-pk').innerText = `${stats.container_memory.peak}%`;

            document.getElementById('stat-mem').innerText = `${latest.memory_percent}%`;
            document.getElementById('bar-mem').style.width = `${Math.min(latest.memory_percent, 100)}%`;
            document.getElementById('sub-mem-avg').innerText = `${stats.memory.avg}%`;
            document.getElementById('sub-mem-pk').innerText = `${stats.memory.peak}%`;

            document.getElementById('stat-disk').innerText = `${latest.disk_percent}%`;
            document.getElementById('bar-disk').style.width = `${Math.min(latest.disk_percent, 100)}%`;
            document.getElementById('sub-disk-avg').innerText = `${stats.disk.avg}%`;
            document.getElementById('sub-disk-pk').innerText = `${stats.disk.peak}%`;

            // Render ApexChart
            if (chart) chart.destroy();
            chart = new ApexCharts(document.getElementById('resources-chart'), {
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: { show: false },
                    animations: { enabled: true }
                },
                series: data.series,
                colors: ['#6366f1', '#10b981', '#f59e0b'],
                stroke: { width: 2, curve: 'smooth' },
                xaxis: { type: 'datetime' },
                yaxis: {
                    max: 100,
                    min: 0,
                    labels: {
                        style: { colors: '#9ca3af' },
                        formatter: val => `${val.toFixed(1)}%`
                    }
                },
                tooltip: {
                    y: { formatter: val => `${val.toFixed(2)}%` }
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
                fetchResources();
            });
        });

        fetchResources();
    </script>
</x-app-layout>
