<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AiAssistantController;
use App\Http\Controllers\Web\AlertEventController;
use App\Http\Controllers\Web\AlertRuleController;
use App\Http\Controllers\Web\ApiKeyController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeploymentController;
use App\Http\Controllers\Web\GitHubIntegrationController;
use App\Http\Controllers\Web\IncidentController;
use App\Http\Controllers\Web\LogController;
use App\Http\Controllers\Web\MessagingIntegrationController;
use App\Http\Controllers\Web\MetricController;
use App\Http\Controllers\Web\NotificationChannelController;
use App\Http\Controllers\Web\NotificationDeliveryController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\ProjectMemberController;
use App\Http\Controllers\Web\QueueController;
use App\Http\Controllers\Web\ScheduledTaskController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\WebhookCallController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

use App\Http\Controllers\Web\RequestAnalyticsController;
use App\Http\Controllers\Web\ResourceMonitoringController;

Route::middleware('auth')->group(function () {
    Route::get('projects/{project}/resources', [ResourceMonitoringController::class, 'index'])->name('projects.resources.index');
    Route::get('projects/{project}/resources/data', [ResourceMonitoringController::class, 'data'])->name('projects.resources.data');
    Route::get('projects/{project}/request-analytics', [RequestAnalyticsController::class, 'index'])->name('projects.request-analytics.index');
    Route::get('projects/{project}/request-analytics/data', [RequestAnalyticsController::class, 'data'])->name('projects.request-analytics.data');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');

    Route::resource('projects.services', ServiceController::class);
    Route::post('projects/{project}/services/{service}/ping', [ServiceController::class, 'ping'])->name('projects.services.ping');

    Route::resource('projects.deployments', DeploymentController::class)->only(['index', 'show']);

    Route::resource('projects.incidents', IncidentController::class);
    Route::post('projects/{project}/incidents/{incident}/resolve', [IncidentController::class, 'resolve'])->name('projects.incidents.resolve');
    Route::post('projects/{project}/incidents/{incident}/suggest', [IncidentController::class, 'suggest'])->name('projects.incidents.suggest');

    Route::scopeBindings()->group(function () {
        Route::resource('projects.members', ProjectMemberController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    Route::get('projects/{project}/logs', [LogController::class, 'index'])->name('projects.logs.index');

    Route::scopeBindings()->group(function () {
        Route::get('projects/{project}/webhooks', [WebhookCallController::class, 'index'])->name('projects.webhooks.index');
        Route::get('projects/{project}/webhooks/{webhookCall}', [WebhookCallController::class, 'show'])->name('projects.webhooks.show');
        Route::post('projects/{project}/webhooks/{webhookCall}/replay', [WebhookCallController::class, 'replay'])->name('projects.webhooks.replay');

        Route::resource('projects.scheduled-tasks', ScheduledTaskController::class)->parameters(['scheduled-tasks' => 'scheduledTask']);
        Route::post('projects/{project}/scheduled-tasks/{scheduledTask}/runs', [ScheduledTaskController::class, 'recordRun'])->name('projects.scheduled-tasks.runs');
    });

    Route::get('projects/{project}/github', [GitHubIntegrationController::class, 'index'])->name('projects.github.index');
    Route::post('projects/{project}/github', [GitHubIntegrationController::class, 'store'])->name('projects.github.store');
    Route::post('projects/{project}/github/sync', [GitHubIntegrationController::class, 'sync'])->name('projects.github.sync');
    Route::delete('projects/{project}/github', [GitHubIntegrationController::class, 'destroy'])->name('projects.github.destroy');

    Route::get('projects/{project}/api-keys', [ApiKeyController::class, 'index'])->name('projects.api-keys.index');
    Route::post('projects/{project}/api-keys', [ApiKeyController::class, 'store'])->name('projects.api-keys.store');
    Route::delete('projects/{project}/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('projects.api-keys.destroy');

    Route::get('projects/{project}/messaging', [MessagingIntegrationController::class, 'index'])->name('projects.messaging.index');
    Route::post('projects/{project}/messaging', [MessagingIntegrationController::class, 'store'])->name('projects.messaging.store');
    Route::post('projects/{project}/messaging/{integration}/send', [MessagingIntegrationController::class, 'send'])->name('projects.messaging.send');
    Route::delete('projects/{project}/messaging/{integration}', [MessagingIntegrationController::class, 'destroy'])->name('projects.messaging.destroy');

    Route::get('projects/{project}/metrics', [MetricController::class, 'index'])->name('projects.metrics.index');
    Route::get('projects/{project}/ai-assistant', [AiAssistantController::class, 'index'])->name('projects.ai-assistant');
    Route::post('projects/{project}/ai-assistant', [AiAssistantController::class, 'ask'])->name('projects.ai-assistant.ask');
    Route::get('projects/{project}/metrics/data', [MetricController::class, 'data'])->name('projects.metrics.data');
    Route::post('projects/{project}/metrics/targets', [MetricController::class, 'storeTarget'])->name('projects.metrics.targets.store');
    Route::delete('projects/{project}/metrics/targets/{metricTarget}', [MetricController::class, 'destroyTarget'])->name('projects.metrics.targets.destroy');

    Route::resource('projects.notification-channels', NotificationChannelController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::resource('projects.alert-rules', AlertRuleController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('projects/{project}/alerts', [AlertEventController::class, 'index'])->name('projects.alerts.index');
    Route::get('projects/{project}/alerts/{alertEvent}', [AlertEventController::class, 'show'])->name('projects.alerts.show');
    Route::post('projects/{project}/alerts/deliveries/{delivery}/acknowledge', [NotificationDeliveryController::class, 'acknowledge'])->name('projects.alerts.acknowledge');
    Route::post('projects/{project}/alerts/deliveries/{delivery}/snooze', [NotificationDeliveryController::class, 'snooze'])->name('projects.alerts.snooze');

    Route::get('/queues', QueueController::class)->name('queues.index');
});

require __DIR__.'/auth.php';
