<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller{
    public function index(){
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
        $permissions = Permission::all();

        return response()->json([
            'permissions' => $permissions
        ]);
    }
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array'
        ]);
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);
        return response()->json($role->load('permissions'));
    }
    public function show($id){
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }
    public function update(Request $request, $id){
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
        $role->delete();
        return response()->json(['message' => 'Rol eliminado']);
    }
}
