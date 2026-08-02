<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated user is redirected when accessing queues dashboard', function () {
    $response = $this->get(route('queues.index'));

    $response->assertRedirect('/login');
});

test('authenticated user can view queue metrics dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('queues.index'));

    $response->assertOk()
        ->assertSee('Pending Jobs')
        ->assertSee('Failed Jobs');
});

test('api returns queue metrics json payload', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('api.v1.queues.metrics'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['pending_jobs', 'failed_jobs', 'horizon_status', 'recent_failed_jobs'],
        ]);
});

test('horizon dashboard gate allows authenticated admin user', function () {
    $user = User::factory()->create(['email' => 'admin@atlas.dev']);

    $response = $this->actingAs($user)->get('/horizon');

    $response->assertOk();
});
