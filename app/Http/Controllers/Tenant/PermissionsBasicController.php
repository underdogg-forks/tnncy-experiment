<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\Permission;

class PermissionsBasicController
{
    public function tenantPermissions()
    {
        $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();

        if ( ! $hostname || ! $hostname->customer) {
            return collect();
        }

        return $hostname->customer->getDirectPermissions();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customerPermissions = $this->tenantPermissions();
        $names               = $customerPermissions->pluck('name');

        return Permission::whereIn('name', $names)->get();
    }
}
