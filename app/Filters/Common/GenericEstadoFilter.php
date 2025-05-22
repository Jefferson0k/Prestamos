<?php

namespace App\Filters\Common;
use Closure;
class GenericEstadoFilter{
    protected $value;
    protected $column;
    public function __construct($value, $column = 'estado'){
        $this->value = $value;
        $this->column = $column;
    }
    public function handle($request, Closure $next){
        if ($this->value) {
            $value = strtolower(trim($this->value));
            if (in_array($value, ['activo', 'inactivo'])) {
                $request = $request->whereRaw("{$this->column} ILIKE ?", [$value]);
            }
        }
        return $next($request);
    }
}
