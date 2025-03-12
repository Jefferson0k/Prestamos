<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('dni', 10)->unique();
            $table->string('nombre', 100);
            $table->string('apellidos', 150);
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('correo', 100)->unique();
            $table->string('centro_trabajo', 255)->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }
    public function down(){
        Schema::dropIfExists('clientes');
    }
};
