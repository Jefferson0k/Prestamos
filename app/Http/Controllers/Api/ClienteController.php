<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClienteModelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
class ClienteController extends Controller{
    public function vista(){
        return Inertia::render('Cliente/indexCliente');
    }
    public function index(){
        $clientes = ClienteModelo::all();
        return response()->json($clientes);
    }
    public function store(Request $request){
        $request->validate([
            'dni' => 'required|unique:clientes,dni|max:20',
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'correo' => 'nullable|email|unique:clientes,correo|max:150',
            'centro_trabajo' => 'nullable|string|max:150',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $data = $request->all();
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/clientes', $fileName);
            $data['foto'] = $fileName;
        }
        $cliente = ClienteModelo::create($data);
        return response()->json([
            'message' => 'Cliente registrado exitosamente',
            'cliente' => $cliente
        ], 201);
    }
    public function edit($id){
        $cliente = ClienteModelo::findOrFail($id);
        return response()->json($cliente);
    }
    public function update(Request $request, $id){
        $cliente = ClienteModelo::findOrFail($id);
        $request->validate([
            'dni' => "required|max:20|unique:clientes,dni,$id",
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'correo' => "nullable|email|max:150|unique:clientes,correo,$id",
            'centro_trabajo' => 'nullable|string|max:150',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $data = $request->all();
        if ($request->hasFile('foto')) {
            if ($cliente->foto) {
                Storage::delete('public/clientes/' . $cliente->foto);
            }
            $file = $request->file('foto');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/clientes', $fileName);
            $data['foto'] = $fileName;
        }
        $cliente->update($data);
        return response()->json([
            'message' => 'Cliente actualizado correctamente',
            'cliente' => $cliente
        ]);
    }
    public function destroy($id){
        $cliente = ClienteModelo::findOrFail($id);
        if ($cliente->foto) {
            Storage::delete('public/clientes/' . $cliente->foto);
        }
        $cliente->delete();
        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
}