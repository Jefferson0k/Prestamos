<?php

namespace App\Http\Resources\Cliente;
use App\Services\InteresCalculatorService;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource{
    public function toArray($request){
        $interesCalculator = new InteresCalculatorService();
        
        return [
            'id' => $this->id,
            'nombre_completo' => $this->nombre . ' ' . $this->apellidos,
            'direccion' => $this->direccion,
            'centro_trabajo' => $this->centro_trabajo,
            'celular' => $this->telefono,
            'dni' => $this->dni,
            'tipoCliente' => $this->tipoCliente_id,
            'nomTipoCliente' => $this->tipoCliente->nombre,
            'foto' => $this->foto
                ? asset("customers/{$this->foto}")
                : asset("customers/SinFoto.jpg"),

                'prestamos' => $this->whenLoaded('prestamos', function() use ($interesCalculator) {
                return $this->prestamos->map(function($prestamo) use ($interesCalculator) {
                    $cuotasActivas = $prestamo->cuotas->filter(function($cuota) {
                        return !empty($cuota->fecha_inicio) && empty($cuota->fecha_vencimiento);
                    });
                    
                    return [
                        'id' => $prestamo->id,
                        'fecha_inicio' => Carbon::parse($prestamo->fecha_inicio)->format('d-m-Y h:i:s A'),
                        'fecha_vencimiento' => $prestamo->fecha_vencimiento 
                            ? Carbon::parse($prestamo->fecha_vencimiento)->format('d-m-Y h:i:s A') 
                            : '00-00-0000',
                        'capital' => $prestamo->capital,
                        'tasa_interes' => $prestamo->tasa_interes_diario,
                        'Estado' => $prestamo->estado_cliente,
                        'numero_cuotas' => $prestamo->numero_cuotas,
                        'recomendacion' => $prestamo->recomendacion 
                            ? $prestamo->recomendacion->nombre.' '. $prestamo->recomendacion->apellidos.' '.$prestamo->recomendacion->dni
                            : 'Sin recomendación',
                        'cuotas' => $cuotasActivas->map(function($cuota) use ($prestamo, $interesCalculator) {
                            $dias = $interesCalculator->calcularDias($cuota->fecha_inicio);                            
                            $pagoCompleto = ($cuota->monto_capital_pagar == $cuota->capital);
                            $datosInteres = $interesCalculator->calcularInteres(
                                $cuota->capital, 
                                $prestamo->tasa_interes_diario, 
                                $dias, 
                                true, 
                                $pagoCompleto
                            );                            
                            $datosPago = $interesCalculator->calcularPago(
                                $cuota->capital,
                                $cuota->monto_capital_pagar ?? 0,
                                $datosInteres
                            );                            
                            $estado = $interesCalculator->determinarEstado($cuota->estado, $dias);
                            $totalCuotas = $prestamo->cuotas->sum('monto_capital_mas_interes_a_pagar');
                            $totalInteres = $prestamo->cuotas->sum('monto_interes_pagar');

                            return [
                                'id' => $cuota->id,
                                'numero_cuota' => $cuota->numero_cuota,
                                'fecha_inicio' => Carbon::parse($cuota->fecha_inicio)->format('d-m-Y'),
                                'fecha_vencimientos' => '00-00-0000',
                                'capital_actual' => $cuota->capital,
                                'interes' => $cuota->interes,
                                'dias_transcurridos' => $dias,
                                'dias_calculados' => $datosInteres['dias_calculados'],
                                'monto_interes_pagar' => round($datosInteres['monto_interes_pagar'], 2),
                                'interes_actual' => round($datosInteres['monto_interes_pagar'], 2),
                                'monto_capital_pagar' => $cuota->monto_capital_pagar ?? 0,
                                'saldo_capital' => $datosPago['saldo_capital'],
                                'monto_total_pagar' => round($datosPago['monto_total_pagar'], 2),
                                'estado' => $estado,
                                'interes_totales' => round($totalInteres + ($datosInteres['monto_interes_pagar'] ?? 0),2),
                                'totales' => round($totalCuotas + ($datosInteres['monto_interes_pagar'] ?? 0),2),
                            ];
                        }),
                    ];
                });
            }, []),
        ];
    }
}