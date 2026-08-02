@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-700 bg-gray-950 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm px-3 py-2']) }}>
