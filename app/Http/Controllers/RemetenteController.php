<?php

namespace App\Http\Controllers;

use App\Facades\ApiNotasFiscais;
use DateTime;

/**
 * @OA\Info(
 *   title="API Test Azapfy",
 *   version="1.0.0",
 *   contact={
 *     "email": "eduardo.hor@outlook.com"
 *   }
 * )
 */

class RemetenteController extends Controller
{

    /**
     * @OA\Get(
     *      tags={"Remetentes"},
     *      summary="Retorna notas fiscais dos remetentes",
     *      description="Este endpoint retorna as notas ficais organizadas pelo cnpj dos remetentes. No parâmetro valores é encontrado os calculos dos valores a receber pelo remetente",
     *      path="/api/remetentes",
     * 			 @OA\Response(
     *          response=200,
     *          description="Notas Fiscais Remetentes",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="chave", type="string", example="11112222333444555566677788889"),
     *              @OA\Property(property="numero", type="string", example="123456789"),
     *              @OA\Property(property="dest", type="string", example="11112222333444555566677788889"),
     *              @OA\Property(property="cnpj_remete", type="string", example="11223344556678"),
     *              @OA\Property(property="nome_remete", type="string", example="PEREIRA PECAS LTDA"),
     *              @OA\Property(property="nome_transp", type="string", example="PEREIRA TRANSPORTE"),
     *              @OA\Property(property="cnpj_transp", type="string", example="11223344556678"),
     *              @OA\Property(property="status", type="string", example="COMPROVADO"),
     *              @OA\Property(property="valor", type="string", example="100.00"),
     *              @OA\Property(property="volumes", type="string", example="10"),
     *              @OA\Property(property="dt_emis", type="date", example="2023-03-13T17:33:25.000000Z"),
     *              @OA\Property(property="dt_entrega", type="date", example="2023-03-13T17:33:25.000000Z"),
     *              @OA\Property(property="valores", type="array",
     *                   @OA\Items(
     *                        @OA\property(property="valor_total_notas",type="string", example="100.00" ), 
     *                        @OA\property(property="valor_receber_entregue",type="string", example="100.00"),
     *                        @OA\property(property="valor_receber_nao_entregue",type="string", example="100.00"),
     *                        @OA\property(property="valor_atraso",type="string", example="100.00")
     *                   )),
     * 
     *              ),
     *     ),
     *          @OA\Response(
     *              response=400, description="Bad Request"
     *          )
     * )
     */

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
