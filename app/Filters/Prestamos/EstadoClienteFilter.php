<?php

namespace App\Filters\Prestamos;

use Closure;
use Illuminate\Database\Eloquent\Builder;
class EstadoClienteFilter{
    public function __invoke(Builder $query, Closure $next){
        $estado = request('estado_cliente');
        if (!is_null($estado)) {
            $query->where('estado_cliente', $estado);
        }
        return $next($query);
    }
}
