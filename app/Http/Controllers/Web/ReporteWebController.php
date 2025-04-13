<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
class ReporteWebController extends Controller{
    public function index(): Response{
        return Inertia::render('panel/Reporte/indexReporte');
    }
}
