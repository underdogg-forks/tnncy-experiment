<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\Permission;
use App\Models\Tenant\User;
use Illuminate\Http\Request;

class UserBasicController
{
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
     * @param int $id
     *
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                              => 'required|string|max:255',
            'email'                             => 'required|email|unique:users,email',
            'password'                          => 'required|string|min:8',
            'selectedUserCustomerPermissions'   => 'array',
            'selectedUserCustomerPermissions.*' => 'exists:permissions,id',
        ]);
        $permissions = Permission::whereIn('id', $request->selectedUserCustomerPermissions ?? [])->get();
        $user        = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);
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
            'name'          => 'sometimes|string|max:255',
            'email'         => 'sometimes|email|unique:users,email,' . $id,
            'password'      => 'sometimes|string|min:8',
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $user        = User::find($id);
        if ( ! $user) {
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
        if ( ! $user) {
            abort(404, 'User not found');
        }
        $user->syncPermissions([]);
        $user->delete();

        return 'Deleted';
    }

    private function getUser($id)
    {
        $user = User::find($id);
        if ( ! $user) {
            abort(404, 'User not found');
        }
        $permissions = $user->getAllPermissions()->pluck('id');

        return ['user' => $user, 'permissions' => $permissions];
    }
}
