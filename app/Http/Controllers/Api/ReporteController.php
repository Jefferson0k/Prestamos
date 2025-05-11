<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
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
    public function CantidadEmprestada($anio){
        Gate::authorize('viewAny', 'reporte');
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
}
