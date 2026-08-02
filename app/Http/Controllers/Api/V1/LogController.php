<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Logs\IngestLogEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\IngestLogRequest;
use App\Http\Resources\LogEntryResource;
use App\Models\LogEntry;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LogController extends Controller
{
    /**
     * Display a paginated list of log entries for a project.
     */
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [LogEntry::class, $project]);

        $query = $project->logs();

        if ($request->has('level') && ! empty($request->query('level'))) {
            $query->where('level', $request->query('level'));
        }

        if ($request->has('search') && ! empty($request->query('search'))) {
            $query->where('message', 'like', '%'.$request->query('search').'%');
        }

        $logs = $query->paginate(25);

        return LogEntryResource::collection($logs);
    }

    /**
     * Store/Ingest a new log entry for a project.
     */
    public function store(IngestLogRequest $request, Project $project, IngestLogEntry $ingestLog): JsonResponse
    {
        $log = $ingestLog->handle($project, $request->validated());

        return (new LogEntryResource($log))
            ->response()
            ->setStatusCode(201);
    }
}
