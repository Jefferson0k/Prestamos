<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->increments('id');

            // cliente_id: No permite borrar cliente si tiene préstamos
            $table->foreignId('cliente_id')
                ->constrained()
                ->onDelete('restrict');

            $table->datetime('fecha_inicio');
            $table->datetime('fecha_vencimiento');
            $table->decimal('capital', 15, 2);
            $table->integer('numero_cuotas');
            $table->integer('estado_cliente');

            // recomendado_id: permite nulos, si se borra el cliente recomendado, se pone null
            $table->foreignId('recomendado_id')
                ->nullable()
                ->constrained('clientes')
                ->onDelete('set null');

            $table->decimal('tasa_interes_diario', 5, 2);
            $table->decimal('monto_total', 15, 2)->comment('Capital más intereses totales');
            $table->string('referencia')->unique()->nullable();

            // usuario_id: ya estaba bien con set null
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('prestamos');
    }
};
