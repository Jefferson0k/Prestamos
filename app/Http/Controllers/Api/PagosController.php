<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Pago\PagosResource;
use App\Models\Pagos;
class PagosController extends Controller{
    public function pagosPorCuota($cuotaId){
        $pagos = Pagos::where('cuota_id', $cuotaId)
            ->orderBy('fecha_pago', 'desc')
            ->get();
        return PagosResource::collection($pagos);
    }
}
