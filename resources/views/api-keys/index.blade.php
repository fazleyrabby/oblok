<x-app-layout>
    <x-slot name="header">
    <div class="mb-4">
        <x-project-switcher :projects="$projects" :current="$project" :route="'projects.api-keys.index'" />
    </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    API Keys for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Machine-to-machine access to the REST API for your services</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('createdApiKey'))
            <div class="bg-emerald-900/30 border border-emerald-800 rounded-xl px-4 py-3">
                <p class="text-sm font-semibold text-emerald-300">{{ session('createdApiKeyName') }}</p>
                <p class="mt-1 text-xs text-emerald-400">
                    Copy this token now — it will not be shown again.
                </p>
                <div class="mt-2 flex items-center gap-2">
                    <code class="flex-1 font-mono text-xs text-gray-100 bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 break-all">{{ session('createdApiKey') }}</code>
                    <button type="button"
                            onclick="navigator.clipboard.writeText('{{ session('createdApiKey') }}')"
                            class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                        Copy
                    </button>
                </div>
            </div>
        @endif

        @if(session('status'))
            <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-sm text-gray-300">
                {{ session('status') }}
            </div>
        @endif

        @if(auth()->user()->can('create', [\App\Models\ApiKey::class, $project]))
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-200">Issue a new API key</h3>
                <p class="mt-1 text-sm text-gray-400">
                    Use the token in the <span class="font-mono text-xs">Authorization: Bearer &lt;token&gt;</span> header
                    when calling the REST API from your services, CI, or containers.
                </p>

                <form method="POST" action="{{ route('projects.api-keys.store', $project) }}" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="CI deploy token"
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="expires_at" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Expires (optional)</label>
                        <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}"
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('expires_at')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                            Generate API Key
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Issued Keys</h3>

            @if($keys->isEmpty())
                <p class="text-sm text-gray-500">No API keys issued yet.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wider border-b border-gray-800">
                            <th class="pb-2 pr-4 font-semibold">Name</th>
                            <th class="pb-2 pr-4 font-semibold">Key</th>
                            <th class="pb-2 pr-4 font-semibold">Requests</th>
                            <th class="pb-2 pr-4 font-semibold">Last Used</th>
                            <th class="pb-2 pr-4 font-semibold">Expires</th>
                            <th class="pb-2 font-semibold">Status</th>
                            <th class="pb-2 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach($keys as $key)
                            <tr>
                                <td class="py-3 pr-4 text-gray-200 font-medium">{{ $key->name }}</td>
                                <td class="py-3 pr-4 font-mono text-xs text-gray-400">{{ $key->key_prefix }}…</td>
                                <td class="py-3 pr-4 text-gray-400">{{ $key->requests_count }}</td>
                                <td class="py-3 pr-4 text-gray-400">{{ $key->last_used_at?->diffForHumans() ?? '—' }}</td>
                                <td class="py-3 pr-4 text-gray-400">{{ $key->expires_at?->toDateString() ?? 'Never' }}</td>
                                <td class="py-3 pr-4">
                                    @if($key->isRevoked())
                                        <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase text-gray-400 border-gray-800">Revoked</span>
                                    @elseif($key->isExpired())
                                        <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase text-gray-400 border-gray-800">Expired</span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase text-emerald-400 border-emerald-800">Active</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    @if(! $key->isRevoked() && auth()->user()->can('delete', $key))
                                        <form method="POST" action="{{ route('projects.api-keys.destroy', [$project, $key]) }}"
                                              onsubmit="return confirm('Revoke this API key? Requests using it will be rejected immediately.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-300 uppercase tracking-wider">Revoke</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-app-layout>
