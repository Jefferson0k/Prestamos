<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ClienteResource extends JsonResource{
    public function toArray($request){
        $prestamo = $this->prestamos()->latest()->first();
        return [
            'id'                  => $this->id,
            'nombres'             => $this->nombre,
            'apellidos'           => $this->apellidos,
            'direccion'           => $this->direccion,
            'centro_trabajo'      => $this->centro_trabajo,
            'celular'             => $this->telefono,
            'dni'                 => $this->dni,
            'fecha_inicio'        => $prestamo ? $prestamo->fecha_inicio : null,
            'fecha_vencimiento'   => $prestamo ? $prestamo->fecha_vencimiento : null,
            'tasa_interes_diario' => $prestamo ? $prestamo->tasa_interes_diario : null,
            'capital_inicial'     => $prestamo ? $prestamo->capital : null,
            'capital_del_mes'     => $prestamo ? $prestamo->pagos->sum('monto_capital_pagar') : 0,
            'capital_actual'      => $prestamo ? $prestamo->pagos->last()?->saldo_capital : 0,
            'interes_actual'      => $prestamo ? $prestamo->pagos->last()?->monto_interes_pagar : 0,
            'interes_total'       => $prestamo ? $prestamo->pagos->sum('monto_interes_pagar') : 0,
            'total'               => $prestamo ? $prestamo->pagos->sum(fn($p) => $p->monto_capital_pagar + $p->monto_interes_pagar) : 0,
            'numero_cuotas'       => $prestamo ? $prestamo->numero_cuotas : null,
            'estado_cliente'      => $prestamo ? $prestamo->estado_cliente : null,
            'recomendacion'      => $prestamo ? $prestamo->recomendacion : null,
            'foto'                => $this->foto ? asset("customers/{$this->foto}") : null,
        ];
    }
}
