<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    
    public function up(){
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_cuota');
            $table->decimal('capital', 15, 2);
            $table->dateTime('Fecha_Inicio')->nullable();
            $table->dateTime('fecha_vencimiento')->nullable();
            $table->integer('Dias')->nullable();
            $table->decimal('interes', 15, 2);
            $table->decimal('Tasa_Interes_Diario', 15, 2);
            $table->decimal('Monto_Interes_Pagar', 15, 2);
            $table->decimal('Monto_Capital_Pagar', 15, 2)->nullable();
            $table->decimal('Saldo_Capital', 15, 2);
            $table->decimal('MOnto_Capital_Mas_Interes_a_Pagar', 15, 2);
            $table->enum('estado', ['Pendiente', 'Pagado', 'Vencido', 'Cancelado','Parcial'])->default('Pendiente');
            $table->foreignId('prestamo_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(){
        Schema::dropIfExists('cuotas');
    }
};
