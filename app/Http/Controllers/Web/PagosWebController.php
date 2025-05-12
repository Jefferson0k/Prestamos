<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Pagos;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
class PagosWebController extends Controller{
    public function index(): Response{
        Gate::authorize('viewAny', Pagos::class);
        return Inertia::render('panel/Pagos/indexPagos');
    }
}
