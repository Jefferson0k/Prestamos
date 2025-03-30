<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuotasModelo extends Model{
    use HasFactory;
    protected $fillable = [
        'prestamo_id',
        'numero_cuota',
        'capital',
        'interes',
        'monto_total',
        'fecha_vencimiento',
        'estado'
    ];
    protected $casts = [
        'fecha_vencimiento' => 'date',
        'capital' => 'decimal:2',
        'interes' => 'decimal:2',
        'monto_total' => 'decimal:2',
    ];
    public function prestamo(){
        return $this->belongsTo(PrestamosModelo::class);
    }

    public function pagos(){
        return $this->hasMany(PagosModelo::class);
    }
}
