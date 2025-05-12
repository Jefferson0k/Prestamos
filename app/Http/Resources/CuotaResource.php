<?php
namespace App\Http\Resources;
use App\Services\InteresCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class CuotaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Obtener la instancia del calculador de interés
        $calculator = App::make(InteresCalculatorService::class);
        $fechaInicio = $this->fecha_inicio ? Carbon::parse($this->fecha_inicio)->startOfDay() : null;
        $hoy = Carbon::now()->startOfDay();
        
        // Calcular días transcurridos (incluyendo el día actual)
        $dias = $calculator->calcularDias($fechaInicio, $hoy);
        $capital = floatval($this->capital ?? 0);
        $tasaInteresDiario = floatval($this->tasa_interes_diario ?? 0);
        
        // Obtener el monto a pagar desde la solicitud si está disponible
        $montoCapitalPagar = floatval($this->monto_capital_pagar ?? 0);
        
        // Determinar si es un pago completo (para visualización)
        $esPagoCompleto = ($request->input('pago_completo', false) || $montoCapitalPagar >= $capital);
        
        // Calcular el interés aplicando las reglas de negocio, incluyendo la lógica de pago completo
        $datosInteres = $calculator->calcularInteres($capital, $tasaInteresDiario, $dias, true, $esPagoCompleto);
        
        $saldoCapital = $capital - $montoCapitalPagar;
        $montoTotalPagar = $datosInteres['monto_interes_pagar'] + $montoCapitalPagar;
        
        // Determinar el estado basado en los días transcurridos
        $estado = $dias > 30 ? 'Vencido' : $this->estado;
        
        return [
            'id' => $this->id,
            'numero_cuota' => 'MES ' . $this->numero_cuota,
            'capital' => number_format($capital, 2),
            'interes' => number_format($datosInteres['interes'], 2),
            'dias' => $dias,
            'dias_calculados' => $datosInteres['dias_calculados'], // Añadido para mostrar los días utilizados en el cálculo
            'tasa_interes_diario' => number_format($tasaInteresDiario, 2),
            'monto_interes_pagar' => number_format($datosInteres['monto_interes_pagar'], 2),
            'monto_capital_pagar' => $montoCapitalPagar,
            'saldo_capital' => number_format($saldoCapital, 2),
            'fecha_inicio' => $fechaInicio ? $fechaInicio->format('d-m-Y') : '00-00-0000',
            'fecha_vencimiento' => $montoCapitalPagar > 0
                ? Carbon::now()->format('d-m-Y')
                : ($this->fecha_vencimiento
                    ? Carbon::parse($this->fecha_vencimiento)->format('d-m-Y')
                    : '00-00-0000'),
            'monto_total_pagar' => number_format($montoTotalPagar, 2),
            'estado' => $estado,
        ];
    }
}