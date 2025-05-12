<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Codigo extends Model{

    use HasFactory;
    protected $table = 'codigos_pagos';
    protected $fillable = [
        'codigo', 'estado'
    ];
    public function prestamos(){
        return $this->hasMany(Prestamos::class, 'cliente_id');
    }
}
