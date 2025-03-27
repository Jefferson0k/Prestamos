<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ClienteListResource extends JsonResource{
    public function toArray($request){
        return [
            'id'                  => $this->id,
            'nombre_completo' => $this->nombre . ' ' . $this->apellidos,
            'dni'=> $this->dni,
        ];
    }
}
