<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class PrestamosModelo extends Model{    
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
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
        'capital' => 'decimal:2',
        'tasa_interes_diario' => 'decimal:2',
        'monto_total' => 'decimal:2',
    ];
    public function cliente(){
        return $this->belongsTo(ClienteModelo::class);
    }
    public function cuotas(){
        return $this->hasMany(CuotasModelo::class);
    }
    public function pagos(){
        return $this->hasMany(PagosModelo::class);
    }
}
