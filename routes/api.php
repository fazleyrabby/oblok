<?php

use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ServiceController;
use Illuminate\Support\Facades\Route;

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
});
