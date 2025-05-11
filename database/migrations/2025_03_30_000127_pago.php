<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    
    public function up(): void{
        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('id');            
            $table->foreignId('prestamo_id')->constrained()->onDelete('cascade');            
            $table->foreignId('cuota_id')->nullable()->constrained()->onDelete('set null');            
            $table->decimal('capital', 15, 2);
            $table->date('fecha_pago');
            $table->decimal('monto_capital', 15, 2);
            $table->decimal('monto_interes', 15, 2);
            $table->decimal('monto_total', 15, 2);
            $table->string('metodo_pago')->nullable();            
            $table->string('moneda', 3)->default('PEN');            
            $table->string('Codigo_Comprobante');
            $table->string('referencia')->unique()->nullable();            
            $table->text('observacion')->nullable();          
            $table->foreignId('codigo_pago_id')->nullable()->constrained('codigos_pagos')->onDelete('set null');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');  
            $table->enum('estado', ['activo', 'anulado'])->default('activo');            
            $table->timestamps();
        });
    }

    public function down(): void{
        Schema::dropIfExists('pagos');
    }
};
