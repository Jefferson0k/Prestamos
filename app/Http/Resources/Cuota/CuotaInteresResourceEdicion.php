<?php

namespace App\Http\Resources\Cuota;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CuotaInteresResourceEdicion extends JsonResource{
    public function toArray(Request $request): array{
        return [
            'id' => $this->id,
            'monto_capital_pagar' => $this->monto_capital_pagar,
            'monto_interes_pagar' => $this->monto_interes_pagar,
            'capital_interes' =>$this->monto_capital_mas_interes_a_pagar ,
            'state' => $this->state,
        ];
    }
}