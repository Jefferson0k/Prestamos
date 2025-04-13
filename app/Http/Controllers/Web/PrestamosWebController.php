<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
class PrestamosWebController extends Controller{
    public function index(): Response{
        return Inertia::render('panel/Prestamos/indexPrestamos');
    }
}
