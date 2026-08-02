@props(['projects', 'current', 'route'])

<div class="flex items-center gap-2">
    <label for="project-switcher" class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</label>
    <select id="project-switcher" onchange="window.location.href = this.value"
            class="py-2 px-3 bg-gray-950 border border-gray-800 text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        @foreach($projects as $p)
            <option value="{{ route($route, $p) }}" {{ $p->id === $current->id ? 'selected' : '' }}>
                {{ $p->name }}
            </option>
        @endforeach
    </select>
</div>
