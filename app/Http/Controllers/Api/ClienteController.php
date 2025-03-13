<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\ClienteModelo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
class ClienteController extends Controller {    
    public function index() {
        return ClienteResource::collection(ClienteModelo::all());
    }
    public function store(ClienteRequest $request) {
        $data = $request->validated();
        $data['foto'] = $this->handleFotoUpload($request);
        $cliente = ClienteModelo::create($data);
        return response()->json([
            'message' => 'Cliente registrado exitosamente',
            'cliente' => $cliente
        ], 201);
    }
    public function show(ClienteModelo $cliente) {
        return response()->json($cliente);
    }
    public function update(ClienteRequest $request, ClienteModelo $cliente) {
        $data = $request->validated();
        $data['foto'] = $this->handleFotoUpload($request, $cliente);
        $cliente->update($data);
        return response()->json([
            'message' => 'Cliente actualizado correctamente',
            'cliente' => $cliente
        ]);
    }
    public function destroy(ClienteModelo $cliente) {
        if ($cliente->foto) {
            Storage::delete('public/clientes/' . $cliente->foto);
        }
        $cliente->delete();
        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
    private function handleFotoUpload(Request $request, ClienteModelo $cliente = null) {
        if (!$request->hasFile('foto')) return $cliente?->foto;
        if ($cliente && $cliente->foto) {
            Storage::delete('public/clientes/' . $cliente->foto);
        }
        $file = $request->file('foto');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/clientes', $fileName);
        return $fileName;
    }
}
