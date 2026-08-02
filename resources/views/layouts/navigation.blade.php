<nav class="bg-gray-900 border-b border-gray-800 text-gray-100 sticky top-0 z-20">
    <!-- Primary Navigation Bar -->
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Side: Project Selector Dropdown -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Sidebar Toggle -->
                <button @click="sidebarCollapsed = !sidebarCollapsed"
                        class="md:hidden p-2 -ml-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-800 focus:outline-none"
                        aria-label="Toggle Sidebar">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Project Selector Dropdown -->
                <div class="flex items-center space-x-2 text-sm font-medium">
                    <span class="text-gray-500 hidden sm:inline">Workspace /</span>
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-1.5 border border-gray-800 rounded-lg text-sm font-semibold text-gray-200 bg-gray-950 hover:bg-gray-800 focus:outline-none transition">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                                <span>All Projects</span>
                                <svg class="ms-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-400 border-b border-gray-800">
                                Switch Project Context
                            </div>
                            <x-dropdown-link :href="route('projects.index')">
                                {{ __('All Projects Overview') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('projects.create')">
                                {{ __('+ Create New Project') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Right Side: User Dropdown -->
            <div class="flex items-center ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-gray-800 text-sm leading-4 font-medium rounded-lg text-gray-300 bg-gray-950 hover:bg-gray-800 focus:outline-none transition">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile & Settings') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
