<?php

namespace App\Services;

use App\Models\Cuotas;
use App\Models\Prestamos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PrestamoService{
    public function crearPrestamo(array $data){
        $prestamo = Prestamos::create([
            'cliente_id' => $data['cliente_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_vencimiento' => $data['fecha_vencimiento'],
            'capital' => $data['capital'],
            'numero_cuotas' => $data['numero_cuotas'],
            'estado_cliente' => $data['estado_cliente'] ?? 1,
            'recomendado_id' => $data['recomendado_id'],
            'tasa_interes_diario' => $data['tasa_interes_diario'],
            'monto_total' => $data['capital'],
            'usuario_id' => Auth::id(),
        ]);

        $referencia = 'PREST-' . Carbon::now()->format('Ymd') . '-' . str_pad($prestamo->id, 4, '0', STR_PAD_LEFT);
        $prestamo->referencia = $referencia;
        $prestamo->save();

        $this->generarCuotas($prestamo);
        return $prestamo;
    }
    public function generarCuotas(Prestamos $prestamo){
        $fechaInicio = Carbon::parse($prestamo->fecha_inicio);
        $capital = $prestamo->capital;
        $usuarioId = Auth::id();
        $referenciaPrestamo = $prestamo->referencia;

        for ($i = 1; $i <= $prestamo->numero_cuotas; $i++) {
            Cuotas::create([
                'prestamo_id' => $prestamo->id,
                'numero_cuota' => $i,
                'capital' => $capital,
                'interes' => 0.00,
                'dias' => 0,
                'tasa_interes_diario' => $prestamo->tasa_interes_diario,
                'monto_interes_pagar' => 0.00,
                'monto_capital_pagar' => null,
                'saldo_capital' => $capital,
                'fecha_inicio' => $i == 1 ? $fechaInicio : null,
                'fecha_vencimiento' => null,
                'monto_capital_mas_interes_a_pagar' => 0.00,
                'estado' => 'Pendiente',
                'usuario_id' => $usuarioId,
                'referencia' => $referenciaPrestamo . '-CUOTA-' . str_pad($i, 2, '0', STR_PAD_LEFT),
            ]);
        }
    }
    public function actualizarPrestamo(Prestamos $prestamo, array $data){
        $numeroCuotasAnterior = $prestamo->numero_cuotas;
        $capitalAnterior = $prestamo->capital;

        $prestamo->fill([
            'fecha_inicio' => $data['fecha_inicio'] ?? $prestamo->fecha_inicio,
            'fecha_vencimiento' => $data['fecha_vencimiento'] ?? $prestamo->fecha_vencimiento,
            'capital' => $data['capital'] ?? $prestamo->capital,
            'numero_cuotas' => $data['numero_cuotas'] ?? $prestamo->numero_cuotas,
            'estado_cliente' => $data['estado_cliente'] ?? $prestamo->estado_cliente,
            'recomendado_id' => $data['recomendado_id'] ?? $prestamo->recomendado_id,
            'tasa_interes_diario' => $data['tasa_interes_diario'] ?? $prestamo->tasa_interes_diario,
            'monto_total' => $data['capital'] ?? $prestamo->capital,
        ]);

        $prestamo->save();
        $this->actualizarCuotas($prestamo, $numeroCuotasAnterior, $capitalAnterior);
        return $prestamo;
    }

    public function actualizarCuotas(Prestamos $prestamo, int $numeroCuotasAnterior, float $capitalAnterior = null){
        $cuotasExistentes = Cuotas::where('prestamo_id', $prestamo->id)->orderBy('numero_cuota')->get();
        
        $capitalRestante = $prestamo->capital;
        $capitalOriginal = $prestamo->capital;
        
        foreach ($cuotasExistentes as $cuota) {
            if ($cuota->estado == 'Pagada' || $cuota->estado == 'Parcial') {
                $capitalPagado = $cuota->capital - $cuota->saldo_capital;
                $capitalRestante -= $capitalPagado;
            }
        }

        if ($prestamo->numero_cuotas > $numeroCuotasAnterior) {
            for ($i = $numeroCuotasAnterior + 1; $i <= $prestamo->numero_cuotas; $i++) {
                Cuotas::create([
                    'prestamo_id' => $prestamo->id,
                    'numero_cuota' => $i,
                    'capital' => $capitalOriginal,
                    'interes' => 0.00,
                    'dias' => 0,
                    'tasa_interes_diario' => $prestamo->tasa_interes_diario,
                    'monto_interes_pagar' => 0.00,
                    'monto_capital_pagar' => null,
                    'saldo_capital' => $capitalRestante,
                    'fecha_inicio' => null,
                    'fecha_vencimiento' => null,
                    'monto_capital_mas_interes_a_pagar' => 0.00,
                    'estado' => 'Pendiente',
                    'usuario_id' => Auth::id(),
                    'referencia' => $prestamo->referencia . '-CUOTA-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                ]);
            }
        } elseif ($prestamo->numero_cuotas < $numeroCuotasAnterior) {
            $cuotasPagadas = $cuotasExistentes->where('estado', '!=', 'Pendiente')
                ->where('numero_cuota', '>', $prestamo->numero_cuotas)
                ->count();

            if ($cuotasPagadas > 0) {
                throw new \Exception('No se pueden eliminar cuotas que ya han sido pagadas');
            }

            Cuotas::where('prestamo_id', $prestamo->id)
                ->where('numero_cuota', '>', $prestamo->numero_cuotas)
                ->delete();
        }
        foreach ($cuotasExistentes as $cuota) {
            if ($cuota->numero_cuota <= $prestamo->numero_cuotas) {
                $cuotaModel = Cuotas::find($cuota->id);
                if ($cuotaModel) {
                    $estado = $cuotaModel->estado;
                    $saldoCapital = $cuotaModel->saldo_capital;
                    if ($estado == 'Pendiente') {
                        $saldoCapital = $capitalRestante;
                    }
                    $cuotaModel->capital = $capitalOriginal;
                    $cuotaModel->tasa_interes_diario = $prestamo->tasa_interes_diario;
                    $cuotaModel->saldo_capital = $saldoCapital;
                    $cuotaModel->estado = $estado;
                    $cuotaModel->save();
                }
            }
        }
        $primeraCuota = Cuotas::where('prestamo_id', $prestamo->id)
            ->where('numero_cuota', 1)
            ->first();

        if ($primeraCuota) {
            $primeraCuota->fecha_inicio = $prestamo->fecha_inicio;
            $primeraCuota->save();
        }
    }
}