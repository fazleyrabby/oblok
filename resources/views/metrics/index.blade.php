<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Metrics for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Custom metrics, Prometheus scrape targets, and dashboards</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('status'))
            <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-sm text-gray-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-1">Push metrics</h3>
                <p class="text-xs text-gray-400 mt-1">
                    Any service can post metrics with an API key. Replace your Prometheus pushgateway.
                </p>
                <pre class="mt-4 text-xs text-gray-300 bg-gray-950 border border-gray-700 rounded-lg p-3 overflow-x-auto">curl -X POST {{ route('api.v1.projects.metrics.store', $project) }} \
  -H "Authorization: Bearer &lt;API_KEY&gt;" \
  -H "Content-Type: application/json" \
  -d '{"metrics":[{"name":"http_requests_total","value":42,"labels":{"env":"prod"}}]}'</pre>
            </div>

            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-1">Scrape targets</h3>
                <p class="text-xs text-gray-400 mt-1">
                    Point Atlas at a Prometheus-compatible endpoint (node_exporter, cAdvisor, app <span class="font-mono">/metrics</span>).
                </p>
                @if(auth()->user()->can('create', [\App\Models\MetricTarget::class, $project]))
                    <form method="POST" action="{{ route('projects.metrics.targets.store', $project) }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                        @csrf
                        <input type="text" name="name" placeholder="Name" required value="{{ old('name') }}"
                               class="bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        <input type="url" name="url" placeholder="http://host:9100/metrics" required value="{{ old('url') }}"
                               class="md:col-span-1 bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">Add Target</button>
                    </form>
                @endif
                @error('url')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror

                @if($targets->isEmpty())
                    <p class="mt-4 text-sm text-gray-500">No scrape targets yet.</p>
                @else
                    <ul class="mt-4 divide-y divide-gray-800">
                        @foreach($targets as $target)
                            <li class="py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-200 font-medium">{{ $target->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $target->url }}</p>
                                    @if($target->last_error)
                                        <p class="text-xs text-red-400 mt-0.5">Error: {{ $target->last_error }}</p>
                                    @elseif($target->last_scraped_at)
                                        <p class="text-xs text-gray-500 mt-0.5">Last scraped {{ $target->last_scraped_at->diffForHumans() }}</p>
                                    @endif
                                </div>
                                @if(auth()->user()->can('delete', $target))
                                    <form method="POST" action="{{ route('projects.metrics.targets.destroy', [$project, $target]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-300 uppercase tracking-wider">Remove</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider">Metric chart</h3>
                    <p class="text-xs text-gray-400 mt-1">Choose a metric name and time range.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <select id="metric-name" class="bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($names as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <div class="flex rounded-lg overflow-hidden border border-gray-700">
                        @foreach(['1h' => '1H', '6h' => '6H', '24h' => '24H', '7d' => '7D'] as $value => $label)
                            <button type="button" data-range="{{ $value }}"
                                    class="range-btn px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-white hover:bg-gray-800 transition {{ $value === '24h' ? 'bg-gray-800 text-white' : '' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            @if($names->isEmpty())
                <p class="mt-6 text-sm text-gray-500">No metrics collected yet. Push metrics or add a scrape target.</p>
            @else
                <div id="metric-chart" class="h-80 mt-4"></div>
            @endif
        </div>
    </div>

    @if(! $names->isEmpty())
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            const projectUrl = @json(url('projects/'.$project->id.'/metrics/data'));
            const nameEl = document.getElementById('metric-name');
            const chartEl = document.getElementById('metric-chart');
            let range = '24h';
            let chart = null;

            async function load() {
                const to = new Date().toISOString();
                const from = new Date(Date.now() - rangeToMs(range)).toISOString();
                const name = nameEl.value;
                const res = await fetch(`${projectUrl}?name=${encodeURIComponent(name)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
                const payload = await res.json();

                const series = payload.series.map(s => ({
                    name: Object.keys(s.labels).length
                        ? s.labels.map((v, k) => `${k}=${v}`).join(', ')
                        : name,
                    data: s.points.map(p => ({ x: p[0], y: p[1] })),
                }));

                if (chart) chart.destroy();
                chart = new ApexCharts(chartEl, {
                    chart: { type: 'line', height: 320, toolbar: { show: false }, animations: { enabled: true } },
                    series,
                    stroke: { curve: 'straight', width: 2 },
                    colors: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                    xaxis: { type: 'datetime' },
                    grid: { borderColor: '#1f2937' },
                    legend: { labels: { colors: '#9ca3af' } },
                });
                chart.render();
            }

            function rangeToMs(r) {
                const map = { '1h': 3600000, '6h': 21600000, '24h': 86400000, '7d': 604800000 };
                return map[r] || 86400000;
            }

            nameEl.addEventListener('change', load);
            document.querySelectorAll('.range-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.range-btn').forEach(b => b.classList.remove('bg-gray-800', 'text-white'));
                    btn.classList.add('bg-gray-800', 'text-white');
                    range = btn.dataset.range;
                    load();
                });
            });

            load();
        </script>
    @endif
</x-app-layout>
