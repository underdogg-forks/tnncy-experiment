<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\BaseAuthController;

class AuthBasicController extends BaseAuthController
{
    protected function guardName(): string
    {
        return 'system';
    }

    protected function getUserPermissions($user)
    {
        return $user->getAllPermissions();
    }
}
