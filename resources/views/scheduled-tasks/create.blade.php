<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 leading-tight">
            New Scheduled Task for {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <form method="POST" action="{{ route('projects.scheduled-tasks.store', $project) }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Task Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus placeholder="e.g. Nightly Database Backup" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description (Optional)')" />
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-700 bg-gray-950 text-white text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="command" :value="__('Command')" />
                            <x-text-input id="command" name="command" type="text" class="mt-1 block w-full" :value="old('command')" required placeholder="e.g. php artisan backup:run" />
                            <x-input-error class="mt-2" :messages="$errors->get('command')" />
                        </div>

                        <div>
                            <x-input-label for="timezone" :value="__('Timezone')" />
                            <x-text-input id="timezone" name="timezone" type="text" class="mt-1 block w-full" :value="old('timezone', 'UTC')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="cron_expression" :value="__('Cron Expression')" />
                        <x-text-input id="cron_expression" name="cron_expression" type="text" class="mt-1 block w-full" :value="old('cron_expression')" required placeholder="e.g. 0 2 * * *" />
                        <p class="text-xs text-gray-500 mt-1">Five-field cron syntax (minute hour day month weekday).</p>
                        <x-input-error class="mt-2" :messages="$errors->get('cron_expression')" />
                    </div>

                    <label class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="enabled" value="1" class="rounded border-gray-700 bg-gray-950 text-indigo-600 focus:ring-indigo-500" @checked(old('enabled', true))>
                        <span class="text-sm text-gray-300">Task is active</span>
                    </label>

                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>{{ __('Create Scheduled Task') }}</x-primary-button>
                        <a href="{{ route('projects.scheduled-tasks.index', $project) }}" class="text-sm font-medium text-gray-400 hover:text-gray-200">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
