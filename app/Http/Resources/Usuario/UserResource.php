<?php

namespace App\Http\Resources\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
class UserResource extends JsonResource{
    public function toArray($request){
        $role = $this->roles->first();
        return [
            'id' => $this->id,
            'dni' => $this->dni,
            'name1' => $this->name . ' ' . $this->apellidos,
            'name' => $this->name,
            'apellidos' => $this->apellidos,
            'nacimiento' => Carbon::parse($this->nacimiento)->format('d-m-Y'),
            'email' => $this->email,
            'username' => $this->username,
            'status' => $this->status,
            'online' => cache()->has('user-is-online-' . $this->id),
            'creacion' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s A'),
            'role' => $role ? $role->name : null,
            'role_id' => $role ? $role->id : null,
        ];
    }
}
