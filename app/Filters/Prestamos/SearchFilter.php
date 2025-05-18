<?php

namespace App\Filters\Prestamos;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter{
    public function __invoke(Builder $query, Closure $next){
        $search = request('search');

        if (!empty($search)) {
            $words = preg_split('/\s+/', $search);

            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($subQuery) use ($word) {
                        $subQuery->where('id', 'ILIKE', "%{$word}%")
                            ->orWhere('cliente_id', 'ILIKE', "%{$word}%")
                            ->orWhere('capital', 'ILIKE', "%{$word}%")
                            ->orWhere('numero_cuotas', 'ILIKE', "%{$word}%")
                            ->orWhere('estado_cliente', 'ILIKE', "%{$word}%")
                            ->orWhere('recomendado_id', 'ILIKE', "%{$word}%")
                            ->orWhere('tasa_interes_diario', 'ILIKE', "%{$word}%")
                            ->orWhereHas('cliente', function ($clienteQuery) use ($word) {
                                $clienteQuery->where('dni', 'ILIKE', "%{$word}%")
                                    ->orWhere('nombre', 'ILIKE', "%{$word}%")
                                    ->orWhere('apellidos', 'ILIKE', "%{$word}%");
                            });
                    });
                }
            });
        }

        return $next($query);
    }
}
