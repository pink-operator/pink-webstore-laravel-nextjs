<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Tests\Helpers\TestSetup;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase, TestSetup;

    public function test_verification_email_is_sent_when_user_registers()
    {
        Notification::fake();
        
        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertStatus(201);
        
        $user = User::where('email', 'test@example.com')->first();
        
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }
    
    public function test_email_can_be_verified()
    {
        $user = User::factory()->unverified()->create();

        Event::fake();
        
        // Need to be authenticated as the user to access the verification route
        $this->actingAs($user);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Follow the redirect that happens after successful verification
        $response = $this->get($verificationUrl);
        
        // Should redirect to dashboard with a verified param
        $response->assertRedirect('/dashboard?verified=1');
        
        // The user should now be verified
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        
        // The verified event should be dispatched
        Event::assertDispatched(Verified::class);
    }

    public function test_email_is_not_verified_with_invalid_hash()
    {
        $user = User::factory()->unverified()->create();
        
        // Need to be authenticated as the user
        $this->actingAs($user);
        
        // Create a verification URL with an invalid hash
        $invalidVerificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );
        
        // Visit the invalid URL - this will result in a 403 forbidden
        $this->get($invalidVerificationUrl)->assertStatus(403);
        
        // The user should still be unverified
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
    
    public function test_verification_email_can_be_resent()
    {
        // Skip this test for now until we implement or enable the API-based resend endpoint
        $this->markTestSkipped("Resend verification email API endpoint not implemented yet");
        
        /*
        Notification::fake();
        
        $user = User::factory()->unverified()->create();
        
        // Create an API token for the user
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Define the verification notification resend endpoint
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/email/verification-notification')
            ->assertRedirect();
        
        Notification::assertSentTo($user, VerifyEmailNotification::class);
        */
    }
}