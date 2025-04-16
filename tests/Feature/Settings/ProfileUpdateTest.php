<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Helpers\TestHelper;

// Use RefreshDatabase trait
uses(RefreshDatabase::class);

// Setup for tests
beforeEach(function () {
    TestHelper::disableAllRateLimiting();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->putJson('/api/auth/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Test User')
        ->assertJsonPath('data.email', 'test@example.com');
});

test('email verification status is unchanged when email is unchanged', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->putJson('/api/auth/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response->assertOk();
    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->deleteJson('/api/auth/profile', [
            'password' => 'password',
        ]);

    $response->assertOk();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->deleteJson('/api/auth/profile', [
            'password' => 'wrong-password',
        ]);

    $response->assertStatus(422);
    expect($user->fresh())->not->toBeNull();
});