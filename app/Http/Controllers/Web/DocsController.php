<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocsController extends Controller
{
    public function index()
    {
        $pages = config('docs.pages');

        return view('docs.index', compact('pages'));
    }

    public function show(Request $request, string $page)
    {
        $pages = config('docs.pages');

        if (! array_key_exists($page, $pages) || empty($pages[$page]['file'])) {
            abort(404);
        }

        $manifest = $pages[$page];
        $path = base_path($manifest['file']);

        if (! File::exists($path)) {
            abort(404);
        }

        $raw = File::get($path);
        $html = Str::markdown($raw, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return view('docs.show', [
            'title' => $manifest['title'],
            'pages' => $pages,
            'current' => $page,
            'content' => $html,
        ]);
    }
}
