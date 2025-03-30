<?php

namespace App\Services;

use App\Models\CuotasModelo;
use App\Models\PagosModelo;
use App\Models\PrestamosModelo;
use Carbon\Carbon;

class PagoService{
    public function registrarPago(CuotasModelo $cuota, $fechaPago){
        $prestamo = $cuota->prestamo;
        $fechaPago = Carbon::parse($fechaPago);
        
        $pago = PagosModelo::create([
            'prestamo_id' => $prestamo->id,
            'cuota_id' => $cuota->id,
            'fecha_pago' => $fechaPago,
            'monto_capital' => $cuota->capital,
            'monto_interes' => $cuota->interes,
            'monto_total' => $cuota->monto_total
        ]);
        $cuota->update(['estado' => 'Pagado']);
        $this->actualizarEstadoPrestamo($prestamo);        
        return $pago;
    }
    public function actualizarEstadoPrestamo(PrestamosModelo $prestamo){
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
    public function simularCalendarioPagos(PrestamosModelo $prestamo){
        $calendario = [];
        $saldoCapital = $prestamo->capital;
        $capitalPorCuota = $prestamo->capital / $prestamo->numero_cuotas;
        $fechaInicio = Carbon::parse($prestamo->fecha_inicio);
        
        for ($i = 1; $i <= $prestamo->numero_cuotas; $i++) {
            $fechaVencimiento = $fechaInicio->copy()->addMonths($i);
            $diasInteres = 30;
            $interesDiario = $prestamo->tasa_interes_diario / 100;
            $montoCapitalPagar = ($i < 9) ? 0 : $capitalPorCuota;
            $montoInteresPagar = $saldoCapital * $interesDiario * $diasInteres;
            if ($montoCapitalPagar > 0) {
                $saldoCapital -= $montoCapitalPagar;
            }            
            $calendario[] = [
                'numero_cuota' => $i,
                'capital' => $saldoCapital + $montoCapitalPagar,
                'fecha_inicio' => $fechaInicio->copy()->addMonths($i-1)->format('d/m/Y'),
                'fecha_pago' => $fechaVencimiento->format('d/m/Y'),
                'dias_interes' => $diasInteres,
                'tasa_interes_diario' => $prestamo->tasa_interes_diario,
                'monto_interes_pagar' => $montoInteresPagar,
                'monto_capital_pagar' => $montoCapitalPagar,
                'saldo_capital' => $saldoCapital,
                'monto_capital_mas_interes' => $montoCapitalPagar + $montoInteresPagar
            ];
        }        
        return $calendario;
    }
}
