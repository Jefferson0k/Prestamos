<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class PagoResource extends JsonResource{
    public function toArray($request){
        return [
            'id'                   => $this->id,
            'prestamo_id'          => $this->prestamo_id,
            'numero_cuota'         => $this->numero_cuota,
            'capital'              => $this->capital,
            'fecha_inicio'         => $this->fecha_inicio,
            'fecha_pago'           => $this->fecha_pago_formatted,
            'dias_interes'         => $this->dias_interes,
            'tasa_interes_diario'  => $this->tasa_interes_diario,
            'monto_interes_pagar'  => $this->monto_interes_pagar,
            'monto_capital_pagar'  => $this->monto_capital_pagar,
            'saldo_capital'        => $this->saldo_capital,
            'monto_capital_mas'    => $this->monto_capital_mas,
        ];
    }
}
