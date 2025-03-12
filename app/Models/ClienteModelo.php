<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteModelo extends Model{
    protected $table = 'clientes';
    protected $fillable = [
        'nombres',
        'apellidos',
        'direccion',
        'centro_de_trabajo',
        'celular',
        'dni',
        'fecha_de_inicio',
        'fecha_de_vencimiento',
        'tasa_de_interes_diario',
        'capital_inicial',
        'capital_del_mes',
        'capital_actual',
        'interes_actual',
        'interes_total',
        'total',
        'numero_cuotas',
        'estado_del_cliente',
        'foto_del_cliente',
        'recomendacion'
    ];

    // Relación si un cliente tiene muchos préstamos (ejemplo)
    /*public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }*/
}
