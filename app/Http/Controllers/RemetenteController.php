<?php

namespace App\Http\Controllers;

use App\Facades\ApiNotasFiscais;
use DateTime;

class RemetenteController extends Controller
{

    public function index()
    {
        $notasFiscais = ApiNotasFiscais::get('api/ps/notas')->json();

        $remetentesCollection = collect($notasFiscais);

        $notasPorRemententes = $remetentesCollection
            ->groupBy('cnpj_remete')
            ->map(function ($notas) {

                $valorReceberEntregue = 0;
                $valorReceberNaoEntregue = 0;
                $valorAtraso = 0;

                foreach ($notas as $nota) {
                    if (array_key_exists("dt_entrega", $nota)) {
                        $entrega = strtotime(str_replace('/', '-', $nota['dt_entrega']));
                        $emissao = strtotime(str_replace('/', '-', $nota['dt_emis']));

                        $dataEntrega = date('d-m-y H:i:s', $entrega);
                        $dataEmissao = date('d-m-y H:i:s', $emissao);

                        $datetimeEntrega = new DateTime($dataEntrega);
                        $datetimeEmissao = new DateTime($dataEmissao);

                        $intervalo = $datetimeEntrega->diff($datetimeEmissao)->y;


                        if ($nota['status'] === 'COMPROVADO' && $intervalo <= 2) {
                            $valorReceberEntregue += $nota['valor'];
                        } elseif ($nota['status'] === 'COMPROVADO' && $intervalo > 2) {
                            $valorAtraso += $nota['valor'];
                        }
                    } else {
                        $valorReceberNaoEntregue += $nota['valor'];
                    }
                }

                $valores = [
                    'valor_total_notas' => strval($notas->sum('valor')),
                    'valor_receber_entregue' => strval($valorReceberEntregue),
                    'valor_receber_nao_entregue' => strval($valorReceberNaoEntregue),
                    'valor_atraso' => strval($valorAtraso)
                ];

                $notas['valores'] = $valores;
                
                return $notas;
            });

        return response()->json($notasPorRemententes, 200);
    }
}
