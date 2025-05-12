<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Cuotas;
use App\Models\Prestamos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReporteController extends Controller{
    public function vista(){
        Gate::authorize('viewAny', 'reporte');
        return inertia::render('panel/Reporte/indexReporte');
    }
    public function clientesPorAnio($anio){
        Gate::authorize('viewAny', 'reporte');
        $clientesPorMes = DB::table('clientes')
            ->selectRaw('EXTRACT(MONTH FROM created_at) as mes, COUNT(*) as cantidad')
            ->whereYear('created_at', $anio)
            ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->orderBy('mes')
            ->get();
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[$i] = 0;
        }
        foreach ($clientesPorMes as $registro) {
            $data[$registro->mes] = $registro->cantidad;
        }
        return response()->json([
            'anio' => $anio,
            'clientes_por_mes' => $data,
        ]);
    }
    public function CantidadEmprestada($anio, Prestamos $prestamos){
        Gate::authorize('view', $prestamos);
        $capitalTotal = DB::table('prestamos')
            ->whereYear('fecha_inicio', $anio)
            ->sum('capital');
        $capitalPorMes = DB::table('prestamos')
            ->selectRaw("TO_CHAR(fecha_inicio, 'MM') as mes, SUM(capital) as total_capital")
            ->whereYear('fecha_inicio', $anio)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->map(function ($item) use ($capitalTotal) {
                $mesNombre = Carbon::createFromFormat('m', $item->mes)->locale('es')->monthName;
                $item->mes_nombre = mb_strtoupper($mesNombre, 'UTF-8');
                $item->total_capital = round($item->total_capital, 2);
                $item->total_capital_formatted = 'S/ ' . number_format($item->total_capital, 2);
                $item->porcentaje = $capitalTotal > 0
                    ? round(($item->total_capital / $capitalTotal) * 100, 2)
                    : 0;

                return $item;
            });
        return response()->json([
            'anio' => $anio,
            'capital_total' => round($capitalTotal, 2),
            'capital_total_formatted' => 'S/ ' . number_format($capitalTotal, 2),
            'por_mes' => $capitalPorMes,
        ]);
    }
    public function calcularInteresesMensuales(){
        $fecha_inicio_mes = Carbon::now()->startOfMonth();
        $fecha_fin_mes = Carbon::now()->endOfMonth();

        $total_intereses = Prestamos::with('cuotas')
            ->whereHas('cuotas', function ($query) use ($fecha_inicio_mes, $fecha_fin_mes) {
                $query->whereBetween('fecha_vencimiento', [$fecha_inicio_mes, $fecha_fin_mes]);
            })
            ->get()
            ->reduce(function ($carry, $prestamo) {
                return $carry + $prestamo->cuotas->sum('monto_interes_pagar');
            }, 0);

        return response()->json([
            'total_intereses' => number_format($total_intereses, 2)
        ]);
    }
    public function contarClientes(){
        $numeroClientes = Cliente::count();
        return response()->json(['numeroClientes' => $numeroClientes]);
    }
    public function numeroPrestamosPorEstado(){
        // Contar las cuotas cuyo estado es 'Vencido'
        $numeroVencidas = Cuotas::where('estado', 'Vencido')->count();

        // Retornar el número de cuotas vencidas
        return response()->json(['numeroVencidas' => $numeroVencidas]);
    }
}
