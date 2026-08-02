<?php

use App\Enums\ProjectRole;
use App\Models\Deployment;
use App\Models\Incident;
use App\Models\LogEntry;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function projectWithMember(string $role): array
{
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    if ($role !== 'owner') {
        $project->members()->attach($member->id, ['role' => $role]);
    }

    return ['owner' => $owner, 'member' => $member, 'project' => $project];
}

$cases = [
    'owner' => [
        'view' => true,
        'update' => true,
        'delete' => true,
        'manageMembers' => true,
    ],
    'admin' => [
        'view' => true,
        'update' => true,
        'delete' => false,
        'manageMembers' => true,
    ],
    'operator' => [
        'view' => true,
        'update' => false,
        'delete' => false,
        'manageMembers' => false,
    ],
    'viewer' => [
        'view' => true,
        'update' => false,
        'delete' => false,
        'manageMembers' => false,
    ],
];

foreach ($cases as $role => $expectations) {
    foreach ($expectations as $ability => $expected) {
        test("{$role} can {$ability} project: ".($expected ? 'true' : 'false'), function () use ($role, $ability, $expected) {
            ['owner' => $owner, 'member' => $member, 'project' => $project] = projectWithMember($role);

            $actor = $role === 'owner' ? $owner : $member;

            expect($actor->can($ability, $project))->toBe($expected);
        });
    }
}

test('a non-member cannot view the project', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    expect($stranger->can('view', $project))->toBeFalse();
});

test('operator can manage services and incidents but not deployments', function () {
    ['owner' => $owner, 'member' => $member, 'project' => $project] = projectWithMember(ProjectRole::Operator->value);

    $service = Service::factory()->create(['project_id' => $project->id]);
    $incident = Incident::factory()->create(['project_id' => $project->id]);
    $deployment = Deployment::factory()->create(['project_id' => $project->id]);
    $log = LogEntry::factory()->create(['project_id' => $project->id]);

    expect($member->can('view', $project))->toBeTrue();

    // Child resources — project-level abilities
    expect($member->can('create', [Service::class, $project]))->toBeTrue();
    expect($member->can('create', [Incident::class, $project]))->toBeTrue();
    expect($member->can('create', [LogEntry::class, $project]))->toBeTrue();
    expect($member->can('create', [Deployment::class, $project]))->toBeFalse();

    // Instance-level abilities
    expect($member->can('update', $service))->toBeTrue();
    expect($member->can('update', $incident))->toBeTrue();
    expect($member->can('update', $deployment))->toBeFalse();
    expect($member->can('view', $service))->toBeTrue();
    expect($member->can('view', $incident))->toBeTrue();
    expect($member->can('view', $deployment))->toBeTrue();
    expect($member->can('view', $log))->toBeTrue();
});

test('viewer is read-only across all child resources', function () {
    ['owner' => $owner, 'member' => $member, 'project' => $project] = projectWithMember(ProjectRole::Viewer->value);

    $service = Service::factory()->create(['project_id' => $project->id]);
    $incident = Incident::factory()->create(['project_id' => $project->id]);
    $deployment = Deployment::factory()->create(['project_id' => $project->id]);
    $log = LogEntry::factory()->create(['project_id' => $project->id]);

    expect($member->can('view', $project))->toBeTrue();
    expect($member->can('update', $project))->toBeFalse();
    expect($member->can('manageMembers', $project))->toBeFalse();
    expect($member->can('create', [Service::class, $project]))->toBeFalse();
    expect($member->can('create', [Incident::class, $project]))->toBeFalse();
    expect($member->can('create', [LogEntry::class, $project]))->toBeFalse();
    expect($member->can('create', [Deployment::class, $project]))->toBeFalse();
    expect($member->can('view', $service))->toBeTrue();
    expect($member->can('view', $incident))->toBeTrue();
    expect($member->can('view', $deployment))->toBeTrue();
    expect($member->can('view', $log))->toBeTrue();
});
