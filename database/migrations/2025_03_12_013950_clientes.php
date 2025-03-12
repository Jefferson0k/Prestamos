<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombres');
            $table->string('apellidos');
            $table->text('direccion')->nullable();
            $table->string('centro_de_trabajo')->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('dni', 15)->unique();
            $table->date('fecha_de_inicio')->nullable();
            $table->date('fecha_de_vencimiento')->nullable();
            $table->decimal('tasa_de_interes_diario', 8, 2)->default(0);
            $table->decimal('capital_inicial', 10, 2)->default(0);
            $table->decimal('capital_del_mes', 10, 2)->default(0);
            $table->decimal('capital_actual', 10, 2)->default(0);
            $table->decimal('interes_actual', 10, 2)->default(0);
            $table->decimal('interes_total', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->integer('numero_cuotas')->default(0);
            $table->enum('estado_del_cliente', ['activo', 'inactivo'])->default('activo');
            $table->string('foto_del_cliente')->nullable();
            $table->text('recomendacion')->nullable();
            $table->timestamps();
        });
    }
    public function down(){
        Schema::dropIfExists('clientes');
    }
};
