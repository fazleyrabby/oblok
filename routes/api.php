<?php

use App\Http\Controllers\Api\V1\DeploymentController;
use App\Http\Controllers\Api\V1\DeploymentWebhookController;
use App\Http\Controllers\Api\V1\IncidentController;
use App\Http\Controllers\Api\V1\LogController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectMemberController;
use App\Http\Controllers\Api\V1\QueueController;
use App\Http\Controllers\Api\V1\ServiceController;
use Illuminate\Support\Facades\Route;

// Public Webhooks endpoint
Route::post('v1/webhooks/deployments/{project:slug}', DeploymentWebhookController::class)
    ->name('api.v1.webhooks.deployments');

// Authenticated REST API endpoints
Route::prefix('v1')->middleware('auth')->group(function () {
    Route::apiResource('projects', ProjectController::class)->names([
        'index' => 'api.v1.projects.index',
        'store' => 'api.v1.projects.store',
        'show' => 'api.v1.projects.show',
        'update' => 'api.v1.projects.update',
        'destroy' => 'api.v1.projects.destroy',
    ]);
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('api.v1.projects.archive');

    Route::apiResource('projects.services', ServiceController::class)->names([
        'index' => 'api.v1.projects.services.index',
        'store' => 'api.v1.projects.services.store',
        'show' => 'api.v1.projects.services.show',
        'update' => 'api.v1.projects.services.update',
        'destroy' => 'api.v1.projects.services.destroy',
    ]);
    Route::post('projects/{project}/services/{service}/ping', [ServiceController::class, 'ping'])->name('api.v1.projects.services.ping');

    Route::apiResource('projects.deployments', DeploymentController::class)->only(['index', 'show'])->names([
        'index' => 'api.v1.projects.deployments.index',
        'show' => 'api.v1.projects.deployments.show',
    ]);

    Route::apiResource('projects.incidents', IncidentController::class)->names([
        'index' => 'api.v1.projects.incidents.index',
        'store' => 'api.v1.projects.incidents.store',
        'show' => 'api.v1.projects.incidents.show',
        'update' => 'api.v1.projects.incidents.update',
        'destroy' => 'api.v1.projects.incidents.destroy',
    ]);
    Route::post('projects/{project}/incidents/{incident}/resolve', [IncidentController::class, 'resolve'])->name('api.v1.projects.incidents.resolve');

    Route::apiResource('projects.members', ProjectMemberController::class)->only(['index', 'store', 'destroy'])->names([
        'index' => 'api.v1.projects.members.index',
        'store' => 'api.v1.projects.members.store',
        'destroy' => 'api.v1.projects.members.destroy',
    ]);

    Route::apiResource('projects.logs', LogController::class)->only(['index', 'store'])->names([
        'index' => 'api.v1.projects.logs.index',
        'store' => 'api.v1.projects.logs.store',
    ]);

    Route::get('queues/metrics', [QueueController::class, 'metrics'])->name('api.v1.queues.metrics');
});
