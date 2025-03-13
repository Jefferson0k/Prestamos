<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ClienteModelo;
use App\Models\User; // Importa el modelo de usuario

class ClienteTest extends TestCase {
    use RefreshDatabase; // Limpia la BD después de cada test

    public function test_crear_cliente() {
        // Crear un usuario y autenticarlo
        $user = User::factory()->create();
        $this->actingAs($user); // Iniciar sesión como este usuario

        // Datos de prueba
        $data = [
            'dni' => '12345678',
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'correo' => 'juan@example.com',
        ];

        // Enviar la solicitud con autenticación
        $response = $this->postJson('/api/clientes', $data);

        // Asegurar que la respuesta es 201 (creado)
        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Cliente registrado exitosamente',
                     'cliente' => [
                         'dni' => '12345678',
                         'nombre' => 'Juan',
                         'apellidos' => 'Pérez',
                         'correo' => 'juan@example.com',
                     ]
                 ]);

        // Verificar que el cliente fue guardado en la base de datos
        $this->assertDatabaseHas('clientes', [
            'dni' => '12345678',
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'correo' => 'juan@example.com',
        ]);
    }
}
