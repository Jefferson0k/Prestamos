<?php

namespace App\Services;

use App\Models\Cuotas;
use App\Models\Prestamos;
use Carbon\Carbon;

class PrestamoService{
    public function crearPrestamo(array $data){
        $montoTotal = $this->calcularMontoTotal(
            $data['capital'],
            $data['tasa_interes_diario'],
            $data['numero_cuotas']
        );
        
        $prestamo = Prestamos::create([
            'cliente_id' => $data['cliente_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_vencimiento' => $data['fecha_vencimiento'],
            'capital' => $data['capital'],
            'numero_cuotas' => $data['numero_cuotas'],
            'estado_cliente' => $data['estado_cliente'] ?? 'Paga',
            'recomendacion' => $data['recomendacion'] ?? null,
            'tasa_interes_diario' => $data['tasa_interes_diario'],
            'monto_total' => $montoTotal
        ]);
        $this->generarCuotas($prestamo);
        return $prestamo;
    }
    public function calcularMontoTotal($capital, $tasaInteresDiario, $numeroCuotas){
        $diasTotales = $numeroCuotas * 30;
        $interes = $capital * ($tasaInteresDiario / 100) * $diasTotales;
        return $capital + $interes;
    }
    public function generarCuotas(Prestamos $prestamo){
        $fechaInicio = Carbon::parse($prestamo->fecha_inicio);
        $capitalPorCuota = $prestamo->capital / $prestamo->numero_cuotas;
        $saldoCapital = $prestamo->capital;
        
        for ($i = 1; $i <= $prestamo->numero_cuotas; $i++) {
            $fechaVencimiento = $fechaInicio->copy()->addMonths($i);
            $interes = $saldoCapital * ($prestamo->tasa_interes_diario / 100) * 30;
            Cuotas::create([
                'prestamo_id' => $prestamo->id,
                'numero_cuota' => $i,
                'capital' => $capitalPorCuota,
                'interes' => $interes,
                'monto_total' => $capitalPorCuota + $interes,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'Pendiente'
            ]);
            $saldoCapital -= $capitalPorCuota;
        }
    }
}
