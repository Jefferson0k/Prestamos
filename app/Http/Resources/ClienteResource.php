<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ClienteResource extends JsonResource {
    public function toArray($request) {
        return [
            'id' => $this->id,
            'dni' => $this->dni,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'foto' => $this->foto ? asset("storage/clientes/{$this->foto}") : null,
        ];
    }
}
