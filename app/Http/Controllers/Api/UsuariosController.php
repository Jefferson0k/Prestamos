<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Usuario\StoreUserRequest;
use App\Http\Requests\Usuario\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller{
    public function index(Request $request){
        Gate::authorize('viewAny', User::class);
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $estado = $request->input('status');
        $onlineStatus = $request->input('online');
        $searchTerms = [];
        if (!empty($search)) {
            $normalizedSearch = strtolower(trim(preg_replace('/\s+/', ' ', $search)));
            $searchTerms = explode(' ', $normalizedSearch);
        }

        $query = User::query();

        if (!empty($searchTerms)) {
            foreach ($searchTerms as $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%')
                        ->orWhere('username', 'like', '%' . $term . '%');
                });
            }
        }

        if (isset($estado)) {
            $query->where('status', $estado);
        }

        if (isset($onlineStatus)) {
            if ($onlineStatus == '1') {
                $query->whereIn('id', function ($query) {
                    $query->select('id')->from('users')->whereRaw('cache()->has("user-is-online-" . id)');
                });
            } elseif ($onlineStatus == '0') {
                $query->whereNotIn('id', function ($query) {
                    $query->select('id')->from('users')->whereRaw('cache()->has("user-is-online-" . id)');
                });
            }
        }
        $users = $query->paginate($perPage);
        return UserResource::collection($users);
    }
    public function store(StoreUserRequest $request){
        Gate::authorize('create', User::class);
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        return response()->json($user);
    }    
    public function show(User $user){
        Gate::authorize('view', $user);
        return response()->json([
            'status' => true,
            'message' => 'Usuario encontrado',
            'user' => new UserResource($user),
        ], 200);
    }
    public function update(UpdateUserRequest $updateUserRequest, User $user){
        Gate::authorize('update', $user);
        $validated = $updateUserRequest->validated();
        $user->update($validated);
        return response()->json([
            'status' => true,
            'message' => 'Usuario actualizado correctamente',
            'user' => new UserResource($user->refresh()),
        ]);
    }
    public function destroy(User $user){
        Gate::authorize('delete', $user);
        $user->delete();
        return response()->json([
            'status' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }

}