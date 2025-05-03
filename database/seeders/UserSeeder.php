<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $adminRole = Role::findById(1);
        $personalRole = Role::findById(2);
        $permissions = Permission::all()->pluck('name')->toArray();

        // Crear usuarios principales
        $admin_1 = User::create([
            'name' => 'Jefferson Grabiel',
            'dni' => '76393671',
            'apellidos' => 'Covenas Roman',
            'nacimiento' => '2003-03-11',
            'email' => 'jefersoncovenas7@gmail.com',
            'username' => 'JCOVENASRO11',
            'password' => Hash::make('12345678'),
            'status' => 1,
        ]);

        $admin_2 = User::create([
            'name' => 'Luis Fernando',
            'dni' => '07777777',
            'apellidos' => 'Atocha Gonzales',
            'nacimiento' => '2003-03-11',
            'email' => 'luisatocha@gmail.com',
            'username' => 'LATOCHA05',
            'password' => Hash::make('12345678'),
            'status' => 1,
        ]);

        // Asignar permisos y roles a administradores
        $adminRole->syncPermissions($permissions);
        $admin_1->assignRole($adminRole);
        $admin_2->assignRole($adminRole);

        // Crear 5000 usuarios adicionales
        for ($i = 1; $i <= 2; $i++) {
            $name = $faker->firstName();
            $lastName1 = $faker->lastName();
            $lastName2 = $faker->lastName();
            $fullLastName = "$lastName1 $lastName2";
            $birthdate = $faker->date('Y-m-d', '2005-01-01');

            $dni = str_pad(70000000 + $i, 8, '0', STR_PAD_LEFT); // Asegura unicidad
            $email = "usuario{$i}@example.com"; // Email único
            $baseUsername = strtoupper(substr($name, 0, 1) . substr($lastName1, 0, 3) . substr($lastName2, 0, 2));
            $username = $baseUsername . str_pad($i, 3, '0', STR_PAD_LEFT); // Username único

            $user = User::create([
                'name' => $name,
                'dni' => $dni,
                'apellidos' => $fullLastName,
                'nacimiento' => $birthdate,
                'email' => $email,
                'username' => $username,
                'password' => Hash::make('password'), // Contraseña genérica
                'status' => 1,
            ]);

            $user->assignRole($personalRole);
        }
    }
}
