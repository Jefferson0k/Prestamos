<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PagosModelo extends Model{
    protected $table = 'pagos';
    protected $fillable = [
        'prestamo_id', 'numero_cuota', 'capital', 'fecha_inicio',
        'fecha_pago', 'dias_interes', 'tasa_interes_diario',
        'monto_interes_pagar', 'monto_capital_pagar',
        'saldo_capital', 'monto_capital_mas'
    ];
    public function prestamo(){
        return $this->belongsTo(PrestamosModelo::class, 'prestamo_id');
    }
    public function getFechaPagoFormattedAttribute(){
        return date('d-m-Y', strtotime($this->fecha_pago));
    }
    public function scopePorPrestamo($query, $prestamoId){
        return $query->where('prestamo_id', $prestamoId);
    }
}
