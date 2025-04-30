<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prestamos;

class PrestamoSeeder extends Seeder{
    public function run(){
        Prestamos::factory(5000)->create();
    }
}
