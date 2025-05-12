<?php

namespace App\Providers;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Policies\RolePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ReportePolicy;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use PDOException;

class AuthServiceProvider extends ServiceProvider {
    protected $policies = [
        //Role::class => RolePolicy::class,
        //Permission::class => PermissionPolicy::class,
        //'reporte' => ReportePolicy::class,
    ];
    
    public function boot(): void {
        $this->registerPolicies();        
        try {
            DB::connection()->getPdo();
            if (Schema::hasTable('permissions') && Schema::hasTable('roles')) {
                Gate::policy(Role::class, RolePolicy::class);
                Gate::policy(Permission::class, PermissionPolicy::class);
                Gate::define('ver-reportes', [ReportePolicy::class, 'viewAny']);
            }
        } catch (PDOException $e) {
            
        }
    }
}