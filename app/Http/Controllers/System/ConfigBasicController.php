<?php

namespace App\Http\Controllers\System;

class ConfigBasicController
{
    protected $tenancy;

    public function __construct(\Hyn\Tenancy\Environment $tenancy)
    {
        $this->tenancy = $tenancy;
    }

    public function checkTenant()
    {
        $hostname = $this->tenancy->hostname();

        return $hostname ? response()->json(true) : response()->json(false);
    }
}
