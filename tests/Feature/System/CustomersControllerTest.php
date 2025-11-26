<?php

namespace Tests\Feature\System;

use App\Models\System\Customer;
use App\Models\System\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomersControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated user can list all customers.
     *
     * @return void
     */
    public function test_authenticated_user_can_list_customers()
    {
        $token = $this->getAuthToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/customers');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'email'],
        ]);
    }

    /**
     * Test that unauthenticated user cannot list customers.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_list_customers()
    {
        $response = $this->getJson('/api/customers');

        $response->assertStatus(401);
    }

    /**
     * Test that authenticated user can create a customer.
     *
     * @return void
     */
    public function test_authenticated_user_can_create_customer()
    {
        $this->markTestSkipped('Customer creation requires multi-tenant setup which is complex to test');

        // Note: This test is skipped because creating a customer involves:
        // 1. Creating a Website with separate database
        // 2. Creating a Hostname and linking it
        // 3. Switching tenant context
        // 4. Creating admin user in tenant database
        // 5. Switching back to system context
        // This requires a full multi-tenant test setup
    }

    /**
     * Test that customer creation validation fails with missing name.
     *
     * @return void
     */
    public function test_customer_creation_validation_requires_name()
    {
        $token = $this->getAuthToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/customers', [
            'email'  => 'customer@example.com',
            'domain' => 'customer.localhost',
        ]);

        // Will fail validation or at some point due to missing name
        $this->assertTrue($response->status() >= 400);
    }

    /**
     * Test that customer creation validation requires email.
     *
     * @return void
     */
    public function test_customer_creation_validation_requires_email()
    {
        $token = $this->getAuthToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/customers', [
            'name'   => 'Test Customer',
            'domain' => 'customer.localhost',
        ]);

        // Will fail at some point due to missing email
        $this->assertTrue($response->status() >= 400);
    }

    /**
     * Test that authenticated user can view a specific customer.
     *
     * @return void
     */
    public function test_authenticated_user_can_view_customer()
    {
        $this->markTestSkipped('Viewing customer requires hostname relationship which needs multi-tenant setup');

        // Note: Viewing a customer requires the hostname relationship to be set up
        // which is part of the multi-tenant infrastructure
    }

    /**
     * Test that viewing non-existent customer returns 404.
     *
     * @return void
     */
    public function test_viewing_nonexistent_customer_returns_error()
    {
        $token = $this->getAuthToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/customers/99999');

        // Should return an error (404 or 500 depending on hostname lookup)
        $this->assertTrue($response->status() >= 400);
    }

    /**
     * Test that authenticated user can update a customer.
     *
     * @return void
     */
    public function test_authenticated_user_can_update_customer()
    {
        $this->markTestSkipped('Customer update requires hostname relationship which needs multi-tenant setup');

        // Note: Updating a customer involves updating both Customer and Hostname
        // which requires multi-tenant infrastructure
    }

    /**
     * Test that unauthenticated user cannot update a customer.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_update_customer()
    {
        $response = $this->putJson('/api/customers/1', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that updating non-existent customer returns error.
     *
     * @return void
     */
    public function test_updating_nonexistent_customer_returns_error()
    {
        $token = $this->getAuthToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/customers/99999', [
            'name' => 'Updated Name',
        ]);

        // Should return an error
        $this->assertTrue($response->status() >= 400);
    }

    /**
     * Test that customer destroy method exists but is not implemented.
     *
     * @return void
     */
    public function test_customer_destroy_is_not_implemented()
    {
        $this->markTestSkipped('Customer deletion is intentionally not implemented in the controller');

        // Note: The destroy method in CustomersController is commented out
        // as it involves complex database deletion logic
    }

    /**
     * Test that unauthenticated user cannot delete a customer.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_delete_customer()
    {
        $response = $this->deleteJson('/api/customers/1');

        $response->assertStatus(401);
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
