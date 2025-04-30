<?php

namespace App\Services;

use App\Models\Cuotas;
use App\Models\Pagos;
use App\Models\Prestamos;
use Carbon\Carbon;

class PagoService{
    public function registrarPago(Cuotas $cuota, $fechaPago){
        $prestamo = $cuota->prestamo;
        $fechaPago = Carbon::parse($fechaPago);
    
        $pago = Pagos::create([
            'prestamo_id' => $prestamo->id,
            'cuota_id' => $cuota->id,
            'fecha_pago' => $fechaPago,
            'capital' => $cuota->capital ?? 0,
            'monto_capital' => $cuota->capital,
            'monto_interes' => $cuota->interes,
            'monto_total' => $cuota->monto_total
        ]);
    
        $cuota->update(['estado' => 'Pagado']);
        $this->actualizarEstadoPrestamo($prestamo);
    
        return $pago;
    }    
    public function actualizarEstadoPrestamo(Prestamos $prestamo){
        $cuotasPendientes = $prestamo->cuotas()->where('estado', '!=', 'Pagado')->count();
        $cuotasVencidas = $prestamo->cuotas()
            ->where('estado', '!=', 'Pagado')
            ->where('fecha_vencimiento', '<', Carbon::now())
            ->count();        
        if ($cuotasPendientes == 0) {
            $prestamo->update(['estado_cliente' => 'Paga']);
        } elseif ($cuotasVencidas > 0) {
            $prestamo->update(['estado_cliente' => 'Moroso']);
        }
    }
    public function simularCalendarioPagos(Prestamos $prestamo){
        $calendario = [];
        $saldoCapital = $prestamo->capital;
        $capitalPorCuota = $saldoCapital / $prestamo->numero_cuotas;
        $fechaInicio = Carbon::parse($prestamo->fecha_inicio);

        for ($i = 1; $i <= $prestamo->numero_cuotas; $i++) {
            $fechaCuotaInicio = $fechaInicio->copy()->addMonthsNoOverflow($i - 1);
            $fechaVencimiento = $fechaInicio->copy()->addMonthsNoOverflow($i);
            $diasInteres = $fechaCuotaInicio->diffInDays($fechaVencimiento);
            $tasaDiaria = $prestamo->tasa_interes_diario / 100;
            $montoInteresPagar = $saldoCapital * $tasaDiaria * $diasInteres;
            $montoCapitalPagar = $capitalPorCuota;
            $saldoCapital -= $montoCapitalPagar;
            if ($saldoCapital < 0) $saldoCapital = 0;
            $calendario[] = [
                'numero_cuota' => $i,
                'capital' => round($montoCapitalPagar, 2),
                'fecha_inicio' => $fechaCuotaInicio->format('d/m/Y'),
                'fecha_pago' => $fechaVencimiento->format('d/m/Y'),
                'dias_interes' => $diasInteres,
                'tasa_interes_diario' => $prestamo->tasa_interes_diario,
                'tasa_interes_mensual' => round($tasaDiaria * $diasInteres * 100, 2),
                'monto_interes_pagar' => round($montoInteresPagar, 2),
                'monto_capital_pagar' => round($montoCapitalPagar, 2),
                'saldo_capital' => round($saldoCapital, 2),
                'monto_capital_mas_interes' => round($montoInteresPagar + $montoCapitalPagar, 2)
            ];
        }
        return $calendario;
    }
}
