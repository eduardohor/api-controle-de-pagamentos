<?php

namespace App\Http\Controllers;

use App\Facades\ApiNotasFiscais;
use Illuminate\Support\Facades\Http;

class RemetenteController extends Controller
{
    
    public function index()
    {
        $apiNotasFiscais = ApiNotasFiscais::get('api/ps/notas')->json();

        $cnpjRemetentes = array_unique(array_column($apiNotasFiscais, 'cnpj_remete'));

        $test = array_fill_keys($cnpjRemetentes, $apiNotasFiscais);
  
        return response()->json($test, 200);
    }
}
