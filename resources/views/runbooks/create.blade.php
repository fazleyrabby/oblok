<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Create Runbook for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Configure an automated or manual operational recovery procedure</p>
            </div>
            <a href="{{ route('projects.runbooks.index', $project) }}" class="text-xs font-semibold text-gray-400 hover:text-white transition">
                &larr; Back to Runbooks
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm" x-data="{ type: '{{ old('type', 'artisan') }}' }">
            <form action="{{ route('projects.runbooks.store', $project) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Name & Description -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Runbook Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Restart Queue Worker"
                               class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:outline-none focus:border-indigo-500 transition">
                        @error('name')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Execution Driver Type</label>
                        <select name="type" id="type" x-model="type" required
                                class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:outline-none focus:border-indigo-500 transition">
                            <option value="artisan">Artisan Command (Laravel CLI)</option>
                            <option value="webhook">HTTP Webhook (External API / Service)</option>
                            <option value="shell">Shell Script (CLI Process Execution)</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Description (Optional)</label>
                    <textarea name="description" id="description" rows="2" placeholder="Briefly describe what this operational procedure accomplishes"
                              class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:outline-none focus:border-indigo-500 transition">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dynamic Config Fields -->
                <!-- Artisan Command Config -->
                <div x-show="type === 'artisan'" class="space-y-4 border-t border-b border-gray-800 py-4">
                    <h3 class="text-sm font-bold text-indigo-400 uppercase tracking-wider">Artisan Command Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="command" class="block text-xs font-medium text-gray-300 mb-1">Artisan Command Signature</label>
                            <input type="text" name="command" id="command" value="{{ old('command', 'queue:restart') }}" placeholder="e.g. queue:restart or cache:clear"
                                   class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2 text-sm font-mono text-gray-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="parameters" class="block text-xs font-medium text-gray-300 mb-1">Parameters (Optional JSON or String)</label>
                            <input type="text" name="parameters" id="parameters" value="{{ old('parameters') }}" placeholder="e.g. --queue=high"
                                   class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2 text-sm font-mono text-gray-100 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Shell Script Config -->
                <div x-show="type === 'shell'" class="space-y-4 border-t border-b border-gray-800 py-4">
                    <h3 class="text-sm font-bold text-amber-400 uppercase tracking-wider">Shell Process Script Details</h3>
                    <div>
                        <label for="shell_command" class="block text-xs font-medium text-gray-300 mb-1">CLI Command / Script to Execute</label>
                        <input type="text" name="command" id="shell_command" value="{{ old('command', 'echo "Healing action triggered"') }}" placeholder="e.g. ./scripts/deploy-hotfix.sh"
                               class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2 text-sm font-mono text-gray-100 focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <!-- Webhook Config -->
                <div x-show="type === 'webhook'" class="space-y-4 border-t border-b border-gray-800 py-4">
                    <h3 class="text-sm font-bold text-blue-400 uppercase tracking-wider">HTTP Webhook Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-3">
                            <label for="url" class="block text-xs font-medium text-gray-300 mb-1">Webhook Endpoint URL</label>
                            <input type="url" name="url" id="url" value="{{ old('url') }}" placeholder="https://api.cloudprovider.com/v1/restart"
                                   class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2 text-sm font-mono text-gray-100 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label for="method" class="block text-xs font-medium text-gray-300 mb-1">HTTP Method</label>
                            <select name="method" id="method" class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2 text-sm text-gray-100 focus:outline-none focus:border-blue-500">
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="GET">GET</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="body" class="block text-xs font-medium text-gray-300 mb-1">JSON Body Payload (Optional)</label>
                        <textarea name="body" id="body" rows="2" placeholder='{"action": "restart_service"}'
                                  class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2 text-sm font-mono text-gray-100 focus:outline-none focus:border-blue-500">{{ old('body') }}</textarea>
                    </div>
                </div>

                <!-- Trigger & Guardrails -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="trigger_type" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Trigger Mode</label>
                        <select name="trigger_type" id="trigger_type" required
                                class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:outline-none focus:border-indigo-500">
                            <option value="both">Both (Manual & Automatic)</option>
                            <option value="manual">Manual Only</option>
                            <option value="automatic">Automatic Only (Self-Healing)</option>
                        </select>
                    </div>

                    <div>
                        <label for="cooldown_minutes" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Cooldown (Minutes)</label>
                        <input type="number" name="cooldown_minutes" id="cooldown_minutes" value="{{ old('cooldown_minutes', 10) }}" min="0" max="1440" required
                               class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:outline-none focus:border-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Prevents repeated automatic execution loops</p>
                    </div>

                    <div>
                        <label for="timeout_seconds" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Process Timeout (Seconds)</label>
                        <input type="number" name="timeout_seconds" id="timeout_seconds" value="{{ old('timeout_seconds', 30) }}" min="5" max="300" required
                               class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="enabled" id="enabled" value="1" {{ old('enabled', true) ? 'checked' : '' }}
                           class="rounded bg-gray-950 border-gray-800 text-indigo-600 focus:ring-indigo-500">
                    <label for="enabled" class="text-sm font-medium text-gray-300">Enable this runbook for execution</label>
                </div>

                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-800">
                    <a href="{{ route('projects.runbooks.index', $project) }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition shadow-sm">
                        Create Runbook
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
