<?php

namespace App\Http\Resources\Cuota;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CuotaResource extends JsonResource{
    public function toArray(Request $request): array{
        return [
            'id' => $this->id,
            'numero_cuota' => 'MES ' . $this->numero_cuota,
            'capital' => $this->capital,
            'interes' => $this->interes,
            'dias' => $this->dias,
            'dias_calculados' => 0,
            'tasa_interes_diario' => $this->tasa_interes_diario,
            'monto_interes_pagar' => $this->monto_interes_pagar,
            'monto_capital_pagar' => $this->monto_capital_pagar ?? 0,
            'saldo_capital' => $this->saldo_capital,
            'fecha_inicio' => $this->fecha_inicio ? Carbon::parse($this->fecha_inicio)->format('d-m-Y') : '00-00-0000',
            'fecha_vencimiento' => $this->fecha_vencimiento ? Carbon::parse($this->fecha_vencimiento)->format('d-m-Y') : '00-00-0000',
            'monto_total_pagar' => $this->monto_capital_mas_interes_a_pagar,
            'estado' => $this->estado,
        ];
    }
}