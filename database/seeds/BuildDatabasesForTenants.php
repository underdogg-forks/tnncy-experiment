<?php

use App\Models\System\Customer;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class BuildDatabasesForTenants extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = [
            [
                'domain' => 'foo.api.tenancy.localhost',
                'name'   => 'FooCustomer',
                'email'  => 'customer@foo.com',
            ],
        ];
        $permissionName = 'permission_name'; // TODO: set to actual permission name
        $permission     = Permission::where('name', $permissionName)->first();

        foreach ($customers as $customer) {
            /*
            |--------------------------------------------------------------------------
            | CREATE THE CUSTOMER
            |--------------------------------------------------------------------------
             */
            $newCustomer = Customer::create(['name' => $customer['name'], 'email' => $customer['email']]);
            if ($permission) {
                $newCustomer->givePermissionTo($permission);
            } else {
                \Log::warning("Permission '{$permissionName}' not found. Skipping assignment for customer {$customer['name']}.");
                // Optionally: Permission::create(['name' => $permissionName, 'guard_name' => 'system']);
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE THE WEBSITE
            |--------------------------------------------------------------------------
            */
            $website       = new Website();
            $website->uuid = Str::uuid()->toString();
            app(WebsiteRepository::class)->create($website);

            /*
            |--------------------------------------------------------------------------
            | CREATE THE HOSTNAME
            |--------------------------------------------------------------------------
             */
            $hostname              = new Hostname();
            $hostname->customer_id = $newCustomer->id;
            $hostname->fqdn        = $customer['domain'];
            app(HostnameRepository::class)->attach($hostname, $website);
        }
    }
}
