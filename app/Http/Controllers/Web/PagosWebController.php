<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\PagosModelo;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
class PagosWebController extends Controller{
    public function index(): Response{
        Gate::authorize('viewAny', PagosModelo::class);
        return Inertia::render('panel/Pagos/indexPagos');
    }
}
