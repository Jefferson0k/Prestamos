<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TalonarioResource extends JsonResource
{
    public function toArray($request)
    {
        $estados = [
            1 => 'PAGA',
            2 => 'MOROSO',
            3 => 'PENDIENTE',
            4 => 'FINALIZADO',
        ];
        
        return [
            'id' => $this->id,
            'id_cliente' => $this->cliente_id,
            'dni' => $this->cliente->dni,
            'NombreCompleto' => "{$this->cliente->nombre} {$this->cliente->apellidos}",
            'fecha_inicio' => Carbon::parse($this->fecha_inicio)->format('d-m-Y h:i:s A'),
            'fecha_vencimiento' => Carbon::parse($this->fecha_vencimiento)->format('d-m-Y h:i:s A'),
            'capital' => $this->capital,
            'numero_cuotas' => $this->numero_cuotas,
            'estado_cliente' => $estados[$this->estado_cliente] ?? 'DESCONOCIDO',
            'recomendacion' => $this->recomendacion
                ? $this->recomendacion->nombre . ' ' . $this->recomendacion->apellidos . ' (' . $this->recomendacion->dni . ')'
                : null,
            'tasa_interes_diario' => $this->tasa_interes_diario,
            // Agregamos las cuotas al recurso
            'cuotas' => $this->whenLoaded('cuotas', function () {
                return $this->cuotas->map(function ($cuota) {
                    return [
                        'id' => $cuota->id,
                        'numero_cuota' => $cuota->numero_cuota,
                        'capital' => (float) $cuota->capital,
                        'interes' => (float) $cuota->interes,
                        'dias' => $cuota->Dias ?? 0,
                        'tasa_interes_diario' => (float) $cuota->Tasa_Interes_Diario,
                        'monto_interes' => (float) $cuota->Monto_Interes_Pagar,
                        'monto_capital' => (float) $cuota->Monto_Capital_Pagar,
                        'saldo_capital' => (float) $cuota->Saldo_Capital,
                        'fecha_inicio' => Carbon::parse($cuota->fecha_inicio)->format('d-m-Y h:i:s A'),
                        'fecha_vencimiento' => Carbon::parse($cuota->fecha_vencimiento)->format('d-m-Y h:i:s A'),
                        'monto_total' => (float) $cuota->MOnto_Capital_Mas_Interes_a_Pagar,
                        'estado' => $cuota->estado
                    ];
                });
            }),
        ];
    }
}