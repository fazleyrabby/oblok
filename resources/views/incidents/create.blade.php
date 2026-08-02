<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                Log New Incident — {{ $project->name }}
            </h2>
            <a href="{{ route('projects.incidents.index', $project) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                &larr; Back to Incidents
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <form method="POST" action="{{ route('projects.incidents.store', $project) }}" class="space-y-6">
                @csrf

                <!-- Incident Title -->
                <div>
                    <x-input-label for="title" value="Incident Title" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" placeholder="e.g. Stripe Webhook API High Error Rate" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <!-- Associated Service -->
                <div>
                    <x-input-label for="service_id" value="Associated Monitored Service (Optional)" />
                    <select id="service_id" name="service_id" class="mt-1 block w-full py-2 px-3 bg-gray-950 border border-gray-800 text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- System-wide / None --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id') === $service->id ? 'selected' : '' }}>
                                {{ $service->name }} ({{ $service->target }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('service_id')" />
                </div>

                <!-- Severity Level -->
                <div>
                    <x-input-label for="severity" value="Severity Level" />
                    <select id="severity" name="severity" class="mt-1 block w-full py-2 px-3 bg-gray-950 border border-gray-800 text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="low" {{ old('severity') === 'low' ? 'selected' : '' }}>Low — Minor degradation</option>
                        <option value="medium" {{ old('severity', 'medium') === 'medium' ? 'selected' : '' }}>Medium — Partial service disruption</option>
                        <option value="high" {{ old('severity') === 'high' ? 'selected' : '' }}>High — Major outage</option>
                        <option value="critical" {{ old('severity') === 'critical' ? 'selected' : '' }}>Critical — Total system failure</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('severity')" />
                </div>

                <!-- Description -->
                <div>
                    <x-input-label for="description" value="Incident Description & Initial Diagnostics" />
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full bg-gray-950 border border-gray-800 text-gray-100 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-3" placeholder="Provide details about symptoms, affected users, and immediate actions taken...">{{ old('description') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-800">
                    <a href="{{ route('projects.incidents.index', $project) }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold rounded-lg transition">
                        Cancel
                    </a>
                    <x-primary-button>
                        Create Incident Record
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
