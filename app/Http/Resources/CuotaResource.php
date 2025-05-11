<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CuotaResource extends JsonResource{
    public function toArray(Request $request): array{
        $fechaInicio = $this->fecha_inicio ? Carbon::parse($this->fecha_inicio)->startOfDay() : null;
        $hoy = Carbon::now()->startOfDay();
        $dias = $fechaInicio ? $fechaInicio->diffInDays($hoy) : 0;

        $diasCalculados = $this->calcularDiasParaInteres($dias);

        $capital = floatval($this->capital ?? 0);
        $tasaInteresDiarioEntero = floatval($this->tasa_interes_diario ?? 0);
        $tasaInteresDiario = $tasaInteresDiarioEntero / 100;

        $interes = $diasCalculados * $tasaInteresDiario;
        $montoInteresPagar = ($interes / 100) * $capital;
        $montoCapitalPagar = floatval($this->monto_capital_pagar ?? 0);
        $saldoCapital = $capital - $montoCapitalPagar;
        $montoTotalPagar = $montoInteresPagar + $montoCapitalPagar;

        $estado = $dias > 30 ? 'Vencido' : $this->estado;

        return [
            'id' => $this->id,
            'numero_cuota' => 'MES ' . $this->numero_cuota,
            'capital' => number_format($capital, 2),
            'interes' => number_format($interes, 2),
            'dias' => $dias,
            'tasa_interes_diario' => number_format($tasaInteresDiarioEntero, 2),
            'monto_interes_pagar' => number_format($montoInteresPagar, 2),
            'monto_capital_pagar' => $this->monto_capital_pagar ?? 0,
            'saldo_capital' => number_format($saldoCapital, 2),
            'fecha_inicio' => $fechaInicio ? $fechaInicio->format('d-m-Y') : '00-00-0000',
            'fecha_vencimiento' => $montoCapitalPagar > 0
                ? Carbon::now()->format('d-m-Y')
                : ($this->fecha_vencimiento
                    ? Carbon::parse($this->fecha_vencimiento)->format('d-m-Y')
                    : '00-00-0000'),

            'monto_total_pagar' => number_format($montoTotalPagar, 2),
            'estado' => $estado,
        ];
    }
    private function calcularDiasParaInteres(int $dias): int{
        if ($dias >= 1 && $dias < 15) {
            return 15;
        }        
        if ($dias == 15) {
            return 15;
        }        
        if ($dias >= 16 && $dias < 30) {
            return 30;
        }        
        return $dias;
    }
}