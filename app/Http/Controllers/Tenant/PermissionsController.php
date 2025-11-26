<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Permission;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{
    public function tenantPermissions()
    {
        $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();

        if (! $hostname || ! $hostname->customer) {
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
        $names = $customerPermissions->pluck('name');

        return Permission::whereIn('name', $names)->get();
    }
}
