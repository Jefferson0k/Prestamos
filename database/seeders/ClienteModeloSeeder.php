<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClienteModelo;

class ClienteModeloSeeder extends Seeder{
    public function run(){
        ClienteModelo::factory(5000)->create();
    }
}
