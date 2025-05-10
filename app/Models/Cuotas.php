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
        'fecha_vencimiento' => 'date',
        'Fecha_Inicio' => 'date',
        'capital' => 'decimal:2',
        'interes' => 'decimal:2',
        'Monto_Interes_Pagar' => 'decimal:2',
        'Tasa_Interes_Diario' => 'decimal:2',
        'Monto_Capital_Pagar' => 'decimal:2',
        'Saldo_Capital' => 'decimal:2',
        'MOnto_Capital_Mas_Interes_a_Pagar' => 'decimal:2'
    ];
    
    public function prestamo(){
        return $this->belongsTo(Prestamos::class, 'prestamo_id');
    }    
    public function pagos(){
        return $this->hasMany(Pagos::class);
    }
}
