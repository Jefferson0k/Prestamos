<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Usuario\StoreUserRequest;
use App\Http\Requests\Usuario\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
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

        $query = User::query();

        if (!empty($search)) {
            $normalizedSearch = strtolower(trim(preg_replace('/\s+/', ' ', $search)));
            $searchTerms = explode(' ', $normalizedSearch);

            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere(function ($subQuery) use ($term) {
                        $subQuery->where('name', 'ILIKE', '%' . $term . '%')
                            ->orWhere('email', 'ILIKE', '%' . $term . '%')
                            ->orWhere('dni', 'ILIKE', '%' . $term . '%')
                            ->orWhere('apellidos', 'ILIKE', '%' . $term . '%')
                            ->orWhere('nacimiento', 'ILIKE', '%' . $term . '%')
                            ->orWhere('status', 'ILIKE', '%' . $term . '%')
                            ->orWhere('username', 'ILIKE', '%' . $term . '%');
                    });
                }
            });
        }
        if (isset($estado)) {
            $query->where('status', $estado);
        }
        if (isset($onlineStatus)) {
            if ($onlineStatus == '1') {
                $query->whereIn('id', function ($subquery) {
                    $subquery->select('id')
                        ->from('users')
                        ->whereRaw("EXISTS (SELECT 1 FROM pg_catalog.pg_tables WHERE cache_key = CONCAT('user-is-online-', id))");
                });
            } elseif ($onlineStatus == '0') {
                $query->whereNotIn('id', function ($subquery) {
                    $subquery->select('id')
                        ->from('users')
                        ->whereRaw("EXISTS (SELECT 1 FROM pg_catalog.pg_tables WHERE cache_key = CONCAT('user-is-online-', id))");
                });
            }
        }
        $users = $query->paginate($perPage);
        return UserResource::collection($users);
    }
    public function store(StoreUserRequest $request){
        Gate::authorize('create', User::class);
        $validated = $request->validated();
        $validated['nacimiento'] = Carbon::createFromFormat('d/m/Y', $validated['nacimiento'])->format('Y-m-d');
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
    public function update(UpdateUserRequest $request, User $user){
        $data = $request->validated();
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
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