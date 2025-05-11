<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller{
    public function index(){
        Gate::authorize('viewAny', Role::class);
        Carbon::setLocale('es');
        $roles = Role::all()->map(function ($rol) {
            return [
                'id' => $rol->id,
                'name' => $rol->name,
                'guard_name' => $rol->guard_name,
                'created_at' => Carbon::parse($rol->created_at)->isoFormat('dddd, D [de] MMMM [de] YYYY HH:mm:ss A'),
                'updated_at' => Carbon::parse($rol->updated_at)->isoFormat('dddd, D [de] MMMM [de] YYYY HH:mm:ss A'),
            ];
        });
        return response()->json([
            'data' => $roles
        ]);
    }
    public function indexPermisos(){
        Gate::authorize('viewAny', Permission::class);
        $permissions = Permission::all();
        return response()->json([
            'permissions' => $permissions
        ]);
    }
    public function store(Request $request){
        Gate::authorize('create', Role::class);
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array'
        ]);
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);
        return response()->json($role->load('permissions'));
    }
    public function show($id, Role $role){
        Gate::authorize('view', $role);
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }
    public function update(Request $request, $id, Role $role){
        Gate::authorize('update', $role);
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'array'
        ]);
        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);
        return response()->json($role->load('permissions'));
    }
    public function destroy($id){
        $role = Role::findOrFail($id);
        Gate::authorize('delete', $role);
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(['message' => 'Rol eliminado']);
    }
}
