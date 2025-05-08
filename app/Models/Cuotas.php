<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuotas extends Model{
    use HasFactory;
    protected $table = 'cuotas';
    protected $fillable = [
        'prestamo_id',
        'numero_cuota',
        'capital',
        'interes',
        'Dias',
        'Tasa_Interes_Diario',
        'Monto_Interes_Pagar',
        'Monto_Capital_Pagar',
        'Saldo_Capital',
        'Fecha_Inicio',
        'fecha_vencimiento',
        'MOnto_Capital_Mas_Interes_a_Pagar',
        'estado'
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
