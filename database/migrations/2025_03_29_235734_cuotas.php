<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(){
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_cuota');
            $table->decimal('capital', 15, 2);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('dias')->nullable();
            $table->decimal('interes', 15, 2);            
            $table->decimal('tasa_interes_diario', 15, 2);
            $table->decimal('monto_interes_pagar', 15, 2);
            $table->decimal('monto_capital_pagar', 15, 2)->nullable();
            $table->decimal('saldo_capital', 15, 2);
            $table->decimal('monto_capital_mas_interes_a_pagar', 15, 2);
            $table->enum('estado', ['Pendiente', 'Pagado', 'Vencido', 'Cancelado', 'Parcial'])->default('Pendiente');
            $table->foreignId('prestamo_id')->constrained()->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('referencia')->nullable()->unique();
            $table->date('fecha_pago')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cuotas');
    }
};
