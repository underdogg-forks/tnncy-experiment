<?php

namespace Tests\Feature\System;

use Tests\TestCase;
use App\Models\System\User;
use App\Models\System\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Get authenticated user token.
     *
     * @return string
     */
    private function getAuthToken()
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        return auth()->guard('system')->login($user);
    }

    /**
     * Test that authenticated user can list all users.
     *
     * @return void
     */
    public function test_authenticated_user_can_list_users()
    {
        $token = $this->getAuthToken();

        User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(3); // Including admin user
    }

    /**
     * Test that unauthenticated user cannot list users.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_list_users()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }

    /**
     * Test that authenticated user can create a user.
     *
     * @return void
     */
    public function test_authenticated_user_can_create_user()
    {
        $token = $this->getAuthToken();

        $permission = Permission::create([
            'name' => 'edit-users',
            'guard_name' => 'system',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => Hash::make('password123'),
            'selectedPermissions' => [$permission->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);
    }

    /**
     * Test that unauthenticated user cannot create a user.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_create_user()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that authenticated user can view a specific user.
     *
     * @return void
     */
    public function test_authenticated_user_can_view_user()
    {
        $token = $this->getAuthToken();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/' . $user->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'permissions',
        ]);
        $response->assertJson([
            'user' => [
                'name' => 'Test User',
                'email' => 'testuser@example.com',
            ],
        ]);
    }

    /**
     * Test that viewing non-existent user returns 404.
     *
     * @return void
     */
    public function test_viewing_nonexistent_user_returns_404()
    {
        $token = $this->getAuthToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/99999');

        $response->assertStatus(404);
    }

    /**
     * Test that authenticated user can update a user.
     *
     * @return void
     */
    public function test_authenticated_user_can_update_user()
    {
        $token = $this->getAuthToken();

        $user = User::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'password' => Hash::make('password123'),
        ]);

        $permission = Permission::create([
            'name' => 'edit-content',
            'guard_name' => 'system',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $user->id, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'permissions' => [$permission->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test that update validation fails with duplicate email.
     *
     * @return void
     */
    public function test_update_validation_fails_with_duplicate_email()
    {
        $token = $this->getAuthToken();

        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $user2->id, [
            'email' => 'user1@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test that update validation fails with short password.
     *
     * @return void
     */
    public function test_update_validation_fails_with_short_password()
    {
        $token = $this->getAuthToken();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $user->id, [
            'password' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test that updating non-existent user returns 404.
     *
     * @return void
     */
    public function test_updating_nonexistent_user_returns_404()
    {
        $token = $this->getAuthToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/99999', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test that authenticated user can delete a user.
     *
     * @return void
     */
    public function test_authenticated_user_can_delete_user()
    {
        $token = $this->getAuthToken();

        $user = User::create([
            'name' => 'User to Delete',
            'email' => 'delete@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test that unauthenticated user cannot delete a user.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_delete_user()
    {
        $user = User::create([
            'name' => 'User to Delete',
            'email' => 'delete@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(401);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }
}
