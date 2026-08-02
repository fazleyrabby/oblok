<?php

use App\Http\Controllers\Api\V1\AlertEventController;
use App\Http\Controllers\Api\V1\AlertRuleController;
use App\Http\Controllers\Api\V1\DeploymentController;
use App\Http\Controllers\Api\V1\DeploymentWebhookController;
use App\Http\Controllers\Api\V1\GitHubIntegrationController;
use App\Http\Controllers\Api\V1\IncidentController;
use App\Http\Controllers\Api\V1\LogController;
use App\Http\Controllers\Api\V1\NotificationChannelController;
use App\Http\Controllers\Api\V1\NotificationDeliveryController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectMemberController;
use App\Http\Controllers\Api\V1\QueueController;
use App\Http\Controllers\Api\V1\ScheduledTaskController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\WebhookCallController;
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

    Route::scopeBindings()->group(function () {
        Route::apiResource('projects.members', ProjectMemberController::class)->only(['index', 'store', 'update', 'destroy'])->names([
            'index' => 'api.v1.projects.members.index',
            'store' => 'api.v1.projects.members.store',
            'update' => 'api.v1.projects.members.update',
            'destroy' => 'api.v1.projects.members.destroy',
        ]);
    });

    Route::apiResource('projects.logs', LogController::class)->only(['index', 'store'])->names([
        'index' => 'api.v1.projects.logs.index',
        'store' => 'api.v1.projects.logs.store',
    ]);

    Route::apiResource('projects.notification-channels', NotificationChannelController::class)->names([
        'index' => 'api.v1.projects.notification-channels.index',
        'store' => 'api.v1.projects.notification-channels.store',
        'show' => 'api.v1.projects.notification-channels.show',
        'update' => 'api.v1.projects.notification-channels.update',
        'destroy' => 'api.v1.projects.notification-channels.destroy',
    ]);

    Route::apiResource('projects.alert-rules', AlertRuleController::class)->names([
        'index' => 'api.v1.projects.alert-rules.index',
        'store' => 'api.v1.projects.alert-rules.store',
        'show' => 'api.v1.projects.alert-rules.show',
        'update' => 'api.v1.projects.alert-rules.update',
        'destroy' => 'api.v1.projects.alert-rules.destroy',
    ]);

    Route::get('projects/{project}/alerts', [AlertEventController::class, 'index'])->name('api.v1.projects.alerts.index');
    Route::get('projects/{project}/alerts/{alertEvent}', [AlertEventController::class, 'show'])->name('api.v1.projects.alerts.show');
    Route::get('projects/{project}/alerts/deliveries', [NotificationDeliveryController::class, 'index'])->name('api.v1.projects.alerts.deliveries.index');
    Route::post('projects/{project}/alerts/deliveries/{delivery}/acknowledge', [NotificationDeliveryController::class, 'acknowledge'])->name('api.v1.projects.alerts.acknowledge');
    Route::post('projects/{project}/alerts/deliveries/{delivery}/snooze', [NotificationDeliveryController::class, 'snooze'])->name('api.v1.projects.alerts.snooze');

    Route::get('queues/metrics', [QueueController::class, 'metrics'])->name('api.v1.queues.metrics');

    Route::scopeBindings()->group(function () {
        Route::apiResource('projects.webhooks', WebhookCallController::class)
            ->parameters(['webhooks' => 'webhookCall'])
            ->only(['index', 'show'])
            ->names([
                'index' => 'api.v1.projects.webhooks.index',
                'show' => 'api.v1.projects.webhooks.show',
            ]);
        Route::post('projects/{project}/webhooks/{webhookCall}/replay', [WebhookCallController::class, 'replay'])->name('api.v1.projects.webhooks.replay');

        Route::apiResource('projects.scheduled-tasks', ScheduledTaskController::class)
            ->parameters(['scheduled-tasks' => 'scheduledTask'])
            ->names([
                'index' => 'api.v1.projects.scheduled-tasks.index',
                'store' => 'api.v1.projects.scheduled-tasks.store',
                'show' => 'api.v1.projects.scheduled-tasks.show',
                'update' => 'api.v1.projects.scheduled-tasks.update',
                'destroy' => 'api.v1.projects.scheduled-tasks.destroy',
            ]);
        Route::post('projects/{project}/scheduled-tasks/{scheduledTask}/runs', [ScheduledTaskController::class, 'recordRun'])->name('api.v1.projects.scheduled-tasks.runs');
    });

    Route::get('projects/{project}/github', [GitHubIntegrationController::class, 'index'])->name('api.v1.projects.github.index');
    Route::post('projects/{project}/github', [GitHubIntegrationController::class, 'store'])->name('api.v1.projects.github.store');
    Route::get('projects/{project}/github/commits', [GitHubIntegrationController::class, 'commits'])->name('api.v1.projects.github.commits');
    Route::get('projects/{project}/github/pull-requests', [GitHubIntegrationController::class, 'pullRequests'])->name('api.v1.projects.github.pull-requests');
    Route::post('projects/{project}/github/sync', [GitHubIntegrationController::class, 'sync'])->name('api.v1.projects.github.sync');
    Route::delete('projects/{project}/github', [GitHubIntegrationController::class, 'destroy'])->name('api.v1.projects.github.destroy');
});
