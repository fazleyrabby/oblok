<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Project') }}: {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Project Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $project->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="slug" :value="__('Slug')" />
                        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $project->slug)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $project->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Update Project') }}</x-primary-button>
                        <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-red-200 dark:border-red-900">
                <h3 class="text-md font-semibold text-red-600 dark:text-red-400 mb-2">Delete Project</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Once deleted, the project will be soft-deleted and can no longer receive monitoring checks.</p>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Are you sure you want to delete this project?')">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('Delete Project') }}</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
