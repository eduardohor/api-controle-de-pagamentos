<?php

namespace App\Http\Controllers;

use App\Facades\ApiNotasFiscais;

class RemetenteController extends Controller
{

    public function index()
    {
        $notasFiscais = ApiNotasFiscais::get('api/ps/notas')->json();

        $remetentesCollection = collect($notasFiscais);

        $notasRemententes = $remetentesCollection->groupBy('cnpj_remete');

        return response()->json($notasRemententes, 200);
    }
}
