<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prestamo\StorePrestamoRequest;
use App\Http\Requests\Prestamo\UpdatePrestamoRequest;
use App\Http\Resources\PrestamoResource;
use App\Models\ClienteModelo;
use App\Models\PrestamosModelo;
use App\Services\PagoService;
use App\Services\PrestamoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PrestamosController extends Controller{
    protected $prestamoService;
    protected $pagoService;
    public function __construct(PrestamoService $prestamoService, PagoService $pagoService){
        $this->prestamoService = $prestamoService;
        $this->pagoService = $pagoService;
    }
    public function index(Request $request){
        $query = PrestamosModelo::with('cliente', 'pagos');
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                ->orWhere('cliente_id', 'LIKE', "%{$search}%")
                ->orWhere('capital', 'LIKE', "%{$search}%")
                ->orWhere('numero_cuotas', 'LIKE', "%{$search}%")
                ->orWhere('estado_cliente', 'LIKE', "%{$search}%")
                ->orWhere('recomendacion', 'LIKE', "%{$search}%")
                ->orWhere('tasa_interes_diario', 'LIKE', "%{$search}%")
                ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                    $clienteQuery->where('dni', 'LIKE', "%{$search}%")
                                ->orWhere('nombre', 'LIKE', "%{$search}%")
                                ->orWhere('apellidos', 'LIKE', "%{$search}%");
                });
            });
        }
        $prestamos = $query->paginate(10);
        return response()->json([
            'total' => $prestamos->total(),
            'data' => PrestamoResource::collection($prestamos),
        ]);
    }
    public function indexCliente(Request $request){
        $query = ClienteModelo::query();
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
        $validatedData = $request->validated();
        $validatedData['fecha_inicio'] = Carbon::parse($validatedData['fecha_inicio']);
        $validatedData['fecha_vencimiento'] = Carbon::parse($validatedData['fecha_vencimiento']);    
        $prestamo = $this->prestamoService->crearPrestamo($validatedData);
        return response()->json([
            'message' => 'Préstamo creado exitosamente',
            'prestamo' => $prestamo
        ], Response::HTTP_CREATED);
    }
    public function show(PrestamosModelo $prestamo){
        $cuotas = $prestamo->cuotas()->orderBy('numero_cuota')->get();
        $pagos = $prestamo->pagos()->orderBy('created_at', 'desc')->get();
        $simulacion = $this->pagoService->simularCalendarioPagos($prestamo);
        return response()->json([
            'prestamo' => $prestamo,
            'cuotas' => $cuotas,
            'pagos' => $pagos,
            'simulacion' => $simulacion,
        ]);
    }
    public function edit(PrestamosModelo $prestamo){
        $clientes = ClienteModelo::all();

        return response()->json([
            'prestamo' => $prestamo,
            'clientes' => $clientes,
        ]);
    }
    public function update(UpdatePrestamoRequest $request, PrestamosModelo $prestamo){
        $prestamo->update($request->validated());

        return response()->json([
            'message' => 'Préstamo actualizado correctamente.',
            'prestamo' => $prestamo,
        ]);
    }
    public function destroy(PrestamosModelo $prestamo){
        $prestamo->delete();
        return response()->json([
            'message' => 'Préstamo eliminado correctamente.',
        ]);
    }
    public function simulacion(PrestamosModelo $prestamo){
        $simulacion = $this->pagoService->simularCalendarioPagos($prestamo);
        return response()->json([
            'prestamo' => $prestamo,
            'simulacion' => $simulacion,
        ]);
    }

}
