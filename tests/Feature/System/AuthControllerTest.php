<?php

namespace Tests\Feature\System;

use App\Models\System\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user can login with valid credentials.
     *
     * @return void
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'token_type',
            'expires_in',
        ]);
        $this->assertEquals('bearer', $response->json('token_type'));
    }

    /**
     * Test that login fails with invalid password.
     *
     * @return void
     */
    public function test_login_fails_with_invalid_password()
    {
        $user = User::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthorized',
        ]);
    }

    /**
     * Test that login fails with non-existent email.
     *
     * @return void
     */
    public function test_login_fails_with_nonexistent_email()
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthorized',
        ]);
    }

    /**
     * Test that login validation fails with missing email.
     *
     * @return void
     */
    public function test_login_validation_fails_with_missing_email()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test that login validation fails with invalid email format.
     *
     * @return void
     */
    public function test_login_validation_fails_with_invalid_email_format()
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test that login validation fails with missing password.
     *
     * @return void
     */
    public function test_login_validation_fails_with_missing_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test that authenticated user can get their details.
     *
     * @return void
     */
    public function test_authenticated_user_can_get_user_details()
    {
        $user = User::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = auth()->guard('system')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/user');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'permissions',
        ]);
    }

    /**
     * Test that unauthenticated user cannot get user details.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_get_user_details()
    {
        $response = $this->postJson('/api/user');

        $response->assertStatus(401);
    }

    /**
     * Test that authenticated user can logout.
     *
     * @return void
     */
    public function test_authenticated_user_can_logout()
    {
        $user = User::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = auth()->guard('system')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Test that unauthenticated user cannot logout.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /**
     * Test that authenticated user can refresh token.
     *
     * @return void
     */
    public function test_authenticated_user_can_refresh_token()
    {
        $user = User::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = auth()->guard('system')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/refresh');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'token_type',
            'expires_in',
        ]);
    }

    /**
     * Test that unauthenticated user cannot refresh token.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_refresh_token()
    {
        $response = $this->postJson('/api/refresh');

        $response->assertStatus(401);
    }
}
