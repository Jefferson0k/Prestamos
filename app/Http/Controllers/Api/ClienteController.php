<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ClienteController extends Controller{
    public function vista(){
        return inertia::render('Cliente/indexCliente');
    }
    public function index(){
        
    }
    public function store(){

    }
    public function edit($id){

    }
    public function update($id){

    }
    public function destroy($id){

    }
}
