<?php

namespace App\Pipelines;

use Closure;
class EstadoClientePrestamoFilter{
    protected $estado;
    public function __construct($estado){
        $this->estado = $estado;
    }
    public function handle($query, Closure $next){
        if (!is_null($this->estado)) {
            $query->whereHas('prestamos', function ($q) {
                $q->where('estado_cliente', $this->estado);
            });
        }
        return $next($query);
    }
}
