<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\TipoCliente;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
class TipoClienteWebController extends Controller{
    public function index(): Response{
        Gate::authorize('viewAny', TipoCliente::class);
        return Inertia::render('panel/TipoCliente/indexTipoCliente');
    }
}
