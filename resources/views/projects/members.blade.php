<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Team Members — {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Manage project access and role permissions</p>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                &larr; Back to Project Overview
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('status'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm rounded-xl">
                {{ session('status') }}
            </div>
        @endif

        <!-- Add Member Card -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-4">Add Team Member</h3>
            <form method="POST" action="{{ route('projects.members.store', $project) }}" class="flex flex-col sm:flex-row items-center gap-3">
                @csrf
                <div class="flex-1 w-full">
                    <x-text-input id="email" name="email" type="email" class="w-full" placeholder="colleague@atlas.dev" required />
                    <x-input-error class="mt-1" :messages="$errors->get('email')" />
                </div>
                <div class="w-full sm:w-48">
                    <select name="role" class="w-full py-2 px-3 bg-gray-950 border border-gray-800 text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="operator">Operator (Read/Write)</option>
                        <option value="admin">Admin (Full Control)</option>
                        <option value="viewer">Viewer (Read Only)</option>
                    </select>
                </div>
                <x-primary-button class="w-full sm:w-auto">
                    Add Member
                </x-primary-button>
            </form>
        </div>

        <!-- Members Table -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-white mb-4">Project Access List</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-950 text-xs text-gray-400 uppercase tracking-wider border-b border-gray-800">
                        <tr>
                            <th class="py-3 px-4">Member Name</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <!-- Owner Row -->
                        <tr class="hover:bg-gray-850 transition">
                            <td class="py-3 px-4 font-semibold text-white">{{ $project->user->name }}</td>
                            <td class="py-3 px-4 text-xs text-gray-400">{{ $project->user->email }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-full uppercase">
                                    Owner
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-xs text-gray-500">Permanent</td>
                        </tr>

                        <!-- Added Members Rows -->
                        @foreach($members as $member)
                            <tr class="hover:bg-gray-850 transition">
                                <td class="py-3 px-4 font-semibold text-gray-200">{{ $member->name }}</td>
                                <td class="py-3 px-4 text-xs text-gray-400">{{ $member->email }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-full uppercase">
                                        {{ $member->pivot->role }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form method="POST" action="{{ route('projects.members.destroy', [$project, $member]) }}" onsubmit="return confirm('Remove team member?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-300">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
