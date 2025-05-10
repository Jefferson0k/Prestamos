<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagosResource extends JsonResource{
    public function toArray(Request $request): array{
        return [
            'id'             => $this->id,
            'prestamo_id'    => $this->prestamo_id,
            'Estado' => $this->prestamo->estado_cliente,
            'cuota_id'       => $this->cuota_id,
            'capital'        => $this->capital,
            'fecha_pago'     => $this->fecha_pago?->format('Y-m-d'),
            'monto_capital'  => $this->monto_capital,
            'monto_interes'  => $this->monto_interes,
            'monto_total'    => $this->monto_total,
            #Cliente
            'cliente_Dni' => $this->prestamo->cliente->dni,
            'cliente_nom_ape' => $this->prestamo->cliente->nombre. ' '.$this->prestamo->cliente->apellidos,
            'CLiente_Telefono' => $this->prestamo->cliente->telefono,
            'CLiente_Direccion' => $this->prestamo->cliente->direccion,
        ];
    }
}
