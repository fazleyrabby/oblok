@php
    $navProject = \App\Models\Project::where('user_id', Auth::id())->active()->first();
@endphp

<aside :class="{ 'transition-all duration-200 ease-in-out': animated, 'w-20': sidebarCollapsed, 'w-64': !sidebarCollapsed }"
       class="hidden md:flex flex-col fixed inset-y-0 left-0 w-64 bg-gray-900 border-r border-gray-800 text-gray-300 z-30">

    <!-- Top Logo & Collapse Toggle -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
            <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center font-bold text-white shadow-sm flex-shrink-0">
                A
            </div>
            <span x-show="!sidebarCollapsed" class="font-bold text-lg text-white tracking-tight whitespace-nowrap">
                Atlas
            </span>
        </a>
        <button @click="sidebarCollapsed = !sidebarCollapsed"
                title="Toggle Sidebar (Hotkey: [ )"
                class="p-1.5 rounded-md hover:bg-gray-800 text-gray-400 hover:text-gray-200 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
        </button>
    </div>

    <!-- Navigation Items -->
    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
        <!-- Core Section -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Core
            </div>
            <nav class="space-y-1">
                <a href="{{ route('dashboard') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Dashboard</span>
                </a>

                <a href="{{ route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.index', 'projects.create', 'projects.show', 'projects.edit') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Projects</span>
                </a>
            </nav>
        </div>

        <!-- Observability Section -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Observability
            </div>
            <nav class="space-y-1">
                <a href="{{ $navProject ? route('projects.services.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.services.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Services</span>
                </a>

                <a href="{{ $navProject ? route('projects.deployments.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.deployments.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Deployments</span>
                </a>

                <a href="{{ $navProject ? route('projects.logs.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.logs.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Logs Stream</span>
                </a>

                <a href="{{ route('queues.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('queues.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Queues & Workers</span>
                </a>
            </nav>
        </div>

        <!-- Account Section -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Management
            </div>
            <nav class="space-y-1">
                <a href="{{ $navProject ? route('projects.incidents.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.incidents.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Incidents</span>
                </a>

                <a href="{{ route('profile.edit') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('profile.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Settings</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- User Footer Profile -->
    <div class="p-4 border-t border-gray-800 flex items-center justify-between">
        <div class="flex items-center space-x-3 overflow-hidden">
            <div class="w-8 h-8 rounded-full bg-gray-700 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div x-show="!sidebarCollapsed" class="overflow-hidden">
                <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
