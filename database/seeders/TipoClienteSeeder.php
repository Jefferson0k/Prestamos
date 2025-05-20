<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoCliente;

class TipoClienteSeeder extends Seeder{
    public function run(){
        TipoCliente::factory(2)->create();
    }
}
