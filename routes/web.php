<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AlertEventController;
use App\Http\Controllers\Web\AlertRuleController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeploymentController;
use App\Http\Controllers\Web\IncidentController;
use App\Http\Controllers\Web\LogController;
use App\Http\Controllers\Web\NotificationChannelController;
use App\Http\Controllers\Web\NotificationDeliveryController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\ProjectMemberController;
use App\Http\Controllers\Web\QueueController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\WebhookCallController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
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

    Route::scopeBindings()->group(function () {
        Route::resource('projects.members', ProjectMemberController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    Route::get('projects/{project}/logs', [LogController::class, 'index'])->name('projects.logs.index');

    Route::scopeBindings()->group(function () {
        Route::get('projects/{project}/webhooks', [WebhookCallController::class, 'index'])->name('projects.webhooks.index');
        Route::get('projects/{project}/webhooks/{webhookCall}', [WebhookCallController::class, 'show'])->name('projects.webhooks.show');
        Route::post('projects/{project}/webhooks/{webhookCall}/replay', [WebhookCallController::class, 'replay'])->name('projects.webhooks.replay');
    });

    Route::resource('projects.notification-channels', NotificationChannelController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::resource('projects.alert-rules', AlertRuleController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('projects/{project}/alerts', [AlertEventController::class, 'index'])->name('projects.alerts.index');
    Route::get('projects/{project}/alerts/{alertEvent}', [AlertEventController::class, 'show'])->name('projects.alerts.show');
    Route::post('projects/{project}/alerts/deliveries/{delivery}/acknowledge', [NotificationDeliveryController::class, 'acknowledge'])->name('projects.alerts.acknowledge');
    Route::post('projects/{project}/alerts/deliveries/{delivery}/snooze', [NotificationDeliveryController::class, 'snooze'])->name('projects.alerts.snooze');

    Route::get('/queues', QueueController::class)->name('queues.index');
});

require __DIR__.'/auth.php';
