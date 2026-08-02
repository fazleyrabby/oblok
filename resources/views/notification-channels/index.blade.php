<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Notification Channels for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Delivery destinations for alert notifications (email, Slack, webhook)</p>
            </div>
            <a href="{{ route('projects.notification-channels.create', $project) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                + New Channel
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($channels->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-3 text-base font-semibold text-gray-200">No notification channels configured</h3>
                <p class="mt-1 text-sm text-gray-400">Add email, Slack, or generic webhook destinations to receive alert notifications.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.notification-channels.create', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-wider">
                        + Add First Channel
                    </a>
                </div>
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                            <tr>
                                <th class="py-3 px-4">Channel</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($channels as $channel)
                                <tr class="hover:bg-gray-850 transition">
                                    <td class="py-3 px-4 font-semibold text-gray-200">{{ $channel->name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase {{ $channel->type->value === 'mail' ? 'text-blue-400 bg-blue-500/10 border-blue-500/20' : ($channel->type->value === 'slack' ? 'text-purple-400 bg-purple-500/10 border-purple-500/20' : 'text-gray-300 bg-gray-500/10 border-gray-500/20') }}">
                                            {{ $channel->type->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($channel->enabled)
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">Enabled</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-gray-800 text-gray-400 border border-gray-700 rounded-full">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('projects.notification-channels.edit', [$project, $channel]) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                                            Manage &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
