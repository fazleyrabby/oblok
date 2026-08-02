<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKeyProjectScope
{
    /**
     * Enforce that an API-key-authenticated request only reaches its scoped project.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->attributes->get('oblok_api_key');

        if (! $key) {
            return $next($request);
        }

        $project = $request->route('project');

        if ($project && (string) $key->project_id !== (string) $project->id) {
            abort(403, 'This API key is not authorized for this project.');
        }

        return $next($request);
    }
}
