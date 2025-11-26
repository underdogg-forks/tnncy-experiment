<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\BaseAuthController;

class AuthBasicController extends BaseAuthController
{
    protected function guardName(): string
    {
        return 'tenant';
    }

    protected function getUserPermissions($user)
    {
        return $user->getDirectPermissions();
    }
}
