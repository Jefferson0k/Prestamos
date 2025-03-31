<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    
    public function up(){
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained()->onDelete('cascade');
            $table->integer('numero_cuota');
            $table->decimal('capital', 15, 2);
            $table->decimal('interes', 15, 2);
            $table->decimal('monto_total', 15, 2);
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['Pendiente', 'Pagado', 'Vencido'])->default('Pendiente');
            $table->timestamps();
        });
    }
    public function down(){
        Schema::dropIfExists('cuotas');
    }
};
