<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Support\Facades\Gate;

class ConsultasDni extends Controller{
    public function consultar($dni = null,Cliente $cliente){
        Gate::authorize('view', $cliente);
        if (empty($dni)) {
            return response()->json(['error' => 'Debe proporcionar un DNI válido'], 400);
        }
        $token = '7376|GjwfAFgF0cjFhnmxUjl15BznXKjh69AIC2phYEQa';
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://apis.aqpfact.pe/api/dni/' . $dni,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Referer: https://apis.net.pe/consulta-dni-api',
                'Authorization: Bearer ' . $token,
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $persona = json_decode($response);
        if (!$persona) {
            return response()->json(['error' => 'No se encontraron datos para el DNI proporcionado'], 404);
        }
        return response()->json($persona);
    }
}
