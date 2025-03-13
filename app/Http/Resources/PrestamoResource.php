<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class PrestamoResource extends JsonResource{
    public function toArray($request){
        return [
            'id'                => $this->id,
            'cliente'           => new ClienteResource($this->cliente),
            'fecha_inicio'      => $this->fecha_inicio,
            'fecha_vencimiento' => $this->fecha_vencimiento_formatted,
            'capital'           => $this->capital,
            'numero_cuotas'     => $this->numero_cuotas,
            'estado_cliente'    => $this->estado_cliente,
            'recomendacion'     => $this->recomendacion,
            'tasa_interes_diario' => $this->tasa_interes_diario,
        ];
    }
}
