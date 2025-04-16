<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Helpers\TestHelper;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Register auth routes without rate limiting for testing
        TestHelper::registerAuthRoutesWithoutRateLimit();
        
        // Also define the auth rate limiter just in case it's still used somewhere
        if (!RateLimiter::limiter('auth')) {
            RateLimiter::for('auth', function ($request) {
                return 5;
            });
        }
    }

    public function test_user_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertCreated()
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'customer'
        ]);
    }

    public function test_user_cannot_register_with_existing_email()
    {
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role'
                ]
            ]);
    }

    public function test_user_cannot_login_with_wrong_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_get_their_profile()
    {
        $user = User::factory()->create();
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/user');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role'
                ]
            ]);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_csrf_protection_works()
    {
        $response = $this->get('/sanctum/csrf-cookie');
        
        $response->assertNoContent(); // Expects 204 status code
        $this->assertTrue(
            !empty($response->headers->getCookies()),
            'CSRF cookie was not set'
        );
    }

//    /**
//     * @group rate-limiting
//     */
//    public function test_rate_limiting_on_auth_routes()
//    {
//        // This test is skipped since we're bypassing rate limiting in tests
//       $this->markTestSkipped('Rate limiting test requires special setup and is bypassed in tests.');
//    }
}