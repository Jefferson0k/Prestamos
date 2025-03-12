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
        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('prestamo_id');
            $table->integer('numero_cuota');
            $table->decimal('capital', 15, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_pago');
            $table->integer('dias_interes');
            $table->decimal('tasa_interes_diario', 5, 2);
            $table->decimal('monto_interes_pagar', 15, 2);
            $table->decimal('monto_capital_pagar', 15, 2);
            $table->decimal('saldo_capital', 15, 2);
            $table->decimal('monto_capital_mas', 15, 2);
            $table->timestamps();

            $table->foreign('prestamo_id')->references('id')->on('prestamos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
