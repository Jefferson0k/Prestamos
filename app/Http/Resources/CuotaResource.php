<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CuotaResource extends JsonResource{
    public function toArray(Request $request): array{
        $fechaInicio = $this->Fecha_Inicio ? Carbon::parse($this->Fecha_Inicio)->startOfDay() : null;
        $hoy = Carbon::now()->startOfDay();
        $dias = $fechaInicio ? $fechaInicio->diffInDays($hoy) : 0;

        $capital = floatval($this->capital ?? 0);
        $tasaInteresDiarioEntero = floatval($this->Tasa_Interes_Diario ?? 0);
        $tasaInteresDiario = $tasaInteresDiarioEntero / 100;

        $interes = $dias * $tasaInteresDiario;
        $montoInteresPagar = ($interes / 100) * $capital;
        $montoCapitalPagar = floatval($this->Monto_Capital_Pagar ?? 0);
        $saldoCapital = $capital - $montoCapitalPagar;
        $montoTotalPagar = $montoInteresPagar + $montoCapitalPagar;

        return [
            'id' => $this->id,
            'numero_cuota' => 'MES ' . $this->numero_cuota,
            'capital' => number_format($capital, 2),
            'interes' => number_format($interes, 2),
            'dias' => $dias,
            'tasa_interes_diario' => number_format($tasaInteresDiarioEntero, 2),
            'monto_interes_pagar' => number_format($montoInteresPagar, 2),
            'monto_capital_pagar' => $this->Monto_Capital_Pagar ?? 0,
            'saldo_capital' => number_format($saldoCapital, 2),
            'fecha_inicio' => $fechaInicio ? $fechaInicio->format('d-m-Y') : '00-00-0000',
            'fecha_vencimiento' => $montoCapitalPagar > 0
                ? Carbon::now()->format('d-m-Y')
                : ($this->fecha_vencimiento
                    ? Carbon::parse($this->fecha_vencimiento)->format('d-m-Y')
                    : '00-00-0000'),

            'monto_total_pagar' => number_format($montoTotalPagar, 2),
            'estado' => $this->estado,
        ];
    }
}
