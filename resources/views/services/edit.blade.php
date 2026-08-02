<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 leading-tight">
            Edit Service: {{ $service->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <form method="POST" action="{{ route('projects.services.update', [$project, $service]) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Service Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $service->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="type" :value="__('Probe Type')" />
                            <select id="type" name="type" class="mt-1 block w-full rounded-lg border-gray-700 bg-gray-950 text-white text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="http" {{ old('type', $service->type) === 'http' ? 'selected' : '' }}>HTTP / HTTPS Probe</option>
                                <option value="tcp" {{ old('type', $service->type) === 'tcp' ? 'selected' : '' }}>TCP Socket Probe</option>
                                <option value="icmp" {{ old('type', $service->type) === 'icmp' ? 'selected' : '' }}>ICMP Ping Probe</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>

                        <div>
                            <x-input-label for="expected_status_code" :value="__('Expected HTTP Code')" />
                            <x-text-input id="expected_status_code" name="expected_status_code" type="number" class="mt-1 block w-full" :value="old('expected_status_code', $service->expected_status_code)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('expected_status_code')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="target" :value="__('Target URL / Endpoint')" />
                        <x-text-input id="target" name="target" type="url" class="mt-1 block w-full font-mono text-sm" :value="old('target', $service->target)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('target')" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="check_interval" :value="__('Check Interval (Seconds)')" />
                            <x-text-input id="check_interval" name="check_interval" type="number" class="mt-1 block w-full" :value="old('check_interval', $service->check_interval)" min="10" max="86400" required />
                            <x-input-error class="mt-2" :messages="$errors->get('check_interval')" />
                        </div>

                        <div>
                            <x-input-label for="timeout" :value="__('Timeout (Seconds)')" />
                            <x-text-input id="timeout" name="timeout" type="number" class="mt-1 block w-full" :value="old('timeout', $service->timeout)" min="1" max="60" required />
                            <x-input-error class="mt-2" :messages="$errors->get('timeout')" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>{{ __('Update Monitored Service') }}</x-primary-button>
                        <a href="{{ route('projects.services.show', [$project, $service]) }}" class="text-sm font-medium text-gray-400 hover:text-gray-200">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-gray-900 border border-red-900/50 rounded-xl p-6 shadow-sm">
                <h3 class="text-md font-semibold text-red-400 mb-2">Delete Service</h3>
                <p class="text-xs text-gray-400 mb-4">Once deleted, health check probes for this endpoint will stop immediately.</p>
                <form method="POST" action="{{ route('projects.services.destroy', [$project, $service]) }}" onsubmit="return confirm('Are you sure you want to delete this monitored service?')">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('Delete Service') }}</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
