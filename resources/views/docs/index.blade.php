@extends('layouts.docs')

@section('content')
    <div class="docs-prose">
        <h1>oblok Documentation</h1>
        <p>
            oblok is a self-hosted Developer Operations Platform. These guides cover running
            your own instance and connecting your applications to it. Use the navigation on the
            left to jump between topics.
        </p>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach($pages as $slug => $page)
            <a href="{{ route('docs.show', $slug) }}"
               class="group block rounded-xl border border-gray-800 bg-gray-900/50 p-5 transition hover:border-indigo-600/60 hover:bg-gray-900">
                <h3 class="text-base font-semibold text-gray-100 group-hover:text-indigo-400 transition">
                    {{ $page['title'] }}
                </h3>
                <p class="mt-1.5 text-sm text-gray-400 leading-relaxed">{{ $page['summary'] }}</p>
                <span class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-indigo-400">
                    Read more
                    <span aria-hidden="true">&rarr;</span>
                </span>
            </a>
        @endforeach
    </div>
@endsection
