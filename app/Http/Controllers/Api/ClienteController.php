<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\StoreClienteRequest;
use App\Http\Requests\Cliente\UpdateClienteRequest;
use App\Http\Resources\Cliente\ClienteResource;
use App\Http\Resources\TipoCliente\TipoClienteResource;
use App\Models\Cliente;
use App\Models\TipoCliente;
use App\Pipelines\EstadoClientePrestamoFilter;
use App\Pipelines\SearchClienteFilter;
use App\Pipelines\TipoClienteFilter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ClienteController extends Controller {
    public function index(Request $request){
        Gate::authorize('viewAny', Cliente::class);

        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $estadoCliente = $request->input('estado_cliente');
        $tipoClienteId = $request->input('tipoCliente_id');

        $query = app(Pipeline::class)
            ->send(Cliente::query())
            ->through([
                new SearchClienteFilter($search),
                new EstadoClientePrestamoFilter($estadoCliente),
                new TipoClienteFilter($tipoClienteId),
            ])
            ->thenReturn()
            ->with(['prestamos' => function($query) use ($estadoCliente) {
                $query->latest('fecha_inicio')
                    ->take(1)
                    ->with(['pagos' => fn($q) => $q->select('id', 'prestamo_id', 'monto_capital', 'monto_interes', 'monto_total')]);

                if (!is_null($estadoCliente)) {
                    $query->where('estado_cliente', $estadoCliente);
                }
            }]);

        return ClienteResource::collection($query->paginate($perPage));
    }        
    public function store(StoreClienteRequest $request) {
        Gate::authorize('create', Cliente::class);        
        $data = $request->validated();
        $data['usuario_id'] = Auth::id();
        if ($request->hasFile('foto')) {
            $data['foto'] = $this->handleFotoUpload($request);
        }
        $cliente = Cliente::create($data);
        return response()->json([
            'message' => 'Cliente registrado exitosamente',
            'cliente' => $cliente
        ], 201);
    }
    public function show(Cliente $cliente) {
        Gate::authorize('view', $cliente);
        return response()->json($cliente);
    }
    public function update(UpdateClienteRequest $request, Cliente $cliente){
        Gate::authorize('update', $cliente);

        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->handleFotoUpload($request, $cliente);
        }

        $cliente->update($data);

        return response()->json([
            'message' => 'Cliente actualizado correctamente',
            'cliente' => $cliente
        ]);
    }
    public function destroy($id){
        $cliente = Cliente::findOrFail($id);
        Gate::authorize('delete', $cliente);
        if ($cliente->foto) {
            Storage::delete('public/customers/' . $cliente->foto);
        }
        $cliente->delete();
        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
    private function handleFotoUpload(Request $request, Cliente $cliente = null) {
        if (!$request->hasFile('foto')) return $cliente?->foto;
        if ($cliente && $cliente->foto) {
            if (file_exists(public_path('customers/' . $cliente->foto))) {
                unlink(public_path('customers/' . $cliente->foto));
            }
        }
        $file = $request->file('foto');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('customers'), $fileName);
        return $fileName;
    }
    public function indexList(){
        Gate::authorize('viewAny', Cliente::class);
        $tipoclientes = TipoCliente::where('estado', 'activo')->get();
        return TipoClienteResource::collection($tipoclientes);
    }
}
