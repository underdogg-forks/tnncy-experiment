<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\User;
use App\Models\Tenant\Permission;

class UserController extends Controller
{
    private function getUser($id)
    {
        $user = User::find($id);
        $permissions =  $user->getAllPermissions()->pluck('id');
        return ['user' => $user,'permissions' => $permissions];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get Current Customer and his permissions
        // dd($hostname->customer->hasPermissionTo('create-users', 'customer'));

        // $user = User::with('permissions')->find(1);
        // dd($user->hasPermissionTo($permission));


        // Get Users Customer 
        return User::all();
    }

    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = $this->getUser($id);
        return $user;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $permissions = Permission::whereIn('id', $request->selecetdUserCustomerPermissions)->get();
        $user = User::create($request->all());
        $user->givePermissionTo($permissions);
        return $user;
    }

    /**
     * Show the profile for the given user.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $user = User::find($id);
        if (!$user) {
            abort(404, 'User not found');
        }
        $user->update($validated);
        $user->syncPermissions($permissions);
        return $user;
    }

    /**
     * Show the profile for the given user.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            abort(404, 'User not found');
        }
        $user->syncPermissions([]);
        $user->delete();
        return 'Deleted';
    }
}
