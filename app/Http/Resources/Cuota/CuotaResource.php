<?php

namespace App\Http\Resources\Cuota;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class CuotaResource extends JsonResource{
    public function toArray(Request $request): array{
        return [
            'id' => $this->id,
            'numero_cuota' => $this->numero_cuota,
            'capital' =>$this->capital,
            'fecha_inicio' => $this->fecha_inicio 
                ? Carbon::parse($this->fecha_inicio)->format('d-m-Y') 
                : '00-00-0000',
            'fecha_vencimiento' => $this->fecha_vencimiento
                ? Carbon::parse($this->fecha_vencimiento)->format('d-m-Y')
                : '00-00-0000',
            'dias' => $this->dias ?? 0,
            'tasa_interes_diario' => $this->tasa_interes_diario,
            'interes' => $this->interes,
            'monto_interes_pagar' => $this->monto_interes_pagar,
            'monto_capital_paga' => $this->monto_capital_paga ?? 0,
            'saldo_capital' => $this->saldo_capital,
            'monto_capital_mas_interes_a_pagar' => $this->monto_capital_mas_interes_a_pagar,
            'estado' =>$this->estado
        ];
    }
}