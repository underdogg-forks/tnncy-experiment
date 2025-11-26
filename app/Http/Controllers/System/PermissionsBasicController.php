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
        $allowedGuards = ['system', 'customer'];
        $guard         = $request->get('guard', 'system');
        if ( ! in_array($guard, $allowedGuards, true)) {
            return response()->json(['error' => 'Invalid guard'], 400);
        }

        return Permission::where('guard_name', $guard)->get();
    }
}
