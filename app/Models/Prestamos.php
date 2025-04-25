<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Prestamos extends Model{    
    use HasFactory;
    protected $table = 'prestamos';
    protected $fillable = [
        'cliente_id',
        'fecha_inicio',
        'fecha_vencimiento',
        'capital',
        'numero_cuotas',
        'estado_cliente',
        'recomendacion',
        'tasa_interes_diario',
        'monto_total'
    ];
    protected $casts = [
        'capital' => 'decimal:2',
        'tasa_interes_diario' => 'decimal:2',
        'monto_total' => 'decimal:2',
    ];
    protected $dates = [
        'fecha_inicio',
        'fecha_vencimiento',
    ];    
    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }
    public function cuotas(){
        return $this->hasMany(Cliente::class, 'prestamo_id');
    }    
    public function pagos(){
        return $this->hasMany(Pagos::class, 'prestamo_id'); 
    }
}
