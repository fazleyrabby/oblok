<x-app-layout>
    <x-slot name="header">
    <div class="mb-4">
        <x-project-switcher :projects="$projects" :current="$project" :route="'projects.services.index'" />
    </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Services for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Monitored endpoints & service health probes</p>
            </div>
            <a href="{{ route('projects.services.create', $project) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                + Add Service
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($services->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <h3 class="mt-3 text-base font-semibold text-gray-200">No services monitored yet</h3>
                <p class="mt-1 text-sm text-gray-400">Add HTTP or API endpoints to monitor uptime and response latency.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.services.create', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-wider">
                        + Add First Service
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($services as $service)
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 flex flex-col justify-between hover:border-gray-700 transition shadow-sm">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <a href="{{ route('projects.services.show', [$project, $service]) }}" class="text-base font-bold text-white hover:text-indigo-400">
                                    {{ $service->name }}
                                </a>
                                @if($service->status === 'healthy')
                                    <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Healthy
                                    </span>
                                @elseif($service->status === 'failing')
                                    <span class="px-2.5 py-1 text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Failing
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold bg-gray-800 text-gray-400 border border-gray-700 rounded-full">
                                        Unknown
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs font-mono text-gray-400 truncate mb-3">{{ $service->target }}</p>

                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-400 mb-4 bg-gray-950 p-2.5 rounded-lg border border-gray-850">
                                <div>
                                    <span class="block text-gray-500">Interval</span>
                                    <span>Every {{ $service->check_interval }}s</span>
                                </div>
                                <div>
                                    <span class="block text-gray-500">Expected Code</span>
                                    <span>{{ $service->expected_status_code }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-800">
                            <form method="POST" action="{{ route('projects.services.ping', [$project, $service]) }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-gray-300 hover:text-white bg-gray-800 hover:bg-gray-700 px-3 py-1.5 rounded-lg transition">
                                    ⚡ Check Now
                                </button>
                            </form>
                            <a href="{{ route('projects.services.show', [$project, $service]) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                View Metrics &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
