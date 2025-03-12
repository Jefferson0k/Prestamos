<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ClienteModelo extends Model{
    protected $table = 'clientes';
    protected $fillable = [
        'dni', 'nombre', 'apellidos', 'telefono', 'direccion', 'correo', 'centro_trabajo', 'foto'
    ];
    public function prestamos(){
        return $this->hasMany(PrestamosModelo::class, 'cliente_id');
    }
}