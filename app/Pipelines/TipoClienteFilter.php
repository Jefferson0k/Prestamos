<?php

namespace App\Pipelines;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class TipoClienteFilter{
    protected $tipoClienteId;
    public function __construct($tipoClienteId){
        $this->tipoClienteId = $tipoClienteId;
    }
    public function handle($request, Closure $next): Builder{
        if (!is_null($this->tipoClienteId)) {
            $request->where('tipoCliente_id', $this->tipoClienteId);
        }
        return $next($request);
    }
}
