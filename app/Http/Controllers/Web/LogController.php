<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    /**
     * Display real-time log inspector Blade view.
     */
    public function index(Request $request, Project $project): View
    {
        $this->authorize('viewAny', [LogEntry::class, $project]);

        $query = $project->logs();

        if ($request->filled('level')) {
            $query->where('level', $request->query('level'));
        }

        if ($request->filled('search')) {
            $query->where('message', 'like', '%'.$request->query('search').'%');
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('logs.index', compact('project', 'logs'));
    }
}
