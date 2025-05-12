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
            
            // Determinar si es un pago completo
            $esPagoCompleto = ($montoCapitalPagado >= $capital);
            
            // Calculamos el interés aplicando las reglas de negocio, indicando si es pago completo
            $datosInteres = $this->interesCalculator->calcularInteres($capital, $tasaInteresDiario, $dias, true, $esPagoCompleto);
            
            // Calculamos el pago
            $saldoCapital = $capital - $montoCapitalPagado;
            $montoTotalPagar = $datosInteres['monto_interes_pagar'] + $montoCapitalPagado;
            $estado = $saldoCapital > 0 ? 'Parcial' : 'Pagado';

            // Actualizamos la cuota
            $cuota->update([
                'fecha_vencimiento' => $hoy,
                'dias' => $dias, // Guardamos los días reales calculados con +1
                'interes' => $datosInteres['interes'], // Guardamos el interés calculado
                'monto_interes_pagar' => $datosInteres['monto_interes_pagar'], // Monto de interés a pagar
                'monto_capital_pagar' => $montoCapitalPagado,
                'saldo_capital' => $saldoCapital,
                'monto_capital_mas_interes_a_pagar' => $montoTotalPagar, // Monto total a pagar
                'estado' => $estado,
                'usuario_id' => Auth::id(),
            ]);

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
            $prestamo = Cuotas::where('prestamo_id', $prestamoId)->first()->prestamo;
            $cuotasConSaldo = Cuotas::where('prestamo_id', $prestamoId)
                ->where('saldo_capital', '>', 0)
                ->count();

            Log::info("Verificando estado préstamo {$prestamoId}: Cuotas con saldo > 0 = {$cuotasConSaldo}");

            if ($cuotasConSaldo === 0) {
                Cuotas::where('prestamo_id', $prestamoId)
                    ->where('estado', 'Parcial')
                    ->where('saldo_capital', '<=', 0)
                    ->update(['estado' => 'Pagado']);
                $prestamo->update([
                    'estado_cliente' => 4
                ]);

                Log::info("Préstamo {$prestamoId} marcado como pagado completamente (estado_cliente=4)");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Error al actualizar estado del préstamo: " . $e->getMessage());
            return false;
        }
    }
}