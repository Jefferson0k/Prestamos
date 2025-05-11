<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Policies\RolePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ReportePolicy;

class AuthServiceProvider extends ServiceProvider{
    protected $policies = [
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        'reporte' => ReportePolicy::class,
    ];
    public function boot(): void
    {
        $this->registerPolicies();
    }
}

