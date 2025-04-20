<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Usuario\StoreUserRequest;
use App\Http\Requests\Usuario\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller{
    public function index(Request $request){
        Gate::authorize('viewAny', User::class);
        try {
            $name = $request->get('name');
            $users = User::when($name, function ($query, $name) {
                return $query->whereLike('name', "%$name%");
            })->orderBy('id','asc')->paginate(12);
            return response()->json([
                'data' => UserResource::collection($users),
                'pagination' => [
                    'total' => $users->total(),
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem()
                ]
                ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al listar los usuarios',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function store(StoreUserRequest $storeUserRequest){
        Gate::authorize('create', User::class);
        $validated = $storeUserRequest->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated = $storeUserRequest->safe()->except(['status']);
        $user = User::create(Arr::except($validated, ['status']));
        return response()->json($user);
    }
    public function show(User $user){
        Gate::authorize('view', User::class);
        return response()->json([
            'status' => true,
            'message' => 'Usuario encontrado',
            'user' => new UserResource($user),
        ], 200);
    }
    public function update(UpdateUserRequest $updateUserRequest, User $user){
        Gate::authorize('update', $user);
        $validated = $updateUserRequest->validated();
        $validated['status'] = ($validated['status'] ?? 'inactivo') === 'activo';
        $user->update($validated);
        return response()->json([
            'status' => true,
            'message' => 'Usuario actualizado correctamente',
            'user' => new UserResource($user->refresh()),
        ]);
    }
    public function destroy(User $user){
        Gate::authorize('dalete', $user);
        $user->delete();
        return response()->json([
            'status' => true,
            'messeger' => 'Usuario eliminado correctamente'
        ]);
    }
}