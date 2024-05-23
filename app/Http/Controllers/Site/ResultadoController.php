<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\CondicaoClimatica;
use App\Models\Site\Corrida;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Models\Site\ForcaPiloto;
use App\Models\Site\ForcaEquipe;

class ResultadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $corrida = Corrida::where('id', $id)->first();
        $voltaRapida = PilotoEquipe::find($corrida->volta_rapida);
        $descEvento = 'Grand Prix';
        if ($corrida->flg_sprint == 'S') {
            $descEvento = 'Sprint';
        }

        if ($corrida->flg_super_corrida == 'S') {
            $descEvento = 'Super-Race';
        }

        $condicoesClimaticas = CondicaoClimatica::where('user_id', Auth::user()->id)->get();
        $model = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $corrida->id)->orderBy('chegada')->paginate(11);
        $vencedor = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $corrida->id)->where('chegada', 1)->orderBy('chegada')->first();
        if (count($model) == 0) {
            return redirect()->back()->with('error', 'Não existem resultados cadastrados para o evento selecionado');
        }

        return view('site.resultados.show', compact('corrida', 'model', 'condicoesClimaticas', 'vencedor', 'descEvento', 'voltaRapida'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $corrida = Corrida::where('id', $id)->first();
        $condicoesClimaticas = CondicaoClimatica::where('user_id', Auth::user()->id)->get();
        $model = PilotoEquipe::where('user_id', Auth::user()->id)->where('ano_id', $corrida->temporada->ano_id)->where('flg_ativo', 'S')->orderBy('equipe_id')->get();

        return view('site.resultados.form', compact('corrida', 'model', 'condicoesClimaticas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {   
        $corrida = Corrida::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $corrida->volta_rapida = $request->volta_rapida;
        $corrida->condicao_id = $request->condicao_id;
        $corrida->qtd_safety_car = $request->qtd_safety_car;
        $corrida->dificuldade_ia = $request->dificuldade_ia;
        $corrida->observacoes = $request->observacoes;
        $corrida->updated_at = date('Y-m-d H:i:s');

        if ($request->categoria == 'F1Sprint') {
            $corrida->flg_sprint = 'S';
        } else {
            $corrida->flg_sprint = 'N';
        }

        if ($request->categoria == 'SC') {
            $corrida->flg_super_corrida = 'S';
            $corrida->flg_sprint = 'S';
        }

        $corrida->update();

        foreach ($request->pilotoEquipe_id as $key => $pilotoEquipe) {
            $model = Resultado::where('user_id', Auth::user()->id)->where('pilotoEquipe_id', $pilotoEquipe)->where('corrida_id', $corrida->id)->first();
            $largada = $request->input('largada')[$key];
            $chegada = $request->input('chegada')[$key];
            if ($model) {
                $model->largada = $largada;
                $model->chegada = $chegada;
                $model->flg_abandono = 'N';
                if ($request->input('flg_abandono')) {
                    foreach ($request->input('flg_abandono') as $isAbandono) {
                        if ($isAbandono == $pilotoEquipe) {
                            $model->flg_abandono = 'S';
                        }
                    }
                }

                if ($request->categoria == 'FormulaE') {
                    //Regra de 3 pontos para o pole position
                    if ($model->largada == 1) {
                        $model->pontuacao = $this->calcularPontuacaoFormulaE($model, $chegada) + 3;
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    } else {
                        $model->pontuacao = $this->calcularPontuacaoFormulaE($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }
                }

                if ($request->categoria == 'F1') {
                    $model->pontuacao = $this->calcularPontuacao($model, $chegada);
                    $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                    $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                    $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                }

                if ($request->categoria == 'F1Sprint') {
                    $model->pontuacao = $this->calcularPontuacaoSprint($model, $chegada);
                    $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                    $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                    $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                }

                if ($request->categoria == 'SC') {
                    if ($model['largada'] == 1) {
                        $model->pontuacao = $this->calcularPontuacaoSuperCorrida($model, $chegada) + 0;
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    } else {
                        $model->pontuacao = $this->calcularPontuacaoSuperCorrida($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }
                }

                if ($request->categoria == 'Indy') {
                    if ($model->largada == 1) {
                        $model->pontuacao = $this->calcularPontuacaoIndy($model, $chegada) + 1;
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    } else {
                        $model->pontuacao = $this->calcularPontuacaoIndy($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }
                }

                if ($request->categoria == 'Stock1') {
                    //Regra de 2 pontos para o pole position da Corrida 1
                    if ($model->largada == 1) {
                        $model->pontuacao = $this->calcularPontuacaoStockCar1($model, $chegada) + 2;
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    } else {
                        $model->pontuacao = $this->calcularPontuacaoStockCar1($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }
                }

                if ($request->categoria == 'Stock2') {
                    $model->pontuacao = $this->calcularPontuacaoStockCar2($model, $chegada);
                    $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                    $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                    $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                }

                $model->update();
            } else {
                $model = new Resultado();
                $model->corrida_id = $corrida->id;
                $model->pilotoEquipe_id = $pilotoEquipe;
                $model->user_id = Auth::user()->id;
                $model->largada = $largada;
                $model->chegada = $chegada;
                if ($request->input('flg_abandono')) {
                    foreach ($request->input('flg_abandono') as $isAbandono) {
                        if ($isAbandono == $pilotoEquipe) {
                            $model->flg_abandono = 'S';
                        }
                    }
                }

                if ($request->categoria == 'FormulaE') {
                    if ($model->largada == 1) {
                        $model->pontuacao = $this->calcularPontuacaoFormulaE($model, $chegada) + 3;
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao = $this->calcularPontuacaoInvertida($model, $chegada);
                    } else {
                        $model->pontuacao = $this->calcularPontuacaoFormulaE($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }
                }

                if ($request->categoria == 'F1') {
                    $model->pontuacao = $this->calcularPontuacao($model, $chegada);
                    $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                    $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                    $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                }

                if ($request->categoria == 'F1Sprint') {
                    $model->pontuacao = $this->calcularPontuacaoSprint($model, $chegada);
                    $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                    $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                    $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                }

                if ($request->categoria == 'SC') {
                    if ($model->largada == 1) {
                        // $model->pontuacao = $this->calcularPontuacaoSuperCorrida($model, $chegada)+20;
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    } else {
                        $model->pontuacao = $this->calcularPontuacaoSuperCorrida($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }
                }

                if ($request->categoria == 'Indy') {
                    if ($model->largada == 1) {
                        $model->pontuacao = $this->calcularPontuacaoIndy($model, $chegada) + 1;
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    } else {
                        $model->pontuacao = $this->calcularPontuacaoIndy($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }
                }

                if ($request->categoria == 'Stock1') {
                    //Regra de 3 pontos para o pole position da Corrida 1
                    if ($model->largada == 1) {
                        $model->pontuacao = $this->calcularPontuacaoStockCar1($model, $chegada) + 2;
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    } else {
                        $model->pontuacao = $this->calcularPontuacaoStockCar1($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }
                }

                if ($request->categoria == 'Stock2') {
                    $model->pontuacao = $this->calcularPontuacaoStockCar2($model, $chegada);
                    $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                    $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                    $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                }

                //salva as alterações 
                if (($model->largada != null) || $model->chegada != null) {
                    $model->save();
                }
            }
        }

        /**Cáculo da volta mais rapida Formula 1 */
        if ($corrida->volta_rapida != null && $corrida->flg_super_corrida != 'S') {
            $resultadoVoltaRapida = Resultado::where('pilotoEquipe_id', $corrida->volta_rapida)
                ->where('user_id', Auth::user()->id)
                ->where('corrida_id', $corrida->id)
                ->first();

            if ($resultadoVoltaRapida->chegada <= 10 || ($corrida->flg_super_corrida == 'S' && $resultadoVoltaRapida->chegada <= 8)) {
                $resultadoVoltaRapida->pontuacao = $resultadoVoltaRapida->pontuacao + 1;
                $resultadoVoltaRapida->update();
            }
        }

        /**calcula das super corridas F1 */
        if ($corrida->volta_rapida != null && $corrida->flg_super_corrida == 'S') {
            $resultadoVoltaRapida = Resultado::where('pilotoEquipe_id', $corrida->volta_rapida)
                ->where('user_id', Auth::user()->id)
                ->where('corrida_id', $corrida->id)
                ->first();

            if ($resultadoVoltaRapida->chegada <= 10) {
                $resultadoVoltaRapida->pontuacao = $resultadoVoltaRapida->pontuacao + 1;
                $resultadoVoltaRapida->update();
            }
        }

        //pega as posições de largada dos pilotos
        $arrayPilotoEquipe_id = $request->pilotoEquipe_id;
        $arrayPosicaoLargada = $request->largada;
        
        // Criando o array associativo com chave = pilotoEquipe_id e valor = largada
        $arrayOrdemLargada = array();
        for ($i = 0; $i < count($arrayPilotoEquipe_id); $i++) {
            if ($arrayPosicaoLargada[$i] !== null) {
                $arrayOrdemLargada[$arrayPilotoEquipe_id[$i]] = $arrayPosicaoLargada[$i];
            }
        }

        // Ordenando o array pelos valores (largada)
        asort($arrayOrdemLargada);

        //fazer foreach consultando na tabela de forças e skins e ir montando o arquivo de acordo com o arquivo PHP puro
        $ballast = [];
        $restrictor = [];
        $skins = [];

        foreach($arrayOrdemLargada as $pilotoEquipe_id => $largada){

            //descubro o piloto baseado no pilotoEquipe_id ordenado do grid de largada
            $pilotoEquipe = PilotoEquipe::find($pilotoEquipe_id);
            $piloto = $pilotoEquipe->piloto->id;
            $equipe = $pilotoEquipe->equipe->id;

            //descubro a força do piloto
            $pilotoForca = ForcaPiloto::where('piloto_id', $piloto)
                                        ->where('ano_id', $corrida->temporada->ano->id)
                                        ->where('user_id', Auth::user()->id)
                                        ->first();

            //adiciona ao array de forças que será colocado no documento
            $ballast[] = isset($pilotoForca) ? $pilotoForca->forca : 'null';

            //descubro a força da equipe
            $equipeForca = ForcaEquipe::where('equipe_id', $equipe)
                                        ->where('ano_id', $corrida->temporada->ano->id)
                                        ->where('user_id', Auth::user()->id)
                                        ->first();

             //adiciona ao array de forças que será colocado no documento
             $restrictor[] = isset($equipeForca) ? $equipeForca->forca : 'null';

             $skins[] = isset($pilotoEquipe->skin) ? $pilotoEquipe->skin->skin : null;

        }
        
        // dd($arrayPilotoEquipe_id, $arrayPosicaoLargada, $arrayOrdemLargada);
        //dd($ballast, $restrictor, $skins);

        //colocar os dados no documento fixado e gerar o download

        if($request->gerarGridLargada == 'S'){

           $json_string = '{"ModeId":"custom","FilterValue":"","CarIds":["rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023","rss_formula_hybrid_2023"],"AiLevels":["100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100","100"],"Ballasts":["70","90","100","9","12","10","11","13","17","20","11","2","17","11","17","1","10","16","17","19","17","16","17","2","19"],"Restrictors":["2","2","0","3","0","5","8","14","10","10","5","17","13","6","18","3","12","12","18","13","14","18","17","6","18"],"PlayerRestrictor":8.0,"SkinIds":["MCL38_4_Norris","MCL38_81_Piastri","RB20_1_Verstappen_concept","SF24_Miami_16_Leclerc","RB20_11_Perez_bee_concept-1","W15_63_Russell","24_Sean_Bull_Maserati__Concept_#48","24_Audi_Sport_Sauber_Concept_JQKA_5-1","VCARB01_22_Tsunoda","VCARB01_22_Tsunoda-1","W15_44_Albon","24_Sean_Bull_BMW_Concept_#5-1","VF24_20_Magnussen","AMR24_18_Leclerc-1","2024_Andretti_Cadillac_9","SF24_Miami_55_Sainz","23_Renault_Concept_31","23_Renault_Concept_10","2024_Andretti_Cadillac_26","VF24_27_Hulkenberg-1","24_Audi_Sport_Sauber_Concept_JQKA_47","FW46_2_Perez-1","24_Sean_Bull_BMW_Concept_#50-1","AMR24_14_Alonso-1","FW46_2_Antonelli"],"ShuffleCandidates":true,"VarietyLimitation":0,"OpponentsNumber":25,"StartingPosition":6,"AiLevel":100.0,"AiLevelMin":99.0,"AiLevelArrangeRandom":0.1,"AiLevelArrangeReverse":false,"AiLevelArrangePowerRatio":false,"AiAggression":59.6,"AiAggressionMin":18.6,"AiAggressionArrangeRandom":0.1,"AiAggressionArrangeReverse":false}';

           // Decodifica a string JSON em um array associativo
            $data = json_decode($json_string, true);

            // Substitui os valores do array "Ballasts"
            $data['Ballasts'] = $ballast;
            $data['Restrictors'] = $restrictor;
            $data['StartingPosition'] = 26;
            $data['PlayerRestrictor'] = 50;
            $data['SkinIds'] = $skins;

            // Codifica o array associativo de volta para uma string JSON
            $new_json_string = json_encode($data);

            // Converte a string JSON para um array associativo PHP
            $conteudo = json_encode($new_json_string, true);
            $conteudo = stripslashes($conteudo);
            $conteudo = substr($conteudo, 1, -1);

            // Nome do arquivo que será criado

            //se for menos que dez e maior que 0, colocar o zero antes do numero
            $nomeArquivoOrdem = $corrida->ordem;
            if($corrida->ordem > 0 || $corrida->ordem < 10){
                $nomeArquivoOrdem = '0'.$corrida->ordem; 
            }

            $nomeArquivoTipo = 'Sprint';
            $nomeArquivoPista = $corrida->pista->nome;

            $nomeArquivo = $nomeArquivoOrdem.'_'.$nomeArquivoPista.".CMPRESET";

            if($corrida->flg_sprint != 'N'){
                $nomeArquivo = $nomeArquivoOrdem.'_'.$nomeArquivoTipo.'_'.$nomeArquivoPista.".CMPRESET";
            }

            // Define os cabeçalhos HTTP para forçar o download do arquivo
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            header('Content-Length: ' . strlen($conteudo));

            // Envia o conteúdo do arquivo
            echo $conteudo;
        }else{
            return redirect()->back();
        }
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function calcularPontuacao($model, $chegada)
    {
        $primeiro = 25;
        $segundo = 18;
        $terceiro = 15;
        $quarto = 12;
        $quinto = 10;
        $sexto = 8;
        $setimo = 6;
        $oitavo = 4;
        $nono = 2;
        $decimo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            default:
                $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }
    public function calcularPontuacaoFormulaE($model, $chegada)
    {
        $primeiro = 25;
        $segundo = 18;
        $terceiro = 15;
        $quarto = 12;
        $quinto = 10;
        $sexto = 8;
        $setimo = 6;
        $oitavo = 4;
        $nono = 2;
        $decimo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            default:
                $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }

    public function calcularPontuacaoClassica($model, $chegada)
    {
        $primeiro = 10;
        $segundo = 6;
        $terceiro = 4;
        $quarto = 3;
        $quinto = 2;
        $sexto = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao_classica = $primeiro;
                break;
            case 2:
                $model->pontuacao_classica = $segundo;
                break;
            case 3:
                $model->pontuacao_classica = $terceiro;
                break;
            case 4:
                $model->pontuacao_classica = $quarto;
                break;
            case 5:
                $model->pontuacao_classica = $quinto;
                break;
            case 6:
                $model->pontuacao_classica = $sexto;
                break;
            default:
                $model->pontuacao_classica = 0;
        }

        return $model->pontuacao_classica;
    }

    // public function calcularPontuacaoSuperCorrida($model, $chegada){
    //     $primeiro = 50;
    //     $segundo = 40;
    //     $terceiro = 35;
    //     $quarto = 32;
    //     $quinto = 30;
    //     $sexto = 28;
    //     $setimo = 26;
    //     $oitavo = 24;
    //     $nono = 22;
    //     $decimo = 20; 
    //     $decimoPrimeiro = 19; 
    //     $decimoSegundo = 18; 
    //     $decimoTerceiro = 17; 
    //     $decimoQuarto = 16; 
    //     $decimoQuinto = 15; 
    //     $decimoSexto = 14; 
    //     $decimoSetimo = 13; 
    //     $decimoOitavo = 12; 
    //     $decimoNono = 11; 
    //     $vigesimo = 10;
    //     $vigesimoPrimeiro = 9;
    //     $vigesimoSegundo = 8;
    //     $vigesimoTerceiro = 7;
    //     $vigesimoQuarto = 6;

    //     switch ($chegada) {
    //         case 1:
    //             $model->pontuacao = $primeiro;
    //             break;
    //         case 2:
    //             $model->pontuacao = $segundo;
    //             break;
    //         case 3:
    //             $model->pontuacao = $terceiro;
    //             break;
    //         case 4:
    //             $model->pontuacao = $quarto;
    //             break;
    //         case 5:
    //             $model->pontuacao = $quinto;
    //             break;
    //         case 6:
    //             $model->pontuacao = $sexto;
    //             break;
    //         case 7:
    //             $model->pontuacao = $setimo;
    //             break;
    //         case 8:
    //             $model->pontuacao = $oitavo;
    //             break;
    //         case 9:
    //             $model->pontuacao = $nono;
    //             break;
    //         case 10:
    //             $model->pontuacao = $decimo;
    //             break;
    //         case 11:
    //             $model->pontuacao = $decimoPrimeiro;
    //             break;
    //         case 12:
    //             $model->pontuacao = $decimoSegundo;
    //             break;
    //         case 13:
    //             $model->pontuacao = $decimoTerceiro;
    //             break;
    //         case 14:
    //             $model->pontuacao = $decimoQuarto;
    //             break;
    //         case 15:
    //             $model->pontuacao = $decimoQuinto;
    //             break;
    //         case 16:
    //             $model->pontuacao = $decimoSexto;
    //             break;
    //         case 17:
    //             $model->pontuacao = $decimoSetimo;
    //             break;
    //         case 18:
    //             $model->pontuacao = $decimoOitavo;
    //             break;
    //         case 19:
    //             $model->pontuacao = $decimoNono;
    //             break;
    //         case 20:
    //             $model->pontuacao = $vigesimo;
    //             break;
    //         case 21:
    //             $model->pontuacao = $vigesimoPrimeiro;
    //             break;
    //         case 22:
    //             $model->pontuacao = $vigesimoSegundo;
    //             break;
    //         case 23:
    //             $model->pontuacao = $vigesimoTerceiro;
    //             break;
    //         case 24:
    //             $model->pontuacao = $vigesimoQuarto;
    //             break;
    //         default:
    //         $model->pontuacao = 5;
    //     }

    //     return $model->pontuacao;
    // }
    public function calcularPontuacaoSuperCorrida($model, $chegada)
    {
        $primeiro = 10;
        $segundo = 8;
        $terceiro = 6;
        $quarto = 5;
        $quinto = 4;
        $sexto = 3;
        $setimo = 2;
        $oitavo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            default:
                $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }
    public function calcularPontuacaoIndy($model, $chegada)
    {
        $primeiro = 50;
        $segundo = 40;
        $terceiro = 35;
        $quarto = 32;
        $quinto = 30;
        $sexto = 28;
        $setimo = 26;
        $oitavo = 24;
        $nono = 22;
        $decimo = 20;
        $decimoPrimeiro = 19;
        $decimoSegundo = 18;
        $decimoTerceiro = 17;
        $decimoQuarto = 16;
        $decimoQuinto = 15;
        $decimoSexto = 14;
        $decimoSetimo = 13;
        $decimoOitavo = 12;
        $decimoNono = 11;
        $vigesimo = 10;
        $vigesimoPrimeiro = 9;
        $vigesimoSegundo = 8;
        $vigesimoTerceiro = 7;
        $vigesimoQuarto = 6;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            case 11:
                $model->pontuacao = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao = $decimoSexto;
                break;
            case 17:
                $model->pontuacao = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao = $decimoNono;
                break;
            case 20:
                $model->pontuacao = $vigesimo;
                break;
            case 21:
                $model->pontuacao = $vigesimoPrimeiro;
                break;
            case 22:
                $model->pontuacao = $vigesimoSegundo;
                break;
            case 23:
                $model->pontuacao = $vigesimoTerceiro;
                break;
            case 24:
                $model->pontuacao = $vigesimoQuarto;
                break;
            default:
                $model->pontuacao = 5;
        }

        return $model->pontuacao;
    }
    public function calcularPontuacaoStockCar1($model, $chegada)
    {
        $primeiro = 30;
        $segundo = 26;
        $terceiro = 22;
        $quarto = 19;
        $quinto = 17;
        $sexto = 15;
        $setimo = 14;
        $oitavo = 13;
        $nono = 12;
        $decimo = 11;
        $decimoPrimeiro = 10;
        $decimoSegundo = 9;
        $decimoTerceiro = 8;
        $decimoQuarto = 7;
        $decimoQuinto = 6;
        $decimoSexto = 5;
        $decimoSetimo = 4;
        $decimoOitavo = 3;
        $decimoNono = 2;
        $vigesimo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            case 11:
                $model->pontuacao = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao = $decimoSexto;
                break;
            case 17:
                $model->pontuacao = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao = $decimoNono;
                break;
            case 20:
                $model->pontuacao = $vigesimo;
                break;
            default:
                $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }
    public function calcularPontuacaoStockCar2($model, $chegada)
    {
        $primeiro = 24;
        $segundo = 20;
        $terceiro = 18;
        $quarto = 17;
        $quinto = 16;
        $sexto = 15;
        $setimo = 14;
        $oitavo = 13;
        $nono = 12;
        $decimo = 11;
        $decimoPrimeiro = 10;
        $decimoSegundo = 9;
        $decimoTerceiro = 8;
        $decimoQuarto = 7;
        $decimoQuinto = 6;
        $decimoSexto = 5;
        $decimoSetimo = 4;
        $decimoOitavo = 3;
        $decimoNono = 2;
        $vigesimo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            case 11:
                $model->pontuacao = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao = $decimoSexto;
                break;
            case 17:
                $model->pontuacao = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao = $decimoNono;
                break;
            case 20:
                $model->pontuacao = $vigesimo;
                break;
            default:
                $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }

    public function calcularPontuacaoPersonalizada($model, $chegada)
    {
        $primeiro = 50;
        $segundo = 40;
        $terceiro = 35;
        $quarto = 28;
        $quinto = 26;
        $sexto = 24;
        $setimo = 22;
        $oitavo = 20;
        $nono = 18;
        $decimo = 16;
        $decimoPrimeiro = 12;
        $decimoSegundo = 11;
        $decimoTerceiro = 10;
        $decimoQuarto = 9;
        $decimoQuinto = 8;
        $decimoSexto = 7;
        $decimoSetimo = 6;
        $decimoOitavo = 5;
        $decimoNono = 4;
        $vigesimo = 3;
        $vigesimoPrimeiro = 2;
        $vigesimoSegundo = 1;


        switch ($chegada) {
            case 1:
                $model->pontuacao_personalizada = $primeiro;
                break;
            case 2:
                $model->pontuacao_personalizada = $segundo;
                break;
            case 3:
                $model->pontuacao_personalizada = $terceiro;
                break;
            case 4:
                $model->pontuacao_personalizada = $quarto;
                break;
            case 5:
                $model->pontuacao_personalizada = $quinto;
                break;
            case 6:
                $model->pontuacao_personalizada = $sexto;
                break;
            case 7:
                $model->pontuacao_personalizada = $setimo;
                break;
            case 8:
                $model->pontuacao_personalizada = $oitavo;
                break;
            case 9:
                $model->pontuacao_personalizada = $nono;
                break;
            case 10:
                $model->pontuacao_personalizada = $decimo;
                break;
            case 11:
                $model->pontuacao_personalizada = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao_personalizada = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao_personalizada = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao_personalizada = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao_personalizada = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao_personalizada = $decimoSexto;
                break;
            case 17:
                $model->pontuacao_personalizada = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao_personalizada = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao_personalizada = $decimoNono;
                break;
            case 20:
                $model->pontuacao_personalizada = $vigesimo;
                break;
            case 21:
                $model->pontuacao_personalizada = $vigesimoPrimeiro;
                break;
            case 22:
                $model->pontuacao_personalizada = $vigesimoSegundo;
                break;
            default:
                $model->pontuacao_personalizada = 1;
        }

        return $model->pontuacao_personalizada;
    }

    public function calcularPontuacaoInvertida($model, $chegada)
    {
        $primeiro = 1;
        $segundo = 2;
        $terceiro = 3;
        $quarto = 4;
        $quinto = 5;
        $sexto = 6;
        $setimo = 7;
        $oitavo = 8;
        $nono = 9;
        $decimo = 10;
        $decimoPrimeiro = 11;
        $decimoSegundo = 12;
        $decimoTerceiro = 16;
        $decimoQuarto = 18;
        $decimoQuinto = 20;
        $decimoSexto = 22;
        $decimoSetimo = 24;
        $decimoOitavo = 26;
        $decimoNono = 28;
        $vigesimo = 35;
        $vigesimoPrimeiro = 40;
        $vigesimoSegundo = 50;


        switch ($chegada) {
            case 1:
                $model->pontuacao_invertida = $primeiro;
                break;
            case 2:
                $model->pontuacao_invertida = $segundo;
                break;
            case 3:
                $model->pontuacao_invertida = $terceiro;
                break;
            case 4:
                $model->pontuacao_invertida = $quarto;
                break;
            case 5:
                $model->pontuacao_invertida = $quinto;
                break;
            case 6:
                $model->pontuacao_invertida = $sexto;
                break;
            case 7:
                $model->pontuacao_invertida = $setimo;
                break;
            case 8:
                $model->pontuacao_invertida = $oitavo;
                break;
            case 9:
                $model->pontuacao_invertida = $nono;
                break;
            case 10:
                $model->pontuacao_invertida = $decimo;
                break;
            case 11:
                $model->pontuacao_invertida = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao_invertida = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao_invertida = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao_invertida = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao_invertida = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao_invertida = $decimoSexto;
                break;
            case 17:
                $model->pontuacao_invertida = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao_invertida = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao_invertida = $decimoNono;
                break;
            case 20:
                $model->pontuacao_invertida = $vigesimo;
                break;
            case 21:
                $model->pontuacao_invertida = $vigesimoPrimeiro;
                break;
            case 22:
                $model->pontuacao_invertida = $vigesimoSegundo;
                break;
            default:
                $model->pontuacao_invertida = 70;
        }

        return $model->pontuacao_invertida;
    }

    public function calcularPontuacaoSprint($model, $chegada)
    {
        $primeiro = 8;
        $segundo = 7;
        $terceiro = 6;
        $quarto = 5;
        $quinto = 4;
        $sexto = 3;
        $setimo = 2;
        $oitavo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            default:
                $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }
}
