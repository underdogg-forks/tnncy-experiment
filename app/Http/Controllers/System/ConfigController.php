<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    protected $tenancy;

    public function __construct(\Hyn\Tenancy\Environment $tenancy)
    {
        $this->tenancy = $tenancy;
    }

    public function checkTenant()
    {
        $hostname = $this->tenancy->hostname();
        return $hostname ?  response()->json(true) :  response()->json(false);
    }
}
