<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ReporteController extends Controller{
    public function vista(){
        return inertia::render('panel/Reporte/indexReporte');
    }
}
