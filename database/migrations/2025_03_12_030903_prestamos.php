<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void{
        Schema::create('prestamos', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->datetime('fecha_inicio');
            $table->datetime('fecha_vencimiento');
            $table->decimal('capital', 15, 2);
            $table->integer('numero_cuotas');
            $table->integer('estado_cliente');
            $table->foreignId('idRecomendado');
            $table->decimal('tasa_interes_diario', 5, 2);
            $table->decimal('monto_total', 15, 2)->comment('Capital más intereses totales');
            $table->timestamps();
        });
    }
    public function down(): void{
        Schema::dropIfExists('prestamos');
    }
};
