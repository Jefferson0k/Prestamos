<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TipoCliente extends Model{
    use HasFactory;
    protected $table = 'tipo_cliente';

    protected $fillable = [
        'nombre',
        'estado',
        'usuario_id'
    ];
    public function prestamos(){
        return $this->hasMany(Prestamos::class, 'cliente_id');
    }
    public function user() {
        return $this->belongsTo(User::class, 'usuario_id');
    }    
}
