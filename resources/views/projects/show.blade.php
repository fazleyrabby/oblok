<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">slug: {{ $project->slug }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @can('view', $project)
                    <a href="{{ route('projects.members.index', $project) }}" class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs font-semibold uppercase tracking-widest hover:bg-gray-300">
                        Team Members
                    </a>
                @endcan
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs font-semibold uppercase tracking-widest hover:bg-gray-300">
                        Edit Project
                    </a>
                    <form method="POST" action="{{ route('projects.archive', $project) }}" class="inline">
                        @csrf
                        <input type="hidden" name="archive" value="{{ $project->isArchived() ? '0' : '1' }}">
                        <button type="submit" class="px-3 py-1.5 bg-amber-500 text-white rounded text-xs font-semibold uppercase tracking-widest hover:bg-amber-600">
                            {{ $project->isArchived() ? 'Unarchive' : 'Archive' }}
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Project Header Summary Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Project Overview</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                    {{ $project->description ?? 'No detailed description specified.' }}
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs text-gray-500 dark:text-gray-400 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <div>
                        <span class="block font-medium text-gray-700 dark:text-gray-300">Status</span>
                        <span>{{ $project->isArchived() ? 'Archived' : 'Active' }}</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-700 dark:text-gray-300">Created</span>
                        <span>{{ $project->created_at->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-700 dark:text-gray-300">Services Monitored</span>
                        <span>0 Services</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-700 dark:text-gray-300">ID</span>
                        <span class="font-mono text-xs">{{ Str::limit($project->id, 8) }}</span>
                    </div>
                </div>
            </div>

            <!-- Monitored Services Placeholder Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Monitored Services</h3>
                    <span class="text-xs text-gray-400">Monitoring module coming in v0.1 next step</span>
                </div>
                <div class="text-center py-8 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No services connected to this project yet.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
