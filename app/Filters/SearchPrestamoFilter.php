<?php

namespace App\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class SearchPrestamoFilter
{
    protected array $columns = [
        'id' => true,
        'cliente_id' => true,
        'capital' => true,
        'numero_cuotas' => true,
        'estado_cliente' => false,
        'recomendado_id' => true,
        'tasa_interes_diario' => true,
    ];

    protected array $clienteColumns = [
        'dni',
        'nombre',
        'apellidos',
    ];

    public function handle(Builder $query, Closure $next): Builder
    {
        if (!request()->filled('search')) {
            return $next($query);
        }

        $search = '%' . request('search') . '%';

        $query->where(function (Builder $q) use ($search) {
            foreach ($this->columns as $column => $castToText) {
                if ($castToText) {
                    $q->orWhereRaw("CAST({$column} AS TEXT) ILIKE ?", [$search]);
                } else {
                    $q->orWhereRaw("{$column} ILIKE ?", [$search]);
                }
            }

            $q->orWhereHas('cliente', function (Builder $clienteQuery) use ($search) {
                foreach ($this->clienteColumns as $column) {
                    $clienteQuery->orWhereRaw("{$column} ILIKE ?", [$search]);
                }
            });
        });

        return $next($query);
    }
}
