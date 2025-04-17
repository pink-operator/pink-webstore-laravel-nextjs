<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Helpers\TestSetup;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, TestSetup;

    public function test_users_can_authenticate()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_users_cannot_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(422);
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);
    }

    public function test_users_can_get_their_info()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson('/api/auth/user');
        
        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', $user->name)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_users_cannot_get_user_info()
    {
        $response = $this->getJson('/api/auth/user');
        
        $response->assertStatus(401);
    }
}