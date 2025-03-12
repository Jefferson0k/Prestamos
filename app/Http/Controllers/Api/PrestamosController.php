<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PrestamosController extends Controller{
    public function vista(){
        return inertia::render('Prestamos/indexPrestamos');
    }
}
