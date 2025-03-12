<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id');
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento');
            $table->decimal('capital', 15, 2);
            $table->integer('numero_cuotas');
            $table->string('estado_cliente', 50);
            $table->text('recomendacion')->nullable();
            $table->decimal('tasa_interes_diario', 5, 2);
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
