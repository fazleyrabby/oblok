<x-app-layout>
    <x-slot name="header">
    <div class="mb-4">
        <x-project-switcher :projects="$projects" :current="$project" :route="'projects.messaging.index'" />
    </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Messaging for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Connect chat platforms and send messages from oblok</p>
            </div>
            @if($integration && auth()->user()->can('delete', $integration))
                <form method="POST" action="{{ route('projects.messaging.destroy', [$project, $integration]) }}"
                      onsubmit="return confirm('Disconnect this messaging integration and remove its stored credentials?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                        Disconnect
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('status'))
            <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-sm text-gray-300">
                {{ session('status') }}
            </div>
        @endif

        @unless($integration)
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-200">Connect a chat platform</h3>
                <p class="mt-1 text-sm text-gray-400">
                    Connect a Slack workspace with a bot token to list channels and post messages from oblok.
                </p>

                <form method="POST" action="{{ route('projects.messaging.store', $project) }}" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label for="platform" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Platform</label>
                        <select name="platform" id="platform" class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="slack">Slack</option>
                        </select>
                    </div>
                    <div>
                        <label for="bot_token" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Bot Token</label>
                        <input type="password" name="bot_token" id="bot_token" required value="{{ old('bot_token') }}" placeholder="xoxb-..."
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('bot_token')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                            Connect Workspace
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-gray-800 flex items-center justify-center">
                            <svg class="w-7 h-7 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6.13 11.98A1.67 1.67 0 0 1 4.45 10.3a1.68 1.68 0 0 1 1.68-1.68h1.68v1.68c0 .37.3.68.67.68zM6.99 6.06A1.67 1.67 0 0 1 5.31 4.38a1.68 1.68 0 0 1 1.68-1.68h1.68v1.68c0 .37.3.68.67.68h1.01V5.31a1.68 1.68 0 0 1 1.68-1.68A1.67 1.67 0 0 1 13.7 5.3v4.72a1.67 1.67 0 0 1-1.68 1.67H6.99a1.67 1.67 0 0 1-1.67-1.63v-1zm0 0A1.67 1.67 0 0 1 8.67 4.38V6.06h-1.68zM17.87 12.01a1.67 1.67 0 0 1 1.68 1.68 1.68 1.68 0 0 1-1.68 1.68h-1.68v-1.68a.67.67 0 0 0-.67-.68h-1.01v1.68c0 .37.3.68.67.68h1.68v1.68a1.68 1.68 0 0 1-1.68 1.68 1.67 1.67 0 0 1-1.68-1.68v-4.72a1.67 1.67 0 0 1 1.68-1.67h4.69a1.67 1.67 0 0 1 1.68 1.63v.01zm0 0a1.67 1.67 0 0 0-1.68-1.68v1.68h1.68zM12.01 17.87a1.67 1.67 0 0 1-1.68 1.68 1.68 1.68 0 0 1-1.68-1.68v-1.68h1.68a.67.67 0 0 0 .68-.67v-1.01h-1.68c-.37 0-.68.3-.68.67v1.68H7.28a1.67 1.67 0 0 1-1.68-1.68 1.68 1.68 0 0 1 1.68-1.68h4.72a1.67 1.67 0 0 1 1.68 1.68v4.69a1.67 1.67 0 0 1-1.67 1.68h-.01zM6.06 17.87a1.67 1.67 0 0 1-1.68-1.68 1.68 1.68 0 0 1 1.68-1.68h1.68v1.68a.67.67 0 0 0 .68.67h1.01v-1.68c0-.37-.3-.68-.67-.68H7.28a1.68 1.68 0 0 1-1.68 1.68v1.68h-.67z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-200">{{ $integration->name }}</h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $integration->platform->label() }}
                                &middot; Last connected {{ $integration->last_connected_at?->diffForHumans() ?? 'never' }}
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $integration->enabled ? 'text-emerald-400 border-emerald-800' : 'text-gray-400 border-gray-800' }}">
                        {{ $integration->enabled ? 'Connected' : 'Paused' }}
                    </span>
                </div>
            </div>

            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-1">Send a message</h3>
                @if($channelError)
                    <p class="text-xs text-red-400 mt-2">{{ $channelError }}</p>
                @endif
                <form method="POST" action="{{ route('projects.messaging.send', [$project, $integration]) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="channel" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Channel</label>
                        <select name="channel" id="channel" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @if($integration->channel)
                                <option value="{{ $integration->channel }}">{{ $integration->channel }}</option>
                            @endif
                            @foreach($channels as $channel)
                                <option value="{{ $channel->id }}">#{{ $channel->name }}</option>
                            @endforeach
                        </select>
                        @error('channel')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="message" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Message</label>
                        <textarea name="message" id="message" required rows="3" placeholder="Write a message to post…"
                                  class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                        Send Message
                    </button>
                </form>
            </div>
        @endunless
    </div>
</x-app-layout>
