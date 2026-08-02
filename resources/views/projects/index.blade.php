<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Projects') }}
            </h2>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                + {{ __('New Project') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Search & Filters -->
                <form method="GET" action="{{ route('projects.index') }}" class="mb-6 flex flex-col sm:flex-row items-center gap-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search projects by name..." class="w-full sm:w-80 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <label class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" name="archived" value="1" {{ request('archived') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2">Show Archived Projects</span>
                    </label>
                    <button type="submit" class="px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-xs font-semibold uppercase tracking-widest">Filter</button>
                </form>

                @if($projects->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No projects found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first project to monitor services.</p>
                        <div class="mt-6">
                            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest">
                                + Create Project
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($projects as $project)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-5 flex flex-col justify-between hover:border-indigo-500 transition">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <a href="{{ route('projects.show', $project) }}" class="text-lg font-bold text-gray-900 dark:text-white hover:text-indigo-600">
                                            {{ $project->name }}
                                        </a>
                                        @if($project->isArchived())
                                            <span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 rounded-full">Archived</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 rounded-full">Active</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">slug: {{ $project->slug }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 mb-4">
                                        {{ $project->description ?? 'No description provided.' }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <span class="text-xs text-gray-400">Updated {{ $project->updated_at->diffForHumans() }}</span>
                                    <a href="{{ route('projects.show', $project) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">View Dashboard &rarr;</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $projects->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
