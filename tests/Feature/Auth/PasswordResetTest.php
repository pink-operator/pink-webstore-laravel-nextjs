<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Api\PasswordResetController;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Helpers\TestSetup;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase, TestSetup;

    public function test_reset_password_link_can_be_requested()
    {
        Notification::fake();

        $user = User::factory()->create();
        
        $request = Request::create('/api/auth/forgot-password', 'POST', [
            'email' => $user->email,
        ]);
        
        $controller = new PasswordResetController();
        $response = $controller->forgotPassword($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_fails_with_nonexistent_email()
    {
        $this->expectException(ValidationException::class);
        
        $request = Request::create('/api/auth/forgot-password', 'POST', [
            'email' => 'nonexistent@example.com',
        ]);
        
        $controller = new PasswordResetController();
        $controller->forgotPassword($request);
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        $user = User::factory()->create();
        
        // Create a password reset token
        $token = Password::createToken($user);
        
        $request = Request::create('/api/auth/reset-password', 'POST', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        
        $controller = new PasswordResetController();
        $response = $controller->resetPassword($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        // Verify the password was updated
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_reset_password_fails_with_invalid_token()
    {
        $user = User::factory()->create();
        
        $request = Request::create('/api/auth/reset-password', 'POST', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        
        $controller = new PasswordResetController();
        $response = $controller->resetPassword($request);
        
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_password_cannot_be_reset_with_invalid_email()
    {
        $user = User::factory()->create([
            'email' => 'correct@example.com'
        ]);
        
        // Create a token
        $token = Password::createToken($user);
        
        // Try with wrong email
        $request = Request::create('/api/auth/reset-password', 'POST', [
            'token' => $token,
            'email' => 'wrong@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        
        $controller = new PasswordResetController();
        $response = $controller->resetPassword($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        
        // Verify the password was not updated
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}