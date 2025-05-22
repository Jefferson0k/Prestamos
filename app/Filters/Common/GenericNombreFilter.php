<?php

namespace App\Filters\Common;
use Closure;
class GenericNombreFilter{
    protected $value;
    protected $column;
    public function __construct($value, $column = 'nombre'){
        $this->value = $value;
        $this->column = $column;
    }
    public function handle($request, Closure $next){
        if ($this->value) {
            $request = $request->whereRaw("{$this->column} ILIKE ?", ["%{$this->value}%"]);
        }
        return $next($request);
    }
}
