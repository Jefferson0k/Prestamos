<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class PrestamoResource extends JsonResource{
    public function toArray($request){
        return [
            'id'                => $this->id,
            'id_cliente'        => $this->cliente_id,
            'dni'               => $this->cliente->dni,
            'NombreCompleto' => "{$this->cliente->nombre} {$this->cliente->apellidos}",
            'fecha_inicio'      => \Carbon\Carbon::parse($this->fecha_inicio)->format('d-m-Y'),
            'fecha_vencimiento' => \Carbon\Carbon::parse($this->fecha_vencimiento)->format('d-m-Y'),
            'capital'           => $this->capital,
            'numero_cuotas'     => $this->numero_cuotas,
            'estado_cliente'    => $this->estado_cliente,
            'recomendacion'     => $this->recomendacion,
            'tasa_interes_diario' => $this->tasa_interes_diario,
        ];
    }
}
