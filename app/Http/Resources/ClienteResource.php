<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
class ClienteResource extends JsonResource{
    public function toArray($request){
        $prestamo = $this->whenLoaded('prestamos')->first();
        
        // Valores por defecto
        $capitalDelMes = 0;
        $capitalActual = 0;
        $interesActual = 0;
        $interesTotal = 0;
        $total = 0;
        
        // Solo procesamos pagos si el préstamo existe y los pagos están cargados
        if ($prestamo && $prestamo->relationLoaded('pagos')) {
            $pagos = $prestamo->pagos;
            
            if ($pagos->isNotEmpty()) {
                $capitalDelMes = $pagos->sum('monto_capital');
                $capitalActual = $pagos->last()->saldo_capital ?? 0;
                $interesActual = $pagos->last()->monto_interes ?? 0;
                $interesTotal = $pagos->sum('monto_interes');
                
                // Calculamos la suma total una sola vez
                $total = $pagos->reduce(function ($carry, $pago) {
                    return $carry + ($pago->monto_capital + $pago->monto_interes);
                }, 0);
            }
        }
        
        return [
            'id' => $this->id,
            'nombre_completo' => $this->nombre . ' ' . $this->apellidos,
            'direccion' => $this->direccion,
            'centro_trabajo' => $this->centro_trabajo,
            'celular' => $this->telefono,
            'dni' => $this->dni,
            'fecha_inicio' => $prestamo && $prestamo->fecha_inicio
                ? Carbon::parse($prestamo->fecha_inicio)->format('d-m-Y H:i:s A')
                : null,
            'fecha_vencimiento' => $prestamo && $prestamo->fecha_vencimiento
                ? Carbon::parse($prestamo->fecha_vencimiento)->format('d-m-Y H:i:s A')
                : null,    
            'tasa_interes_diario' => $prestamo ? $prestamo->tasa_interes_diario : null,
            'capital_inicial' => $prestamo ? $prestamo->capital : null,
            'capital_del_mes' => $capitalDelMes,
            'capital_actual' => $capitalActual,
            'interes_actual' => $interesActual,
            'interes_total' => $interesTotal,
            'total' => $total,
            'numero_cuotas' => $prestamo ? $prestamo->numero_cuotas : null,
            'estado_cliente' => $prestamo ? $prestamo->estado_cliente : null,
            'recomendacion' => $prestamo && $prestamo->recomendacion
            ? $prestamo->recomendacion->nombre . ' ' . $prestamo->recomendacion->apellidos . ' (' . $prestamo->recomendacion->dni . ')'
            : null,

            'fecha_Inicio_pago_mes' => $prestamo && $prestamo->fecha_inicio
            ? Carbon::parse($prestamo->fecha_inicio)->format('d-m-Y H:i:s A')
            : null,
            'fecha_vencimiento_pago_mes' => '00-00-0000',
            'Interes_total' => $interesTotal, // Nota: parece duplicado con 'interes_total'
            'foto' => $this->foto
                ? asset("customers/{$this->foto}")
                : asset("customers/SinFoto.jpg"),
        ];
    }
}