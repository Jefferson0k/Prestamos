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
            'name' => 'Jefferson Grabiel',
            'dni' => '76393671',
            'apellidos' => 'Covenas Roman',
            'nacimiento' => '2003-03-11',
            'email' => 'jefersoncovenas7@gmail.com',
            'username' => 'JCOVENASRO11',
            'password' => Hash::make('12345678'),
            'status' => true,
            'restablecimiento' => 0,
        ]);

        $admin_2 = User::create([
            'name' => 'Luis Fernando',
            'dni' => '07777777',
            'apellidos' => 'Atocha Gonzales',
            'nacimiento' => '2003-03-11',
            'email' => 'luisatocha@gmail.com',
            'username' => 'LATOCHA05',
            'password' => Hash::make('12345678'),
            'status' => true,
            'restablecimiento' => 0,
        ]);

        $admin_1->assignRole($adminRole);
        $admin_2->assignRole($adminRole);
    }
}
