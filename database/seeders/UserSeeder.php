<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder{
    public function run(): void{
        $adminRole = Role::where('name', 'administrador')->first();
        $personalRole = Role::where('name', 'personal')->first();
        $permissions = Permission::all();
        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
        }

        $admin_1 = User::create([
            'name' => 'Jefferson',
            'dni' => '0000000',
            'apellidos' => '0k',
            'nacimiento' => '2003-03-11',
            'email' => 'ok@gmail.com',
            'username' => 'Jeferson',
            'password' => Hash::make('12345678'),
            'status' => true,
            'restablecimiento' => 0,
        ]);

        $admin_1->assignRole($adminRole);
    }
}
