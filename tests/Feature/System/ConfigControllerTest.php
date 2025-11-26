<?php

namespace Tests\Feature\System;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConfigControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that checkTenant returns false when not in tenant context.
     *
     * @return void
     */
    public function test_check_tenant_returns_false_in_system_context()
    {
        $response = $this->getJson('/api/checkTenant');

        $response->assertStatus(200);
        $response->assertExactJson(false);
    }

    /**
     * Test that checkTenant endpoint is accessible without authentication.
     *
     * @return void
     */
    public function test_check_tenant_is_accessible_without_authentication()
    {
        $response = $this->getJson('/api/checkTenant');

        $response->assertStatus(200);
    }

    /**
     * Test that checkTenant returns true when in tenant context.
     *
     * @return void
     */
    public function test_check_tenant_returns_true_in_tenant_context()
    {
        $this->markTestSkipped('Testing tenant context requires multi-tenant setup with hostname configuration');
        
        // Note: To test tenant context, we would need to:
        // 1. Create a Website and Hostname
        // 2. Set up the tenant database
        // 3. Make a request with the tenant hostname
        // This requires full multi-tenant infrastructure
    }
}
