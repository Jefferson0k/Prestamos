<?php

namespace Database\Factories;

use App\Models\ClienteModelo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteModeloFactory extends Factory{
    protected $model = ClienteModelo::class;
    public function definition(){
        return [
            'dni' => $this->faker->unique()->numerify('########'),
            'nombre' => $this->faker->firstName,
            'apellidos' => $this->faker->lastName,
            'telefono' => $this->faker->phoneNumber,
            'direccion' => $this->faker->address,
            'correo' => $this->faker->unique()->safeEmail,
            'centro_trabajo' => $this->faker->company,
            'foto' => $this->faker->imageUrl(200, 200, 'people'),
        ];
    }
}
