<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestamosModelo extends Model{
    protected $table = 'prestamos';
    protected $fillable = [
        'cliente_id', 'fecha_inicio', 'fecha_vencimiento',
        'capital', 'numero_cuotas', 'estado_cliente',
        'recomendacion', 'tasa_interes_diario'
    ];
    public function cliente(){
        return $this->belongsTo(ClienteModelo::class, 'cliente_id');
    }
    public function pagos(){
        return $this->hasMany(PagosModelo::class, 'prestamo_id');
    }
    public function getFechaVencimientoFormattedAttribute(){
        return date('d-m-Y', strtotime($this->fecha_vencimiento));
    }
    public function scopeActivos($query){
        return $query->where('estado_cliente', 'activo');
    }
}
