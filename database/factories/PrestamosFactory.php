<?php

namespace Database\Factories;

use App\Models\Prestamos;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PrestamosFactory extends Factory{
    protected $model = Prestamos::class;
    public function definition(){
        $cliente = Cliente::inRandomOrder()->first() ?? Cliente::factory()->create();

        $recomendado = Cliente::where('id', '!=', $cliente->id)->inRandomOrder()->first() ?? Cliente::factory()->create();

        $fechaInicio = $this->faker->dateTimeBetween('-1 years', 'now');
        $numeroCuotas = $this->faker->numberBetween(6, 36);
        $capital = $this->faker->randomFloat(2, 500, 10000);
        $tasaInteresDiario = $this->faker->randomFloat(4, 0.0002, 0.001);
        $diasTotales = $numeroCuotas * 30;
        $interesTotal = $capital * $tasaInteresDiario * $diasTotales;
        $montoTotal = $capital + $interesTotal;

        return [
            'cliente_id' => $cliente->id,
            'fecha_inicio' => $fechaInicio,
            'fecha_vencimiento' => Carbon::instance($fechaInicio)->addMonths($numeroCuotas),
            'capital' => $capital,
            'numero_cuotas' => $numeroCuotas,
            'estado_cliente' => 1,
            'recomendado_id' => $recomendado->id,
            'tasa_interes_diario' => $tasaInteresDiario,
            'monto_total' => $montoTotal,
        ];
    }
}
