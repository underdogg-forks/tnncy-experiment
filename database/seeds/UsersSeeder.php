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

        $email           = env('SEED_ADMIN_EMAIL', 'admin_' . Str::random(6) . '@local.test');
        $isProduction    = app()->environment('production');
        $defaultPassword = $isProduction ? null : 'password';
        $password        = env('SEED_ADMIN_PASSWORD', $defaultPassword);
        $hashedPassword  = $password ? \Illuminate\Support\Facades\Hash::make($password) : null;

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => env('SEED_ADMIN_NAME', 'Admin Name'),
                'email_verified_at' => now(),
                'password'          => $hashedPassword,
                'remember_token'    => Str::random(10),
            ]
        );
        $user->syncPermissions($permissions);
    }
}
