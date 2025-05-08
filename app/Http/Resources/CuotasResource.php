<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CuotasResource extends JsonResource{
    public function toArray($request){
        return [
            'id' => $this->id,
            'prestamo_id' => $this->prestamo_id,
            'numero_cuota' => $this->numero_cuota,
            'capital' => $this->capital,
            'interes' => $this->interes,
            'dias' => $this->Dias,
            'tasa_interes_diario' => $this->Tasa_Interes_Diario,
            'monto_interes_pagar' => $this->Monto_Interes_Pagar,
            'monto_capital_pagar' => $this->Monto_Capital_Pagar ?? '00.00',
            'saldo_capital' => $this->Saldo_Capital,
            'Fecha_Inicio' => Carbon::parse($this->Fecha_Inicio)->format('d-m-Y h:i:s A'),
            'fecha_vencimiento' => $this->fecha_vencimiento 
                ? Carbon::parse($this->fecha_vencimiento)->format('d-m-Y h:i:s A') 
                : '00-00-00 00:00:00',
            'monto_capital_mas_interes_a_pagar' => $this->MOnto_Capital_Mas_Interes_a_Pagar,
            'estado' => $this->estado,
        ];
    }
}
