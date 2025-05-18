<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuotas extends Model{
    use HasFactory;
    protected $table = 'cuotas';
    protected $fillable = [
        'numero_cuota',
        'capital',
        'fecha_inicio',
        'fecha_vencimiento',
        'dias',
        'interes',
        'tasa_interes_diario',
        'monto_interes_pagar',
        'monto_capital_pagar',
        'saldo_capital',
        'monto_capital_mas_interes_a_pagar',
        'estado',
        'prestamo_id',
        'usuario_id',
        'referencia',
        'fecha_pago',
        'observacion',
    ];
    protected $casts = [
        //'fecha_vencimiento' => 'date',
        //'fecha_inicio' => 'date',
        'capital' => 'decimal:2',
        'interes' => 'decimal:2',
        'monto_interes_pagar' => 'decimal:2',
        'tasa_interes_diario' => 'decimal:2',
        'monto_capital_pagar' => 'decimal:2',
        'saldo_capital' => 'decimal:2',
        'monto_capital_mas_interes_a_pagar' => 'decimal:2'
    ];
    
    public function prestamo(){
        return $this->belongsTo(Prestamos::class, 'prestamo_id');
    }    
    public function pagos(){
        return $this->hasMany(Pagos::class);
    }
    public function scopePendientes($query){
        return $query->where('estado', 'Pendiente');
    }

}
