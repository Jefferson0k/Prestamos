<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
class ClienteWebController extends Controller{
    public function index(): Response{
        Gate::authorize('viewAny', Cliente::class);
        return Inertia::render('panel/Cliente/indexCliente');
    }
}
