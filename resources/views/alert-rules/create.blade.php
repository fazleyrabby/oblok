<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 leading-tight">
            New Alert Rule for {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <form method="POST" action="{{ route('projects.alert-rules.store', $project) }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Rule Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus placeholder="e.g. Payment Service Down" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description (Optional)')" />
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-700 bg-gray-950 text-white text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="metric" :value="__('Metric')" />
                            <select id="metric" name="metric" class="mt-1 block w-full rounded-lg border-gray-700 bg-gray-950 text-white text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach (\App\Enums\AlertMetric::cases() as $metric)
                                    <option value="{{ $metric->value }}" {{ old('metric') === $metric->value ? 'selected' : '' }}>{{ $metric->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('metric')" />
                        </div>

                        <div>
                            <x-input-label for="comparison" :value="__('Condition')" />
                            <select id="comparison" name="comparison" class="mt-1 block w-full rounded-lg border-gray-700 bg-gray-950 text-white text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach (\App\Enums\AlertComparison::cases() as $comparison)
                                    <option value="{{ $comparison->value }}" {{ old('comparison') === $comparison->value ? 'selected' : '' }}>{{ $comparison->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('comparison')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="threshold" :value="__('Threshold')" />
                            <x-text-input id="threshold" name="threshold" type="number" class="mt-1 block w-full" :value="old('threshold')" min="0" />
                            <x-input-error class="mt-2" :messages="$errors->get('threshold')" />
                        </div>

                        <div>
                            <x-input-label for="window_minutes" :value="__('Window (Minutes)')" />
                            <x-text-input id="window_minutes" name="window_minutes" type="number" class="mt-1 block w-full" :value="old('window_minutes', 5)" min="1" required />
                            <x-input-error class="mt-2" :messages="$errors->get('window_minutes')" />
                        </div>

                        <div>
                            <x-input-label for="cooldown_minutes" :value="__('Cooldown (Minutes)')" />
                            <x-text-input id="cooldown_minutes" name="cooldown_minutes" type="number" class="mt-1 block w-full" :value="old('cooldown_minutes', 15)" min="0" required />
                            <x-input-error class="mt-2" :messages="$errors->get('cooldown_minutes')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="severity" :value="__('Severity')" />
                            <select id="severity" name="severity" class="mt-1 block w-full rounded-lg border-gray-700 bg-gray-950 text-white text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach (\App\Enums\AlertSeverity::cases() as $severity)
                                    <option value="{{ $severity->value }}" {{ old('severity', 'warning') === $severity->value ? 'selected' : '' }}>{{ $severity->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('severity')" />
                        </div>

                        <div>
                            <x-input-label for="channels" :value="__('Notification Channels')" />
                            <select id="channels" name="channel_ids[]" multiple class="mt-1 block w-full rounded-lg border-gray-700 bg-gray-950 text-white text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($channels as $channel)
                                    <option value="{{ $channel->id }}" @selected(in_array($channel->id, old('channel_ids', [])))>{{ $channel->name }} ({{ $channel->type->label() }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Tip: create notification channels before linking them to rules.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('channel_ids')" />
                        </div>
                    </div>

                    <label class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="enabled" value="1" class="rounded border-gray-700 bg-gray-950 text-indigo-600 focus:ring-indigo-500" @checked(old('enabled', true))>
                        <span class="text-sm text-gray-300">Enable rule</span>
                    </label>

                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>{{ __('Create Alert Rule') }}</x-primary-button>
                        <a href="{{ route('projects.alert-rules.index', $project) }}" class="text-sm font-medium text-gray-400 hover:text-gray-200">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
