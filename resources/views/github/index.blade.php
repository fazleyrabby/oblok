<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    GitHub Integration for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Repository context, commits, and pull requests</p>
            </div>
            @if($integration && auth()->user()->can('sync', $integration))
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('projects.github.sync', $project) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                            Sync Now
                        </button>
                    </form>
                    <form method="POST" action="{{ route('projects.github.destroy', $project) }}"
                          onsubmit="return confirm('Disconnect the GitHub integration and remove all captured repository data?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                            Disconnect
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($errors->has('sync'))
            <div class="bg-red-900/30 border border-red-800 rounded-xl px-4 py-3 text-sm text-red-300">
                {{ $errors->first('sync') }}
            </div>
        @endif

        @unless($integration)
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-200">Link a GitHub repository</h3>
                <p class="mt-1 text-sm text-gray-400">
                    Connect a repository to surface its recent commits and pull requests inside Atlas.
                    Use a personal access token with <span class="font-mono text-xs">repo</span> scope.
                </p>

                <form method="POST" action="{{ route('projects.github.store', $project) }}" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label for="repository" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Repository (owner/name)</label>
                        <input type="text" name="repository" id="repository" required value="{{ old('repository') }}" placeholder="acme/platform"
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('repository')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="access_token" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Access Token</label>
                        <input type="password" name="access_token" id="access_token" required placeholder="ghp_..."
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('access_token')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                            Connect Repository
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-gray-800 flex items-center justify-center">
                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-200">{{ $integration->repositorySlug() }}</h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Default branch
                                <span class="font-mono text-gray-300">{{ $integration->default_branch ?? '—' }}</span>
                                &middot; Last synced {{ $integration->last_synced_at?->diffForHumans() ?? 'never' }}
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $integration->enabled ? 'text-emerald-400 border-emerald-800' : 'text-gray-400 border-gray-800' }}">
                        {{ $integration->enabled ? 'Connected' : 'Paused' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Recent Commits</h3>
                    @if($commits->isEmpty())
                        <p class="text-sm text-gray-500">No commits captured yet. Run a sync to fetch repository history.</p>
                    @else
                        <ul class="divide-y divide-gray-800">
                            @foreach($commits as $commit)
                                <li class="py-3 flex items-start gap-3">
                                    <span class="font-mono text-xs text-indigo-400 mt-0.5">{{ substr($commit->sha, 0, 7) }}</span>
                                    <div class="min-w-0">
                                        <p class="text-sm text-gray-200 truncate">{{ $commit->message }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $commit->author_name }} &middot; {{ $commit->authored_at?->diffForHumans() ?? '—' }}
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Open Pull Requests</h3>
                    @if($pullRequests->isEmpty())
                        <p class="text-sm text-gray-500">No pull requests captured yet. Run a sync to fetch repository context.</p>
                    @else
                        <ul class="divide-y divide-gray-800">
                            @foreach($pullRequests as $pullRequest)
                                <li class="py-3 flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm text-gray-200 truncate">{{ $pullRequest->title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            #{{ $pullRequest->number }} by {{ $pullRequest->author_name }} &middot; {{ $pullRequest->opened_at?->diffForHumans() ?? '—' }}
                                        </p>
                                    </div>
                                    <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $pullRequest->state === 'open' ? 'text-emerald-400 border-emerald-800' : 'text-gray-400 border-gray-800' }}">
                                        {{ $pullRequest->state }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endunless
    </div>
</x-app-layout>
