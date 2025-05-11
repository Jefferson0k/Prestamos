<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder{
    /**
     * Run the database seeds.
     */
    public function run(): void{
        #User
        Permission::create(['name' => 'crear usuarios']);
        Permission::create(['name' => 'editar usuarios']);
        Permission::create(['name' => 'eliminar usuarios']);
        Permission::create(['name' => 'ver usuarios']);
        #Cliente
        Permission::create(['name' =>'crear clientes']);
        Permission::create(['name' =>'editar clientes']);
        Permission::create(['name' =>'eliminar clientes']);
        Permission::create(['name' =>'ver clientes']);
        #Cuotas
        Permission::create(['name' =>'crear cuotas']);
        Permission::create(['name' =>'editar cuotas']);
        Permission::create(['name' =>'eliminar cuotas']);
        Permission::create(['name' =>'ver cuotas']);
        #Pagos
        Permission::create(['name' =>'crear pagos']);
        Permission::create(['name' =>'editar pagos']);
        Permission::create(['name' =>'eliminar pagos']);
        Permission::create(['name' =>'ver pagos']);
        #Prestamo
        Permission::create(['name' =>'crear prestamos']);
        Permission::create(['name' =>'editar prestamos']);
        Permission::create(['name' =>'eliminar prestamos']);
        Permission::create(['name' =>'ver prestamos']);
        # Roles
        Permission::create(['name' =>'crear roles']);
        Permission::create(['name' =>'editar roles']);
        Permission::create(['name' =>'eliminar roles']);
        Permission::create(['name' =>'ver roles']);
        # Permisos
        Permission::create(['name' =>'crear permisos']);
        Permission::create(['name' =>'editar permisos']);
        Permission::create(['name' =>'eliminar permisos']);
        Permission::create(['name' =>'ver permisos']);
        #Reportes
        Permission::create(['name' => 'ver reportes']);
    }
}
