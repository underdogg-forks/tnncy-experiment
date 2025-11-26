<?php

namespace App\Http\Controllers\System;

use App\Models\System\Permission;
use Illuminate\Http\Request;

class PermissionsBasicController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Permission::where('guard_name', $request->guard)->get();
    }
}
