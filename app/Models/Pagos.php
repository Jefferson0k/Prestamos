<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagos extends Model{
    use HasFactory;
    protected $table = 'pagos';
    protected $fillable = [
        'prestamo_id',
        'cuota_id',
        'capital',
        'fecha_pago',
        'monto_capital',
        'monto_interes',
        'monto_total',
        'metodo_pago',
        'moneda',
        'Codigo_Comprobante',
        'referencia',
        'observacion',
        'usuario_id',
        'estado',
        'codigo_pago_id'
    ];
    protected $casts = [
        'fecha_pago' => 'date',
        'monto_capital' => 'decimal:2',
        'monto_interes' => 'decimal:2',
        'monto_total' => 'decimal:2',
        'metodo_pago' => 'string',
        'moneda' => 'string',
        'Codigo_Comprobante' => 'string',
        'referencia' => 'string',
        'observacion' => 'string',
        'estado' => 'string',
    ];
    public function prestamo(){
        return $this->belongsTo(Prestamos::class);
    }
    public function cuota(){
        return $this->belongsTo(Cuotas::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
