@props(['refreshMs' => 5000, 'id' => null])

<div class="flex items-center gap-3"
     x-data="liveStream({
        refreshMs: {{ $refreshMs }},
        onTick: () => window.dispatchEvent(new CustomEvent('live-tick')),
     })"
     x-init="init()">
    <div class="flex items-center gap-2">
        <span class="relative flex h-2.5 w-2.5">
            <span x-show="connected" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span x-bind:class="connected ? 'bg-emerald-500' : 'bg-gray-500'" class="relative inline-flex rounded-full h-2.5 w-2.5"></span>
        </span>
        <button type="button"
                @click="toggle()"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg border bg-gray-900 uppercase tracking-wider transition"
                :class="connected ? 'text-emerald-400 border-emerald-800' : 'text-gray-400 border-gray-800'">
            <span x-text="connected ? 'Live: ON' : 'Live: OFF'"></span>
        </button>
    </div>
</div>
