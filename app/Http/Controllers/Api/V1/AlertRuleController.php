<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlertRuleRequest;
use App\Http\Requests\UpdateAlertRuleRequest;
use App\Http\Resources\AlertRuleResource;
use App\Models\AlertRule;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AlertRuleController extends Controller
{
    /**
     * Display a paginated list of alert rules for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [AlertRule::class, $project]);

        $alertRules = $project->alertRules()->with('channels')->latest()->paginate(15);

        return AlertRuleResource::collection($alertRules);
    }

    /**
     * Create a new alert rule.
     */
    public function store(StoreAlertRuleRequest $request, Project $project): JsonResponse
    {
        $alertRule = $project->alertRules()->create($request->safe()->except('channel_ids'));

        if ($request->filled('channel_ids')) {
            $alertRule->channels()->sync($request->input('channel_ids'));
        }

        return (new AlertRuleResource($alertRule->load('channels')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified alert rule.
     */
    public function show(Project $project, AlertRule $alertRule): AlertRuleResource
    {
        $this->authorize('view', $alertRule);

        return new AlertRuleResource($alertRule->load('channels'));
    }

    /**
     * Update the alert rule.
     */
    public function update(UpdateAlertRuleRequest $request, Project $project, AlertRule $alertRule): AlertRuleResource
    {
        $alertRule->update($request->safe()->except('channel_ids'));

        if ($request->has('channel_ids')) {
            $alertRule->channels()->sync($request->input('channel_ids', []));
        }

        return new AlertRuleResource($alertRule->fresh()->load('channels'));
    }

    /**
     * Delete the alert rule.
     */
    public function destroy(Project $project, AlertRule $alertRule): Response
    {
        $this->authorize('delete', $alertRule);

        $alertRule->delete();

        return response()->noContent();
    }
}
