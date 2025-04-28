<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteModeloSeeder extends Seeder{
    public function run(){
        Cliente::factory(5000)->create();
    }
}
