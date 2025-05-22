<?php

namespace App\Http\Resources\Prestamo;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class PrestamoResource extends JsonResource{
    public function toArray($request){
        $estados = [
            1 => 'PAGA',
            2 => 'MOROSO',
            3 => 'PENDIENTE',
            4 => 'FINALIZADO',
        ];
        return [
            'id'                  => $this->id,
            'id_cliente'          => $this->cliente_id,
            'cliente_id'          => $this->cliente_id,
            'dni'                 => $this->cliente->dni,
            'NombreCompleto'      => "{$this->cliente->nombre} {$this->cliente->apellidos}",
            'fecha_inicio'        => Carbon::parse($this->fecha_inicio)->format('d-m-Y h:i:s A'),
            'fecha_vencimiento'   => Carbon::parse($this->fecha_vencimiento)->format('d-m-Y h:i:s A'),
            'finicio'             => $this->fecha_inicio,
            'fvencimiento'        => $this->fecha_vencimiento,
            'capital'             => $this->capital,
            'numero_cuotas'       => $this->numero_cuotas,
            'estado_cliente'      => $estados[$this->estado_cliente] ?? 'DESCONOCIDO',
            'estado'              => $this->estado_cliente,
            'recomendacion'       => $this->recomendacion
                                        ? $this->recomendacion->nombre . ' ' . $this->recomendacion->apellidos . ' (' . $this->recomendacion->dni . ')'
                                        : null,
            'tasa_interes_diario' => $this->tasa_interes_diario,
            'recomendado_id'      => $this->recomendado_id,
        ];
    }
}
