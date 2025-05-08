<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prestamo\StorePrestamoRequest;
use App\Http\Requests\Prestamo\UpdatePrestamoRequest;
use App\Http\Resources\ClientePrestamoResource;
use App\Http\Resources\CuotaResource;
use App\Http\Resources\PrestamoCollection;
use App\Http\Resources\PrestamoResource;
use App\Http\Resources\TalonarioResource;
use App\Models\Cliente;
use App\Models\Prestamos;
use App\Services\PrestamoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PrestamosController extends Controller{
    protected $prestamoService;
    public function __construct(PrestamoService $prestamoService){
        $this->prestamoService = $prestamoService;
    }
    public function index(Request $request){
        Gate::authorize('viewAny', Prestamos::class);
        
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $estadoCliente = $request->input('estado_cliente');        
        $query = Prestamos::with('cliente', 'pagos');        
        if (!is_null($estadoCliente)) {
            $query->where('estado_cliente', $estadoCliente);
        }        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                ->orWhere('cliente_id', 'LIKE', "%{$search}%")
                ->orWhere('capital', 'LIKE', "%{$search}%")
                ->orWhere('numero_cuotas', 'LIKE', "%{$search}%")
                ->orWhere('estado_cliente', 'LIKE', "%{$search}%")
                ->orWhere('recomendado_id', 'LIKE', "%{$search}%")
                ->orWhere('tasa_interes_diario', 'LIKE', "%{$search}%")
                ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                    $clienteQuery->where('dni', 'LIKE', "%{$search}%")
                        ->orWhere('nombre', 'LIKE', "%{$search}%")
                        ->orWhere('apellidos', 'LIKE', "%{$search}%");
                });
            });
        }
        
        $prestamos = $query->paginate($perPage);
        return PrestamoResource::collection($prestamos);
    }
    public function indexCliente(Request $request){
        Gate::authorize('viewAny', Cliente::class);
        $query = Cliente::query();
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CAST(id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("nombre ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("apellidos ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("dni ILIKE ?", ["%{$search}%"]);
            });
        }
        $clientes = $query->orderBy('nombre', 'asc')->paginate(10);
        return response()->json([
            'data' => $clientes->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'label' => "{$cliente->id} - {$cliente->nombre} {$cliente->apellidos} ({$cliente->dni})",
                    'value' => $cliente->id,
                ];
            }),
            'pagination' => [
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'next_page_url' => $clientes->nextPageUrl(),
            ],
        ]);
    }
    public function store(StorePrestamoRequest $request){
        Gate::authorize('create', Prestamos::class);
        $validatedData = $request->validated();
        $horaActualPeru = Carbon::now('America/Lima')->format('H:i:s');
        $validatedData['fecha_inicio'] = Carbon::createFromFormat('d-m-Y', $validatedData['fecha_inicio'], 'America/Lima')
            ->startOfDay()
            ->setTimeFromTimeString($horaActualPeru);
        $validatedData['fecha_vencimiento'] = Carbon::createFromFormat('d-m-Y', $validatedData['fecha_vencimiento'], 'America/Lima')
            ->startOfDay()
            ->setTimeFromTimeString($horaActualPeru);
        $prestamo = $this->prestamoService->crearPrestamo($validatedData);
        return response()->json([
            'message' => 'Préstamo creado exitosamente',
            'prestamo' => $prestamo
        ], Response::HTTP_CREATED);
    }
    public function show(Prestamos $prestamos){
        Gate::authorize('view', $prestamos);
        return new PrestamoResource($prestamos);
    }
    public function update(UpdatePrestamoRequest $request, Prestamos $prestamo) {
        Gate::authorize('update', $prestamo);
        $validatedData = $request->validated();        
        $horaActualPeru = Carbon::now('America/Lima')->format('H:i:s');       
        if (isset($validatedData['fecha_inicio']) && !$validatedData['fecha_inicio'] instanceof \DateTime) {
            $validatedData['fecha_inicio'] = Carbon::createFromFormat('d-m-Y', $validatedData['fecha_inicio'], 'America/Lima')
                ->startOfDay()
                ->setTimeFromTimeString($horaActualPeru);
        }        
        if (isset($validatedData['fecha_vencimiento']) && !$validatedData['fecha_vencimiento'] instanceof \DateTime) {
            $validatedData['fecha_vencimiento'] = Carbon::createFromFormat('d-m-Y', $validatedData['fecha_vencimiento'], 'America/Lima')
                ->startOfDay()
                ->setTimeFromTimeString($horaActualPeru);
        }
        $prestamoActualizado = $this->prestamoService->actualizarPrestamo($prestamo, $validatedData);
        return response()->json([
            'message' => 'Préstamo actualizado correctamente.',
            'prestamo' => $prestamoActualizado,
        ]);
    }
    public function destroy(Prestamos $prestamo){
        Gate::authorize('delete', $prestamo);
        $prestamo->delete();
        return response()->json([
            'message' => 'Préstamo eliminado correctamente.',
        ]);
    }
    public function consultarPrestamo($id) {
        $cliente = Cliente::with('prestamos.recomendacion')->findOrFail($id);
        $prestamos = $cliente->prestamos;
    
        $todosIds = $prestamos->pluck('id')->values();
        $pendientes = $prestamos->where('estado_cliente', 1)->pluck('id')->values();
        $enMora = $prestamos->where('estado_cliente', 2)->pluck('id')->values();
        $finalizados = $prestamos->where('estado_cliente', 4)->pluck('id')->values();
    
        return response()->json([
            'clientes' => ClientePrestamoResource::collection($prestamos),
            'cantidad_prestamos' => $prestamos->count(),
            'ids' => $todosIds,
            'Pendiente' => $pendientes,
            'Mora' => $enMora,
            'Finalizado' => $finalizados,
        ]);
    }    
    public function consultaTalonario($id){
        $prestamo = Prestamos::with('cuotas', 'cliente')->findOrFail($id);
        $cliente = $prestamo->cliente;
        $cuotas = $prestamo->cuotas->sortBy('numero_cuota')->values();
    
        return response()->json([
            'cliente' => new PrestamoCollection($cliente),
            'cantidad_prestamos' => 1,
            'cantidad_cuotas' => $cuotas->count(),
            'cuotas' => CuotaResource::collection($cuotas)
        ]);
    }    
}
