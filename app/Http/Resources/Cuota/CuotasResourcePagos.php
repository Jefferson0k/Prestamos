<?php

namespace App\Http\Resources\Cuota;

use Illuminate\Http\Resources\Json\JsonResource;

class CuotasResourcePagos extends JsonResource{
    public function toArray($request){
        return [
            'id' => $this->id,
            'nombre' => "{$this->nombre} {$this->apellidos}",
            'dni' => $this->dni,
            'cuotas_pendientes' => $this->cuotas()->pendientes()->count(),
        ];
    }
}
