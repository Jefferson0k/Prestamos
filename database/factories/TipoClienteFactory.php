<?php

namespace Database\Factories;

use App\Models\TipoCliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoClienteFactory extends Factory
{
    protected $model = TipoCliente::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->unique()->word(),
            'estado' => $this->faker->randomElement(['activo', 'inactivo']),
            'usuario_id' => 1,
        ];
    }
}
