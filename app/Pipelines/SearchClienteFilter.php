<?php

namespace App\Pipelines;

use Closure;
class SearchClienteFilter{
    protected $terms;
    public function __construct(?string $search){
        $search = strtolower(trim(preg_replace('/\s+/', ' ', $search)));
        $this->terms = array_filter(explode(' ', $search), fn($term) => strlen($term) >= 2);
    }
    public function handle($query, Closure $next){
        if (!empty($this->terms)) {
            $query->where(function ($q) {
                foreach ($this->terms as $term) {
                    $q->where(function ($subQuery) use ($term) {
                    $subQuery->whereRaw("dni ILIKE ?", ["%{$term}%"])
                        ->orWhereRaw("nombre ILIKE ?", ["%{$term}%"])
                        ->orWhereRaw("apellidos ILIKE ?", ["%{$term}%"])
                        ->orWhereRaw("telefono ILIKE ?", ["%{$term}%"])
                        ->orWhereRaw("direccion ILIKE ?", ["%{$term}%"])
                        ->orWhereRaw("correo ILIKE ?", ["%{$term}%"])
                        ->orWhereRaw("centro_trabajo ILIKE ?", ["%{$term}%"]);
                    });
                }
            });
        }
        return $next($query);
    }
}
