<?php

use App\Actions\ApiKeys\CreateApiKey;
use App\Actions\ApiKeys\RevokeApiKey;
use App\Models\ApiKey;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('token is stored hashed at rest with a displayable prefix', function () {
    $plain = 'atl_'.str_repeat('a', 36);
    $key = ApiKey::factory()->create([
        'token' => hash('sha256', $plain),
        'key_prefix' => substr($plain, 0, 12),
    ]);

    expect($key->getRawOriginal('token'))->not->toBe($plain)
        ->and($key->getRawOriginal('token'))->toBe(hash('sha256', $plain))
        ->and($key->key_prefix)->toBe(substr($plain, 0, 12));
});

test('create action returns the plaintext token exactly once and persists the hash', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $result = app(CreateApiKey::class)->handle($user, $project, 'CI token');
    $key = $result['key'];
    $token = $result['token'];

    expect($token)->toMatch('/^atl_/')
        ->and(strlen($token))->toBe(4 + 36)
        ->and($key->user_id)->toBe($user->id)
        ->and($key->project_id)->toBe($project->id)
        ->and($key->getRawOriginal('token'))->toBe(hash('sha256', $token))
        ->and($key->key_prefix)->toBe(substr($token, 0, 12))
        ->and($key->expires_at)->toBeNull();
});

test('revoke action marks the key as revoked', function () {
    $key = ApiKey::factory()->create();

    expect($key->isRevoked())->toBeFalse();

    app(RevokeApiKey::class)->handle($key);

    expect($key->fresh()->isRevoked())->toBeTrue()
        ->and($key->fresh()->revoked_at)->not->toBeNull();
});

test('isExpired reflects the expires_at date', function () {
    expect(ApiKey::factory()->create(['expires_at' => now()->addDay()])->isExpired())->toBeFalse()
        ->and(ApiKey::factory()->create(['expires_at' => now()->subDay()])->isExpired())->toBeTrue()
        ->and(ApiKey::factory()->create(['expires_at' => null])->isExpired())->toBeFalse();
});

test('isValid is false when revoked or expired', function () {
    $revoked = ApiKey::factory()->create();
    $revoked->forceFill(['revoked_at' => now()])->save();

    expect($revoked->fresh()->isValid())->toBeFalse()
        ->and(ApiKey::factory()->create(['expires_at' => now()->subDay()])->isValid())->toBeFalse()
        ->and(ApiKey::factory()->create()->isValid())->toBeTrue();
});

test('not revoked scope excludes revoked keys', function () {
    $active = ApiKey::factory()->create();
    $revoked = ApiKey::factory()->create();
    $revoked->forceFill(['revoked_at' => now()])->save();

    expect(ApiKey::notRevoked()->pluck('id'))
        ->toContain($active->id)
        ->not->toContain($revoked->id);
});

test('key exposes its owning user and project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $key = ApiKey::factory()->create(['user_id' => $user->id, 'project_id' => $project->id]);

    expect($key->user->is($user))->toBeTrue()
        ->and($key->project->is($project))->toBeTrue()
        ->and($project->apiKeys->contains($key))->toBeTrue();
});
