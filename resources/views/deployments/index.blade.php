<x-app-layout>
    <x-slot name="header">
    <div class="mb-4">
        <x-project-switcher :projects="$projects" :current="$project" :route="'projects.deployments.index'" />
    </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Deployments for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Webhook CI/CD build execution timeline</p>
            </div>
            <div class="text-xs text-gray-400 bg-gray-900 border border-gray-800 px-3 py-1.5 rounded-lg font-mono">
                Webhook URL: <span class="text-indigo-400 font-semibold">{{ url('/api/v1/webhooks/deployments/' . $project->slug) }}</span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($deployments->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <h3 class="mt-3 text-base font-semibold text-gray-200">No deployment events recorded yet</h3>
                <p class="mt-1 text-sm text-gray-400">Trigger a deployment from GitHub Actions or Vercel using the webhook URL above.</p>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Environment</th>
                                <th class="py-3 px-4">Commit / Message</th>
                                <th class="py-3 px-4">Author</th>
                                <th class="py-3 px-4">Timestamp</th>
                                <th class="py-3 px-4 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($deployments as $deployment)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4">
                                        @if($deployment->isSuccessful())
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">
                                                Successful
                                            </span>
                                        @elseif($deployment->status === 'failed')
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20 rounded-full">
                                                Failed
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full">
                                                {{ ucfirst($deployment->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs font-semibold text-gray-300">
                                        <span class="px-2 py-0.5 rounded bg-gray-800 text-indigo-300 border border-gray-700">
                                            {{ $deployment->environment }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs">
                                        <span class="font-mono text-xs text-indigo-400 block">{{ Str::limit($deployment->commit_hash, 8, '') }}</span>
                                        <span class="text-xs text-gray-300 truncate block">{{ $deployment->commit_message ?? 'No commit message' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $deployment->author ?? 'CI Bot' }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $deployment->created_at->diffForHumans() }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('projects.deployments.show', [$project, $deployment]) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                            View Payload &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $deployments->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
