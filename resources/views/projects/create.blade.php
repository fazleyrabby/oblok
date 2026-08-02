<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Project') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Project Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus autocomplete="name" placeholder="e.g. Atlas Core" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="slug" :value="__('Slug (Optional)')" />
                        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug')" placeholder="Auto-generated if left blank" />
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description (Optional)')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Brief overview of the project and service context...">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save Project') }}</x-primary-button>
                        <a href="{{ route('projects.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
