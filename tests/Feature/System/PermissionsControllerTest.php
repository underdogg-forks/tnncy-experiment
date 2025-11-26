<?php

namespace Tests\Feature\System;

use App\Models\System\Permission;
use App\Models\System\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PermissionsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated user can list permissions with guard filter.
     *
     * @return void
     */
    public function test_authenticated_user_can_list_permissions_with_guard_filter()
    {
        $token = $this->getAuthToken();

        Permission::create([
            'name'       => 'edit-users',
            'guard_name' => 'system',
        ]);

        Permission::create([
            'name'       => 'view-reports',
            'guard_name' => 'system',
        ]);

        Permission::create([
            'name'       => 'manage-content',
            'guard_name' => 'tenant',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/permissions?guard=system');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['name' => 'edit-users']);
        $response->assertJsonFragment(['name' => 'view-reports']);
    }

    /**
     * Test that permissions are filtered by guard name.
     *
     * @return void
     */
    public function test_permissions_are_filtered_by_guard_name()
    {
        $token = $this->getAuthToken();

        Permission::create([
            'name'       => 'system-permission',
            'guard_name' => 'system',
        ]);

        Permission::create([
            'name'       => 'tenant-permission',
            'guard_name' => 'tenant',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/permissions?guard=tenant');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'tenant-permission']);
    }

    /**
     * Test that unauthenticated user cannot list permissions.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_list_permissions()
    {
        $response = $this->getJson('/api/permissions?guard=system');

        $response->assertStatus(401);
    }

    /**
     * Test that permissions endpoint returns empty array when no permissions exist.
     *
     * @return void
     */
    public function test_permissions_endpoint_returns_empty_array_when_no_permissions()
    {
        $token = $this->getAuthToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/permissions?guard=system');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    /**
     * Test that permissions endpoint works without guard parameter.
     *
     * @return void
     */
    public function test_permissions_endpoint_works_without_guard_parameter()
    {
        $token = $this->getAuthToken();

        Permission::create([
            'name'       => 'test-permission',
            'guard_name' => 'system',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/permissions');

        $response->assertStatus(200);
    }

    /**
     * Get authenticated user token.
     *
     * @return string
     */
    private function getAuthToken()
    {
        $user = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        return auth()->guard('system')->login($user);
    }
}
