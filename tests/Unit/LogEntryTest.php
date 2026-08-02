<?php

use App\Models\LogEntry;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('log entry generates uuid primary key', function () {
    $log = LogEntry::factory()->create();

    expect($log->id)->toBeString()
        ->and(strlen($log->id))->toBe(36);
});

test('log entry belongs to a project', function () {
    $project = Project::factory()->create();
    $log = LogEntry::factory()->create(['project_id' => $project->id]);

    expect($log->project->id)->toBe($project->id);
});

test('log entry level and search scopes work', function () {
    $errorLog = LogEntry::factory()->error()->create(['message' => 'Database connection timeout']);
    $infoLog = LogEntry::factory()->create(['level' => 'info', 'message' => 'User logged in successfully']);

    $errorList = LogEntry::level('error')->get();
    $searchList = LogEntry::search('connection')->get();

    expect($errorList->contains($errorLog))->toBeTrue()
        ->and($errorList->contains($infoLog))->toBeFalse()
        ->and($searchList->contains($errorLog))->toBeTrue();
});
