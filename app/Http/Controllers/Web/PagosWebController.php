<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
class PagosWebController extends Controller{
    public function index(): Response{
        return Inertia::render('Pagos/indexPagos');
    }
}
