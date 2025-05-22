<?php

namespace App\Filters;
use Closure;
class EstadoClienteFilter{
    public function handle($query, Closure $next){
        if (request()->filled('estado_cliente')) {
            $query->where('estado_cliente', request('estado_cliente'));
        }
        return $next($query);
    }
}
