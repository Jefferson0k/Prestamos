<?php

namespace App\Services;

use App\Models\Cuotas;
use App\Models\Pagos;
use App\Models\Prestamos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PagoService{
    protected $interesCalculator;
    
    public function __construct(InteresCalculatorService $interesCalculator) {
        $this->interesCalculator = $interesCalculator;
    }
    
    public function registrarPago($cuotaId, $montoCapitalPagado){
        try {
            $cuota = Cuotas::findOrFail($cuotaId);
            $prestamo = $cuota->prestamo;
            $hoy = Carbon::now()->startOfDay();

            $fechaInicio = $cuota->fecha_inicio ? Carbon::parse($cuota->fecha_inicio)->startOfDay() : null;
            if (!$fechaInicio) {
                throw new \Exception("La cuota aún no está activa. No tiene Fecha_Inicio.");
            }

            // Usando la misma lógica exacta que en CuotaResource
            $dias = $this->interesCalculator->calcularDias($fechaInicio, $hoy);
            $capital = floatval($cuota->capital ?? 0);
            $tasaInteresDiario = floatval($cuota->tasa_interes_diario ?? 0);
            
            $montoCapitalPagado = floatval($montoCapitalPagado);
            if ($montoCapitalPagado > $capital) {
                throw new \Exception("El monto de pago excede el capital pendiente.");
            }
            
            // Determinar si es un pago completo del capital de esta cuota
            $esPagoCompleto = ($montoCapitalPagado >= $capital);
            
            // Calculamos el interés aplicando las reglas de negocio, indicando si es pago completo
            $datosInteres = $this->interesCalculator->calcularInteres($capital, $tasaInteresDiario, $dias, true, $esPagoCompleto);
            
            // Calculamos el pago y actualizamos el saldo capital
            $saldoCapital = $capital - $montoCapitalPagado;
            $montoTotalPagar = $datosInteres['monto_interes_pagar'] + $montoCapitalPagado;
            
            // Establecer el estado de la cuota basado en el saldo capital
            $estado = $saldoCapital > 0 ? 'Parcial' : 'Pagado';
            
            // Actualizamos la cuota
            $cuota->update([
                'fecha_vencimiento' => $hoy,
                'dias' => $dias,
                'interes' => $datosInteres['interes'],
                'monto_interes_pagar' => $datosInteres['monto_interes_pagar'],
                'monto_capital_pagar' => $montoCapitalPagado,
                'saldo_capital' => $saldoCapital,
                'monto_capital_mas_interes_a_pagar' => $montoTotalPagar,
                'estado' => $estado,
                'usuario_id' => Auth::id(),
            ]);
            
            // Registrar para debugging
            Log::info("Cuota {$cuotaId} actualizada: Capital={$capital}, MontoCapitalPagado={$montoCapitalPagado}, SaldoCapital={$saldoCapital}, Estado={$estado}");

            // Creamos el registro de pago
            Pagos::create([
                'prestamo_id' => $prestamo->id,
                'cuota_id' => $cuota->id,
                'capital' => $capital,
                'fecha_pago' => $hoy,
                'monto_capital' => $montoCapitalPagado,
                'monto_interes' => $datosInteres['monto_interes_pagar'],
                'monto_total' => $montoTotalPagar,
                'usuario_id' => Auth::id(),
            ]);

            // Procesamos las cuotas restantes
            if ($esPagoCompleto) {
                $this->cancelarCuotasRestantes($prestamo->id, $cuota->numero_cuota);
            } else {
                $this->actualizarCuotasPendientes($prestamo->id, $cuota->numero_cuota, $saldoCapital, $hoy, $tasaInteresDiario);
            }

            // Verificar el estado del préstamo después de cada pago
            // Pasamos el ID de la cuota que acaba de ser pagada
            $this->actualizarEstadoPrestamo($prestamo->id);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al registrar pago: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function cancelarCuotasRestantes($prestamoId, $numeroCuota){
        Cuotas::where('prestamo_id', $prestamoId)
            ->where('numero_cuota', '>', $numeroCuota)
            ->update([
                'estado' => 'Cancelado',
                'fecha_inicio' => null,
                'capital' => 0,
                'saldo_capital' => 0,
            ]);
    }
    
    private function actualizarCuotasPendientes($prestamoId, $numeroCuota, $saldoCapital, $fechaInicio, $tasaInteresDiario){
        $siguienteNumero = $numeroCuota + 1;
        $cuotasPendientes = Cuotas::where('prestamo_id', $prestamoId)
            ->where('numero_cuota', '>=', $siguienteNumero)
            ->where('estado', 'Pendiente')
            ->orderBy('numero_cuota')
            ->get();

        foreach ($cuotasPendientes as $index => $cuotaPendiente) {
            if ($index === 0) {
                $cuotaPendiente->update([
                    'capital' => $saldoCapital,
                    'fecha_inicio' => $fechaInicio,
                    'saldo_capital' => $saldoCapital,
                ]);
            } else {
                $cuotaAnterior = $cuotasPendientes[$index - 1];
                $nuevoCapital = $cuotaAnterior->capital;
                $cuotaPendiente->update([
                    'capital' => $nuevoCapital,
                    'saldo_capital' => $nuevoCapital,
                ]);
            }
        }

        if ($cuotasPendientes->isEmpty() && $saldoCapital > 0) {
            $dias = $this->interesCalculator->calcularDias($fechaInicio, Carbon::now()->startOfDay());
            $datosInteres = $this->interesCalculator->calcularInteres($saldoCapital, $tasaInteresDiario, $dias, true, false);

            Cuotas::create([
                'prestamo_id' => $prestamoId,
                'numero_cuota' => $siguienteNumero,
                'capital' => $saldoCapital,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => null,
                'dias' => $dias,
                'interes' => $datosInteres['interes'],
                'tasa_interes_diario' => $tasaInteresDiario,
                'monto_interes_pagar' => $datosInteres['monto_interes_pagar'],
                'monto_capital_pagar' => null,
                'saldo_capital' => $saldoCapital,
                'monto_capital_mas_interes_a_pagar' => $saldoCapital + $datosInteres['monto_interes_pagar'],
                'estado' => 'Pendiente'
            ]);

            $prestamo = Prestamos::find($prestamoId);
            $prestamo->numero_cuotas = Cuotas::where('prestamo_id', $prestamoId)->count();
            $prestamo->save();
        }
    }
    
    private function actualizarEstadoPrestamo($prestamoId){
        try {
            // No necesitamos verificar todas las cuotas, solo la cuota actual
            // El estado de la cuota ya se ha actualizado en registrarPago()
            // antes de llamar a esta función
            
            // Obtenemos el préstamo
            $prestamo = Prestamos::findOrFail($prestamoId);
            
            // Verificamos si la última cuota actualizada está en estado "Pagado"
            // Esto lo podemos hacer consultando la última actualización de pago
            $ultimoPago = Pagos::where('prestamo_id', $prestamoId)
                ->orderBy('id', 'desc')
                ->first();
            
            if ($ultimoPago) {
                $cuotaPagada = Cuotas::find($ultimoPago->cuota_id);
                
                // Si la cuota está pagada completamente, actualizamos el estado del préstamo
                if ($cuotaPagada && $cuotaPagada->estado === 'Pagado') {
                    $prestamo->update([
                        'estado_cliente' => 4
                    ]);
                    
                    Log::info("Préstamo {$prestamoId} marcado como pagado (estado_cliente=4) porque la cuota {$cuotaPagada->id} está completamente pagada");
                    return true;
                } else {
                    Log::info("Préstamo {$prestamoId} no se actualiza porque la cuota procesada no está completamente pagada");
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Error al actualizar estado del préstamo: " . $e->getMessage());
            return false;
        }
    }
}