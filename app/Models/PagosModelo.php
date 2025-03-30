<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PagosModelo extends Model{    
    use HasFactory;
    protected $table = 'pagos';
    protected $fillable = [
        'prestamo_id',
        'cuota_id',
        'fecha_pago',
        'monto_capital',
        'monto_interes',
        'monto_total'
    ];
    protected $casts = [
        'fecha_pago' => 'date',
        'monto_capital' => 'decimal:2',
        'monto_interes' => 'decimal:2',
        'monto_total' => 'decimal:2',
    ];
    public function prestamo(){
        return $this->belongsTo(PrestamosModelo::class);
    }
    public function cuota(){
        return $this->belongsTo(CuotasModelo::class);
    }
}
