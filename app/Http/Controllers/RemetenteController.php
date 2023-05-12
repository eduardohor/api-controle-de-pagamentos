<?php

namespace App\Http\Controllers;

use App\Facades\ApiNotasFiscais;
use Illuminate\Support\Facades\Http;

class RemetenteController extends Controller
{
    
    public function index()
    {
        $apiNotasFiscais = ApiNotasFiscais::get('api/ps/notas')->json();

        return response()->json($apiNotasFiscais, 200);
    }
}
