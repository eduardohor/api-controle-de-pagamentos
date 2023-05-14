<?php

namespace App\Http\Controllers;

use App\Facades\ApiNotasFiscais;

class RemetenteController extends Controller
{

    public function index()
    {
        $notasFiscais = ApiNotasFiscais::get('api/ps/notas')->json();

        $remetentesCollection = collect($notasFiscais);

        $notasPorRemententes = $remetentesCollection
            ->groupBy('cnpj_remete')
            ->map(function ($notas) {
               
                $notas['valor_total_notas'] = strval($notas->sum('valor'));
                         
                return $notas;
            });
   
        return response()->json($notasPorRemententes, 200);
    }
}
