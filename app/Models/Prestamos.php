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
        'recomendado_id',
        'tasa_interes_diario',
        'monto_total',
        'usuario_id',
        'referencia'        
    ];
    protected $casts = [
        'capital' => 'decimal:2',
        'tasa_interes_diario' => 'decimal:2',
        'monto_total' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_vencimiento' => 'datetime',
    ];    
    protected $dates = [
        'fecha_inicio',
        'fecha_vencimiento',
    ];
    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }
    public function recomendacion(){
        return $this->belongsTo(Cliente::class, 'recomendado_id');
    }
    public function cuotas(){
        return $this->hasMany(Cuotas::class, 'prestamo_id');
    }
    public function pagos(){
        return $this->hasMany(Pagos::class, 'prestamo_id');
    }
    public function user() {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
