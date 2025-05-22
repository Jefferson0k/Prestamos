<?php

namespace App\Services;

use App\Models\Cuotas;
use App\Models\Pagos;
use App\Models\Prestamos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PagoService{
    public function __construct(
        protected InteresCalculatorService $interesCalculator
    ) {}
    
    public function registrarPago(
        int $cuotaId,
        float $montoCapitalPagado,
        ?string $fechaPago = null,
        ?int $dias = null
    ): bool {
        DB::beginTransaction();

        try {
            $cuota = Cuotas::findOrFail($cuotaId);
            $prestamo = $cuota->prestamo;

            $esEstadoActivo = (bool) ($cuota->state ?? true);
            $fechaPagoCarbon = $fechaPago ? Carbon::parse($fechaPago)->startOfDay() : now()->startOfDay();
            $fechaInicio = $cuota->fecha_inicio ? Carbon::parse($cuota->fecha_inicio)->startOfDay() : null;

            if (!$fechaInicio) {
                throw new \Exception("La cuota aún no está activa. No tiene fecha_inicio.");
            }

            $dias = $dias ?? $this->interesCalculator->calcularDias($fechaInicio, $fechaPagoCarbon);
            
            $capital = (float) $cuota->capital;
            $tasaInteresDiario = (float) $cuota->tasa_interes_diario;
            $montoCapitalPagado = min((float) $montoCapitalPagado, $capital);
            $esPagoCompleto = $montoCapitalPagado >= $capital;

            // **CAMBIO IMPORTANTE**: Pasar el montoCapitalPagado al cálculo de intereses
            $datosInteres = $this->interesCalculator->calcularInteres(
                $capital, 
                $tasaInteresDiario, 
                $dias, 
                true, 
                $esPagoCompleto,
                $montoCapitalPagado  // ← Nuevo parámetro
            );
            
            $saldoCapital = $capital - $montoCapitalPagado;
            $montoTotalPagar = $datosInteres['monto_interes_pagar'] + $montoCapitalPagado;
            
            // Redondear el monto total a pagar
            $montoTotalPagar = (float) ceil($montoTotalPagar);

            $estado = $saldoCapital > 0 ? 'Parcial' : 'Pagado';

            $this->actualizarCuota(
                $cuota,
                $esEstadoActivo,
                $fechaPagoCarbon,
                $dias, // Usar días reales, no ajustados
                $datosInteres,
                $montoCapitalPagado,
                $saldoCapital,
                $montoTotalPagar,
                $estado
            );

            $this->registrarPagoDB(
                $prestamo->id,
                $cuota->id,
                $capital,
                $fechaPagoCarbon,
                $montoCapitalPagado,
                $datosInteres['monto_interes_pagar'],
                $montoTotalPagar,
                $datosInteres['interes_reducido'] ?? false
            );

            if ($esPagoCompleto) {
                $this->cancelarCuotasRestantes($prestamo->id, $cuota->numero_cuota);
            } else {
                $this->actualizarCuotasPendientes(
                    $prestamo->id,
                    $cuota->numero_cuota,
                    $saldoCapital,
                    $fechaInicio,
                    $fechaPagoCarbon,
                    $tasaInteresDiario,
                    $datosInteres['interes_reducido'] ?? false,
                    $dias
                );
            }

            $this->actualizarEstadoPrestamo($prestamo->id);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error al registrar pago: {$e->getMessage()}", [
                'cuota_id' => $cuotaId,
                'usuario_id' => Auth::id(),
                'datos_interes' => $datosInteres ?? null,
            ]);
            throw $e;
        }
    }
    
    /**
     * Ajusta los días según la regla de negocio para cálculo de intereses
     * - Si días <= 15, se mantienen los días reales
     * - Si 15 < días < 30, se cobran como 30 días
     * - Si días >= 30, se usan los días reales
     */
    protected function ajustarDiasSegunRegla(int $dias): int 
    {
        if ($dias > 15 && $dias < 30) {
            return 30;
        }
        return $dias;
    }
    
    protected function actualizarCuota(
        Cuotas $cuota,
        bool $estadoActivo,
        Carbon $fechaPago,
        int $dias,
        array $datosInteres,
        float $montoCapitalPagado,
        float $saldoCapital,
        float $montoTotalPagar,
        string $estado
    ): void {
        $camposComunes = [
            'fecha_vencimiento' => $fechaPago,
            'dias' => $dias,
            'interes' => $datosInteres['interes'],
            'monto_capital_pagar' => $montoCapitalPagado,
            'saldo_capital' => $saldoCapital,
            'estado' => $estado,
            'usuario_id' => Auth::id(),
        ];

        if ($estadoActivo) {
            $camposComunes += [
                'monto_interes_pagar' => $datosInteres['monto_interes_pagar'],
                'monto_capital_mas_interes_a_pagar' => $montoTotalPagar,
            ];
        } else {
            $camposComunes += [
                'monto_capital_mas_interes_a_pagar' => ($cuota->monto_capital_mas_interes_a_pagar ?? 0) + $montoCapitalPagado,
            ];
        }

        $cuota->update($camposComunes);
    }

    protected function registrarPagoDB(
        int $prestamoId,
        int $cuotaId,
        float $capital,
        Carbon $fechaPago,
        float $montoCapital,
        float $montoInteres,
        float $montoTotal,
        bool $interesReducido
    ): void {
        // Obtener el último ID de pago (opcional: con fallback)
        $ultimoId = Pagos::max('id') ?? 0;
        $nuevoNumero = $ultimoId + 1;

        $referencia = 'REC-' . now()->format('Ymd') . '-' . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);

        Pagos::create([
            'prestamo_id'     => $prestamoId,
            'cuota_id'        => $cuotaId,
            'capital'         => $capital,
            'fecha_pago'      => $fechaPago,
            'monto_capital'   => $montoCapital,
            'monto_interes'   => $montoInteres,
            'monto_total'     => $montoTotal,
            'usuario_id'      => Auth::id(),
            'interes_reducido'=> $interesReducido,
            'referencia'      => $referencia, // ← aquí se guarda el número de recibo
        ]);
    }
    
    protected function cancelarCuotasRestantes(int $prestamoId, int $numeroCuota): void{
        Cuotas::where('prestamo_id', $prestamoId)
            ->where('numero_cuota', '>', $numeroCuota)
            ->update([
                'estado' => 'Cancelado',
                'fecha_inicio' => null,
                'capital' => 0,
                'saldo_capital' => 0,
            ]);
    }
    
    protected function actualizarCuotasPendientes(
        int $prestamoId,
        int $numeroCuota,
        float $saldoCapital,
        Carbon $fechaInicio,
        Carbon $fechaPago,
        float $tasaInteresDiario,
        bool $interesReducido,
        int $diasReales
    ): void {
        $siguienteNumero = $numeroCuota + 1;
        $cuotasPendientes = Cuotas::where('prestamo_id', $prestamoId)
            ->where('numero_cuota', '>=', $siguienteNumero)
            ->where('estado', 'Pendiente')
            ->orderBy('numero_cuota')
            ->get();

        // Determinar la nueva fecha de inicio para la siguiente cuota según las reglas
        $nuevaFechaInicio = $this->determinarNuevaFechaInicio($fechaInicio, $fechaPago, $diasReales);
        
        foreach ($cuotasPendientes as $index => $cuota) {
            if ($index === 0) {
                $cuota->update([
                    'capital' => $saldoCapital,
                    'fecha_inicio' => $nuevaFechaInicio,
                    'saldo_capital' => $saldoCapital,
                ]);
            } else {
                $cuotaAnterior = $cuotasPendientes[$index - 1];
                $cuota->update([
                    'capital' => $cuotaAnterior->capital,
                    'saldo_capital' => $cuotaAnterior->capital,
                ]);
            }
        }

        if ($cuotasPendientes->isEmpty() && $saldoCapital > 0) {
            $datosInteres = $this->interesCalculator->calcularInteres($saldoCapital, $tasaInteresDiario, 0, true, false);

            Cuotas::create([
                'prestamo_id' => $prestamoId,
                'numero_cuota' => $siguienteNumero,
                'capital' => $saldoCapital,
                'fecha_inicio' => $nuevaFechaInicio,
                'dias' => 0,
                'interes' => $datosInteres['interes'],
                'tasa_interes_diario' => $tasaInteresDiario,
                'monto_interes_pagar' => (float) ceil($datosInteres['monto_interes_pagar']),
                'saldo_capital' => $saldoCapital,
                'monto_capital_mas_interes_a_pagar' => (float) ceil($saldoCapital + $datosInteres['monto_interes_pagar']),
                'estado' => 'Pendiente',
                'state' => true,
            ]);

            Prestamos::where('id', $prestamoId)->update([
                'numero_cuotas' => Cuotas::where('prestamo_id', $prestamoId)->count()
            ]);
        }
    }
    
    /**
     * Determina la nueva fecha de inicio según las reglas de negocio
     * - Si días <= 15, se mantiene la fecha de inicio original
     * - Si días > 15, se usa la fecha de pago como nueva fecha de inicio
     */
    protected function determinarNuevaFechaInicio(Carbon $fechaInicio, Carbon $fechaPago, int $diasReales): Carbon 
    {
        // Si son 15 días o menos desde la fecha de inicio, mantenemos la fecha de inicio original
        if ($diasReales <= 15) {
            return $fechaInicio;
        }
        
        // Si son más de 15 días, usamos la fecha de pago como nueva fecha de inicio
        return $fechaPago;
    }
    
    protected function actualizarEstadoPrestamo(int $prestamoId): bool{
        try {
            $ultimoPago = Pagos::where('prestamo_id', $prestamoId)
                ->latest('id')
                ->first();

            if ($ultimoPago) {
                $cuota = Cuotas::find($ultimoPago->cuota_id);
                if ($cuota && $cuota->estado === 'Pagado') {
                    Prestamos::where('id', $prestamoId)->update(['estado_cliente' => 4]);
                    return true;
                }
            }

            return false;
        } catch (\Throwable $e) {
            Log::error("Error al actualizar estado del préstamo: " . $e->getMessage());
            return false;
        }
    }
}