@php
    $navProject = request()->route('project')
        ?? \App\Models\Project::query()
            ->where(function ($q) {
                $q->where('user_id', Auth::id())
                  ->orWhereHas('members', fn ($m) => $m->where('users.id', Auth::id()));
            })
            ->active()
            ->orderBy('name')
            ->first();
@endphp

<aside :class="{ 'transition-all duration-200 ease-in-out': animated, 'w-20 max-md:-translate-x-full': sidebarCollapsed, 'w-64': !sidebarCollapsed }"
       class="flex flex-col fixed inset-y-0 left-0 w-64 bg-gray-900 border-r border-gray-800 text-gray-300 z-30">

    <!-- Top Logo & Collapse Toggle -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
            <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center font-bold text-white shadow-sm flex-shrink-0">
                O
            </div>
            <span x-show="!sidebarCollapsed" class="font-bold text-lg text-white tracking-tight whitespace-nowrap">
                oblok
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
    <div class="flex-1 overflow-y-auto custom-scrollbar py-4 px-3 space-y-6"
         x-init="$nextTick(() => {
             if (sidebarCollapsed) return;
             const active = $el.querySelector('a.bg-indigo-600');
             if (active) active.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
         })">
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

                <a href="{{ $navProject ? route('projects.resources.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.resources.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Server Resources</span>
                </a>

                <a href="{{ $navProject ? route('projects.request-analytics.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.request-analytics.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Request Analytics</span>
                </a>

                <a href="{{ route('queues.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('queues.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Queues & Workers</span>
                </a>

                <a href="{{ $navProject ? route('projects.alerts.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.alerts.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Alerts</span>
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

                <a href="{{ $navProject ? route('projects.runbooks.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.runbooks.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Runbooks</span>
                </a>

                <a href="{{ $navProject ? route('projects.alert-rules.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.alert-rules.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Alert Rules</span>
                </a>

                <a href="{{ $navProject ? route('projects.notification-channels.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.notification-channels.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Notification Channels</span>
                </a>

                <a href="{{ $navProject ? route('projects.webhooks.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.webhooks.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Webhooks</span>
                </a>

                <a href="{{ $navProject ? route('projects.scheduled-tasks.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.scheduled-tasks.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Scheduler</span>
                </a>

                <a href="{{ $navProject ? route('projects.github.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.github.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">GitHub</span>
                </a>

                <a href="{{ $navProject ? route('projects.messaging.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.messaging.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Messaging</span>
                </a>

                <a href="{{ $navProject ? route('projects.metrics.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.metrics.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Metrics</span>
                </a>

                <a href="{{ $navProject ? route('projects.api-keys.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.api-keys.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">API Keys</span>
                </a>

                <a href="{{ $navProject ? route('projects.ai-assistant', $navProject) : '#' }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.ai-assistant') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">AI Assistant</span>
                </a>

                <a href="{{ $navProject ? route('projects.ai-settings.index', $navProject) : '#' }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.ai-settings.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">AI Settings</span>
                </a>

                <a href="{{ $navProject ? route('projects.members.index', $navProject) : route('projects.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                   class="flex items-center py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('projects.members.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="ml-3">Team Members</span>
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
