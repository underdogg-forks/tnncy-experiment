<?php

use App\Models\System\Permission;
use App\Models\System\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::where('guard_name', 'system')->get();

        $user = User::create([
            'name'              => 'Admin Name',
            'email'             => 'admin@mail.com',
            'email_verified_at' => now(),
            'password'          => 'password',
            'remember_token'    => Str::random(10),
        ]);
        $user->givePermissionTo($permissions);
    }
}
