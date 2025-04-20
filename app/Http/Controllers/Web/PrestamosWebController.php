<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\PrestamosModelo;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
class PrestamosWebController extends Controller{
    public function index(): Response{
        Gate::authorize('viewAny', PrestamosModelo::class);
        return Inertia::render('panel/Prestamos/indexPrestamos');
    }
}
