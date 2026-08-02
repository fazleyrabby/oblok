<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Deployment #{{ Str::limit($deployment->id, 8) }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Project: {{ $project->name }}</p>
            </div>
            <a href="{{ route('projects.deployments.index', $project) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                &larr; Back to Deployments
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-4">Deployment Metadata</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs text-gray-400">
                <div>
                    <span class="block text-gray-500 font-medium">Status</span>
                    <span class="font-semibold text-white">{{ ucfirst($deployment->status) }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 font-medium">Environment</span>
                    <span class="font-semibold text-indigo-400">{{ $deployment->environment }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 font-medium">Author</span>
                    <span class="text-gray-300">{{ $deployment->author }}</span>
                </div>
                <div>
                    <span class="block text-gray-500 font-medium">Timestamp</span>
                    <span class="text-gray-300">{{ $deployment->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-3">Raw Webhook Payload</h3>
            <pre class="p-4 bg-gray-950 text-emerald-400 font-mono text-xs rounded-lg overflow-x-auto border border-gray-850"><code>{{ json_encode($deployment->payload, JSON_PRETTY_PRINT) }}</code></pre>
        </div>
    </div>
</x-app-layout>
