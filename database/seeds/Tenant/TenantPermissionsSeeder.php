<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant\Permission;

class TenantPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenantPermissions = config('permission.permissions_list')['tenant'];
        $tenantPermissions = config('permission.permissions_list')['tenant'];

        foreach ($tenantPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'tenant']
            );
        }
    }
}
