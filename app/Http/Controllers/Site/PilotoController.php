<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Piloto;
use App\Models\Site\Pais;
use App\Models\Site\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\PilotosExport;
use App\Models\Site\Corrida;
use App\Models\Site\ForcaPiloto;
use App\Models\Site\Temporada;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\Titulo;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PilotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //pega os pilotos do ID 3 que são uma espécie de usuário padrão; Suponha que tenha dois schumacher, daqui pra frente apenas 1 único será considerado e será o do ID 3
        $pilotos = Piloto::where('user_id', 3)
                            ->orderBy('flg_ativo', 'DESC')
                            ->orderBy('id')
                            ->get();

        $resultados = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
                                    ->join('temporadas', 'temporadas.id', '=', 'corridas.temporada_id')
                                    ->where('resultados.user_id', Auth::user()->id)
                                    ->where('corridas.flg_sprint', 'N')
                                    ->orderBy('resultados.id', 'ASC')
                                    ->orderBy('temporadas.id', 'ASC')
                                    ->get();

        foreach($resultados as $resultado){

            $pilotos->firstWhere('id', $resultado->piloto_id)->corridas++;

            if($resultado->chegada == 1){
                $pilotos->firstWhere('id', $resultado->piloto_id)->vitorias++;
            }

            if($resultado->largada == 1){
                $pilotos->firstWhere('id', $resultado->piloto_id)->poles++;
            }

            if($resultado->chegada >= 1 && $resultado->chegada <=3){
                $pilotos->firstWhere('id', $resultado->piloto_id)->podios++;
            }

            if($resultado->flg_abandono == 'S'){
                $pilotos->firstWhere('id', $resultado->piloto_id)->abandonos++;
            }

        }

        return view('site.pilotos.index', compact('pilotos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $paises = Pais::where('user_id', Auth::user()->id)->get();
        return view('site.pilotos.form', compact('paises'));
    }

    // public function listAllDrivers(){
    //     return view('site.pilotos.allDrivers');
    // }

    // public function getAllDrivers(Request $request){
        
    //     $retorno = Piloto::with('pais');

    //     if($request->busca){
    //         $retorno = $retorno->where('nome', 'LIKE', '%'.$request->busca.'%');
            
    //         $retorno = $retorno->orWhereHas('pais', function($query) use ($request){
    //             $query = $query->where('des_nome', 'LIKE', '%'.$request->busca.'%');
    //         });
    //     }

    //     $retorno = $retorno->paginate($request->qtdResultados);

    //     return response()->json([
    //         'pilotos' => $retorno
    //     ]);
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   

        $piloto = new Piloto();
        $piloto->nome = $request->nome;
        $piloto->sobrenome = $request->sobrenome;
        $piloto->user_id = Auth::user()->id;
        $piloto->pais_id = $request->pais_id;
        /* if ($request->has('flg_ativo')) {
            $piloto->flg_ativo = $request->flg_ativo;
        } else {
            $piloto->flg_ativo = 'N';
        } */
        $piloto->flg_ativo = 'S';

        if($request->imagem == ''){
            $newImageName = '';
        } else {
           $newImageName = time().'-'.$request->nome.'.'.$request->imagem->extension();
           $request->imagem->move(public_path('images'), $newImageName);
        }

        $piloto->imagem = $newImageName;

        $piloto->save();

        return redirect()->back()->with('status', 'O piloto '.$piloto->nome.' '.$piloto->sobrenome.' foi registrado');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        //Dados do Piloto 
        $modelPiloto = Piloto::where('id', $id)
                        // ->where('user_id', Auth::user()->id)
                        ->where('user_id', 3)
                        ->first();

        //total de corridas
        $resultados = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
                                    ->join('temporadas', 'temporadas.id', '=', 'corridas.temporada_id')
                                    ->where('resultados.user_id', Auth::user()->id)
                                    ->where('corridas.flg_sprint', 'N')
                                    ->where('piloto_equipes.piloto_id', $modelPiloto->id)
                                    ->orderBy('resultados.id', 'ASC')
                                    ->orderBy('temporadas.id', 'ASC')
                                    ->get();
    
        $totCorridas = 0;
        $totVitorias = 0;
        $totPoles = 0;
        $totPodios = 0;
        $totTopTen = 0;
        $melhorPosicaoLargada = 22;
        $piorPosicaoLargada = 0;
        $melhorPosicaoChegada = 22;
        $piorPosicaoChegada = 0;
        $totTitulos = 0;
        $totAbandonos = 0;
        $gridMedio = 0;
        $mediaChegada = 0;
        $listagemVitorias = [];
        $listagemPolePositions = [];
        $pistasVitorias = [];
        $pistasPoles = [];
        $corridasDisputadas = [];
        $corridaSeguidasSemVencer = 0;
        $corridaSeguidasSemPolePosition = 0;
        
        foreach($resultados as $resultado){

            //ao iniciar o loop eu ja suponho que ele nao venceu e nem fez pole position
            $corridaSeguidasSemVencer++;
            $corridaSeguidasSemPolePosition++;

            if($resultado->pilotoEquipe->piloto->id == $id){
                $totCorridas++;
                $corridasDisputadas[] = $resultado->corrida->pista->nome;
            }

            //calculo do total de vitórias
            if($resultado->chegada == 1){
                if($resultado->pilotoEquipe->piloto->id == $id){
                    $totVitorias++;
                    $listagemVitorias[] = $resultado;
                    $pistasVitorias[] = $resultado->corrida->pista->nome;
                    $corridaSeguidasSemVencer = 0;
                }
            }

            //calculo do total de pole positions
            if($resultado->largada == 1){
                if($resultado->pilotoEquipe->piloto->id == $id){
                    $totPoles++;
                    $listagemPolePositions[] = $resultado;
                    $pistasPoles[] = $resultado->corrida->pista->nome;
                    $corridaSeguidasSemPolePosition = 0;
                }
            }

             //calculo de podios
             if(Resultado::podio($resultado->chegada) == true){
                if($resultado->pilotoEquipe->piloto->id == $id){
                    $totPodios++;
                }
            }

            //calculo de chegadas no top 10
            if(Resultado::topTen($resultado->chegada) == true){
                if($resultado->pilotoEquipe->piloto->id == $id){
                    $totTopTen++;
                }
            }

            //calculo de total de abandonos
            if($resultado->flg_abandono == 'S'){
                if($resultado->pilotoEquipe->piloto->id == $id){
                    $totAbandonos++;
                }
            }

             //calculo da Média de Largada
            if($resultado->pilotoEquipe->piloto->id == $id){
                $gridMedio += $resultado->largada;
            }

             //calculo da Média de Chegada
            if($resultado->pilotoEquipe->piloto->id == $id){
                $mediaChegada += $resultado->chegada;
            }
        

            //calculo de melhor posição de largada
            if($resultado->pilotoEquipe->piloto->id == $id){
                if($resultado->largada <= $melhorPosicaoLargada){
                    $melhorPosicaoLargada = $resultado->largada;
                }    
            }

            //calculo pior posição de largada
            if($resultado->pilotoEquipe->piloto->id == $id){
                if($resultado->largada > $piorPosicaoLargada){
                    $piorPosicaoLargada = $resultado->largada;
                }    
            }

            //calculo melhor posição de chegada
            if($resultado->pilotoEquipe->piloto->id == $id && Resultado::chegadaValida($resultado->chegada) == true){
                if($resultado->chegada <= $melhorPosicaoChegada){
                    $melhorPosicaoChegada = $resultado->chegada;
                }    
            }

            //calculo pior posição de chegada 
            if($resultado->pilotoEquipe->piloto->id == $id && Resultado::chegadaValida($resultado->chegada) == true){
                if($resultado->chegada > $piorPosicaoChegada){
                    $piorPosicaoChegada = $resultado->chegada;
                }    
            }
        }

        //calculo de títulos 
        $totTitulos = count(Titulo::join('piloto_equipes', 'piloto_equipes.id', '=', 'titulos.pilotoEquipe_id')
                                ->where('titulos.user_id', Auth::user()->id)
                                ->where('piloto_equipes.piloto_id', $id)
                                ->get());
    
        //Total de Pontos tem cálculo diferente pois envolve sprints
        $resultados = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
                                    ->where('resultados.user_id', Auth::user()->id)
                                    ->where('piloto_equipes.piloto_id', $modelPiloto->id)
                                    ->get();
                              
        $totPontos = 0;
        foreach($resultados as $resultado){
        if($resultado->pilotoEquipe->piloto->id == $id){
                $totPontos += $resultado->pontuacao;
            }
        }

        $pilotoEquipe = PilotoEquipe::where('piloto_id', $id)
                                    ->where('user_id', Auth::user()->id)
                                    ->get();
    
        $ids = [];
        foreach($pilotoEquipe as $pilotoId){
            array_push($ids, $pilotoId->id);
        }

       $resultado = Corrida::where('user_id', Auth::user()->id)
                                ->where('flg_sprint', 'N')
                                ->where('volta_rapida', '!=', null)
                                ->whereIn('volta_rapida', $ids)
                                ->get();
    
        $totVoltasRapidas = count($resultado);    
        
        //historico de equipes
        $equipes = PilotoEquipe::where('user_id', Auth::user()->id)->where('piloto_id', $id)->get();
        
        //devolve os anos em que o piloto esteve inscrito para correr - Dinamicamente com base nos registros da tabela piloto_equipes
        $temporadasDisputadas = [];
        //calculo da pontuação por temporada disputada
        $pontuacaoPorTemporada = [];
        foreach($pilotoEquipe as $pilotoTemporada){
            $retorno =  DB::select('select
            sum(pontuacao) as totPontos
            from resultados
            join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
            join equipes on equipes.id = piloto_equipes.equipe_id
            join corridas on corridas.id = resultados.corrida_id
            join pilotos on piloto_equipes.piloto_id = pilotos.id
            join temporadas on temporadas.id = corridas.temporada_id
            where temporadas.ano_id = '.$pilotoTemporada->ano_id.'
            and resultados.user_id = '. Auth::user()->id.'
            and piloto_id = '.$pilotoTemporada->piloto_id.'');
           
            array_push($pontuacaoPorTemporada, $retorno[0]->totPontos);
            array_push($temporadasDisputadas, $pilotoTemporada->ano->ano);
        }

        //Calculo final da média de largada e chegada
        if($totCorridas > 0){
            $gridMedio = round($gridMedio/$totCorridas);
            $mediaChegada = round($mediaChegada/$totCorridas);
        }

        // $resultadosPorCorrida = Resultado::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->get();
        $resultadosPorCorrida = Resultado::join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                ->join('corridas', 'resultados.corrida_id', '=', 'corridas.id')
                                ->join('temporadas', 'temporadas.id', '=', 'corridas.temporada_id')
                                ->where('resultados.user_id', Auth::user()->id)
                                ->where('piloto_equipes.piloto_id', $modelPiloto->id)
                                ->orderBy('temporadas.id', 'DESC')
                                ->orderBy('resultados.id', 'DESC')
                                ->paginate(30);

        // sleep(1);
        // $time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        // $tempoExecucao =  "Tempo de execução: ".$time;
        // dd($tempoExecucao);

        // dd($temporadasDisputadas);

        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();
        // $temporadas = Temporada::join('anos', 'anos.id', '=', 'temporadas.ano_id')
        //                         ->where('temporadas.user_id', Auth::user()->id)
        //                         ->whereIn('anos.ano', $temporadasDisputadas)
        //                         ->get();

                                // dd($temporadas);

        $corridasPorEquipe = Resultado::join('corridas', 'resultados.corrida_id', '=', 'corridas.id')
                                                ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                                ->join('equipes', 'equipes.id', 'piloto_equipes.equipe_id')
                                                ->where('resultados.user_id', Auth::user()->id)
                                                ->where('piloto_equipes.piloto_id', $modelPiloto->id)
                                                ->where('corridas.flg_sprint', '<>', 'S')
                                                ->groupBy('piloto_equipes.equipe_id')
                                                ->select('piloto_equipes.equipe_id', 'equipes.nome','equipes.imagem', DB::raw('COUNT(*) as quantidade'))
                                                ->orderBy('quantidade', 'DESC')
                                                ->get();

        $pistasEmQueOPilotoNaoVenceu = array_diff($corridasDisputadas, $pistasVitorias);
        // $pistasEmQueOPilotoNaoVenceu = array_unique($pistasEmQueOPilotoNaoVenceu);

        $pistasEmQueOPilotoNaoVenceu = array_count_values($pistasEmQueOPilotoNaoVenceu);

        arsort($pistasEmQueOPilotoNaoVenceu);

        $pistasEmQueOPilotoNaoFoiPolePosition = array_diff($corridasDisputadas, $pistasPoles);
        // $pistasEmQueOPilotoNaoFoiPolePosition = array_unique($pistasEmQueOPilotoNaoFoiPolePosition);

        $pistasEmQueOPilotoNaoFoiPolePosition = array_count_values($pistasEmQueOPilotoNaoFoiPolePosition);

        arsort($pistasEmQueOPilotoNaoFoiPolePosition);

        $vitoriasPorPista = array_count_values($pistasVitorias);
        arsort($vitoriasPorPista);

        $polesPorPista = array_count_values($pistasPoles);
        arsort($polesPorPista);

        return view('site.pilotos.show', compact('modelPiloto','totTitulos','totAbandonos', 'totCorridas', 'totVitorias','totPontos', 'totPodios', 'totTopTen','piorPosicaoLargada','totPoles', 'melhorPosicaoLargada','melhorPosicaoChegada', 'piorPosicaoChegada','totVoltasRapidas', 'equipes','mediaChegada','gridMedio','temporadasDisputadas','pontuacaoPorTemporada','resultadosPorCorrida','temporadas','listagemVitorias', 'listagemPolePositions', 'corridasPorEquipe','vitoriasPorPista', 'polesPorPista','pistasEmQueOPilotoNaoVenceu','pistasEmQueOPilotoNaoFoiPolePosition', 'corridaSeguidasSemVencer', 'corridaSeguidasSemPolePosition'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = Piloto::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $paises = Pais::where('user_id', Auth::user()->id)->get();
        return view('site.pilotos.form', compact('model','paises'));
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
        $piloto = Piloto::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $piloto->nome = $request->nome;
        $piloto->sobrenome = $request->sobrenome;
        $piloto->user_id = Auth::user()->id;
        $piloto->pais_id = $request->pais_id;
        if ($request->has('flg_ativo')) {
            $piloto->flg_ativo = $request->flg_ativo;
        } else {
            $piloto->flg_ativo = 'N';
        }

        //se tiver foto criar span no form pra nao precisar alterar,  caso queira, apagar a antiga e colocar outra
        if($request->imagem == ''){
            $newImageName = '';
        } else {
            if(file_exists(public_path('images/'.$piloto->imagem))){
                if($piloto->imagem != null){
                    unlink(public_path('images/'.$piloto->imagem));
                }
            }
           $newImageName = time().'-'.$request->nome.'.'.$request->imagem->extension();
           $request->imagem->move(public_path('images'), $newImageName);
           $piloto->imagem = $newImageName;
        }

        $piloto->update();

        return redirect()->route('pilotos.index')->with('status', 'O piloto '.$piloto->nomeCompleto().' foi editado');
    }

    public function comparativo(){

        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();
        
        $temporada_id = 1;
        $usuario_id = 1;
        $piloto1_id = 4; //verstappen
        $piloto2_id = 23; //perez

        //Variaveis com Resultados da Comparação
        $piloto1TotVitorias = 0;
        $piloto2TotVitorias = 0;
        $piloto1TotPolePositions = 0;
        $piloto2TotPolePositions = 0;
        $piloto1Largada = 0;
        $piloto2Largada = 0;
        $piloto1Chegada = 0;
        $piloto2Chegada = 0;
        $piloto1TotPodios = 0;
        $piloto2TotPodios = 0;
        $piloto1TotAbandonos = 0;
        $piloto2TotAbandonos = 0;
        $piloto1TotPontos = 0;
        $piloto2TotPontos = 0;
        $piloto1TotVoltasRapidas = 0;
        $piloto2TotVoltasRapidas = 0;
        $piloto1MelhorChegada = 22;
        $piloto2MelhorChegada = 22;
        $piloto1PiorChegada = 0;
        $piloto2PiorChegada = 0;

        $piloto1MelhorLargada = 22;
        $piloto2MelhorLargada = 22;
        $piloto1PiorLargada = 0;
        $piloto2PiorLargada = 0;

        //temporariamente; Depois pegar só campos que interessam no dadospilotos e concatenar nomes
        //$pilotos = Piloto::whereIn('id', [$piloto1_id, $piloto2_id])->get();

        $dadosPiloto1 =  DB::select('select
                                     * 
                                    from resultados
                                    join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                    join equipes on equipes.id = piloto_equipes.equipe_id
                                    join corridas on corridas.id = resultados.corrida_id
                                    join pilotos on piloto_equipes.piloto_id = pilotos.id
                                    join temporadas on temporadas.id = corridas.temporada_id
                                    where temporadas.id = '.$temporada_id.'
                                    and resultados.user_id = '.$usuario_id.'
                                    and corridas.flg_sprint = "N"
                                    and piloto_id = '.$piloto1_id.'');
                                
        $dadosPiloto2 =  DB::select('select 
                                    *
                                    from resultados
                                    join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                    join equipes on equipes.id = piloto_equipes.equipe_id
                                    join corridas on corridas.id = resultados.corrida_id
                                    join pilotos on piloto_equipes.piloto_id = pilotos.id
                                    join temporadas on temporadas.id = corridas.temporada_id
                                    where temporadas.id = '.$temporada_id.'
                                    and resultados.user_id = '.$usuario_id.'
                                    and corridas.flg_sprint = "N"
                                    and piloto_id = '.$piloto2_id.'');
        
        //loop que faz os comparativos
        // dd($dadosPiloto1, $dadosPiloto2);

        foreach($dadosPiloto1 as $key1 => $piloto1){
           if(isset($dadosPiloto2[$key1]) && $piloto1->corrida_id == $dadosPiloto2[$key1]->corrida_id){
                $piloto1->largada < $dadosPiloto2[$key1]->largada ?  $piloto1Largada++ : $piloto2Largada++;
                $piloto1->chegada < $dadosPiloto2[$key1]->chegada ?  $piloto1Chegada++ : $piloto2Chegada++;
                $piloto1->chegada <= 3 ? $piloto1TotPodios++ : "";
                $piloto1->flg_abandono == 'S' ? $piloto1TotAbandonos++ : '';
                $piloto1->volta_rapida == $piloto1->pilotoEquipe_id ? $piloto1TotVoltasRapidas++ : '';
                $piloto1->chegada == 1 ? $piloto1TotVitorias++ : "";
                $piloto1->largada == 1 ? $piloto1TotPolePositions++ : "";
                $piloto1->chegada < $piloto1MelhorChegada ? $piloto1MelhorChegada = $piloto1->chegada : "";
                $piloto1->chegada > $piloto1PiorChegada ? $piloto1PiorChegada = $piloto1->chegada : "";
                $piloto1->largada > $piloto1PiorLargada ? $piloto1PiorLargada = $piloto1->largada : "";
                $piloto1->largada < $piloto1MelhorLargada ? $piloto1MelhorLargada = $piloto1->largada : "";
           }   
        }

        //loop do segundo piloto para dados especificos dele. Os do primeiro piloto ja são tratados como os dados base do loop comparativo
        foreach($dadosPiloto2 as $key1 => $piloto2){
            if(isset($dadosPiloto1[$key1]) && $piloto2->corrida_id == $dadosPiloto1[$key1]->corrida_id){
                $piloto2->chegada <= 3 ? $piloto2TotPodios++ : "";
                $piloto2->flg_abandono == 'S' ? $piloto2TotAbandonos++ : '';
                $piloto2->volta_rapida == $piloto2->pilotoEquipe_id ? $piloto2TotVoltasRapidas++ : '';
                $piloto2->chegada == 1 ? $piloto2TotVitorias++ : "";
                $piloto2->largada == 1 ? $piloto2TotPolePositions++ : "";
                $piloto2->chegada < $piloto2MelhorChegada ? $piloto2MelhorChegada = $piloto2->chegada : "";
                $piloto2->chegada > $piloto2PiorChegada ? $piloto2PiorChegada = $piloto2->chegada : "";
                $piloto2->largada > $piloto2PiorLargada ? $piloto2PiorLargada = $piloto2->largada : "";
                $piloto2->largada < $piloto2MelhorLargada ? $piloto2MelhorLargada = $piloto2->largada : "";
            }
        }

        //calculo de pontuação é em outro loop pois utiliza dados da corrida sprint também
        $piloto1TotPontos = DB::select('select
                                        sum(pontuacao) as totPontos
                                        from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join pilotos on piloto_equipes.piloto_id = pilotos.id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        where temporadas.id = '.$temporada_id.'
                                        and resultados.user_id = '.$usuario_id.'
                                        and piloto_id = '.$piloto1_id.'');

        $piloto1TotPontos = $piloto1TotPontos[0]->totPontos;

        $piloto2TotPontos = DB::select('select
                                        sum(pontuacao) as totPontos
                                        from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join pilotos on piloto_equipes.piloto_id = pilotos.id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        where temporadas.id = '.$temporada_id.'
                                        and resultados.user_id = '.$usuario_id.'
                                        and piloto_id = '.$piloto2_id.'');

        $piloto2TotPontos = $piloto2TotPontos[0]->totPontos;

        $modelPilotos = Piloto::where('user_id', Auth::user()->id)
                                ->get();


        return view('site.pilotos.comparativo', compact('piloto1Largada','piloto2Largada','piloto1Chegada','piloto2Chegada','dadosPiloto1', 'dadosPiloto2','piloto1TotPodios','piloto2TotPodios','piloto1TotAbandonos','piloto2TotAbandonos','piloto1TotPontos','piloto2TotPontos','piloto1TotVoltasRapidas','piloto2TotVoltasRapidas','piloto1TotVitorias','piloto2TotVitorias','piloto1TotPolePositions','piloto2TotPolePositions','piloto1MelhorChegada','piloto2MelhorChegada','piloto1PiorChegada','piloto2PiorChegada','piloto1MelhorLargada','piloto2MelhorLargada','piloto1PiorLargada','piloto2PiorLargada','modelPilotos','temporadas'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   
        $id = $request-> piloto_id;
        $piloto = Piloto::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $piloto->delete();

        //apagando a foto do usuário
        if(file_exists(public_path('images/'.$piloto->imagem))){
            unlink(public_path('images/'.$piloto->imagem));
        }
    
        return redirect()->route('pilotos.index')->with('status', 'O piloto '.$piloto->nomeCompleto().' foi excluído com sucesso');
    }

    public function export($id) 
    {   

        $modelPiloto = Piloto::where('id', $id)
                                ->where('user_id', Auth::user()->id)
                                ->first();

        $nomeArquivo = $modelPiloto->nome." ".$modelPiloto->sobrenome."_".date('Y');


        return Excel::download(new PilotosExport($id), $nomeArquivo.'.xlsx');
    }

    public function relacaoForcasIndex(){
        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();

        return view('site.pilotos.relacaoForcas.index', compact('temporadas'));
    }

    public function relacaoForcasIndexListagemPilotos($temporada_id){
        
        $temporada = Temporada::find($temporada_id);
       
        $pilotos = PilotoEquipe::with('piloto')
                        ->where('user_id', Auth::user()->id)
                        ->where('ano_id', $temporada->ano->id)
                        ->orderBy('id')
                        ->distinct('piloto_id')
                        ->get();

        return view('site.pilotos.relacaoForcas.listagemPilotos', compact('pilotos', 'temporada'));

    }

    public function relacaoForcasEdit($ano_id, $piloto_id){

        $piloto = Piloto::find($piloto_id);

        $model = ForcaPiloto::where('piloto_id', $piloto_id)
                            ->where('ano_id', $ano_id)
                            ->where('user_id', Auth::user()->id)
                            ->first();

        return view('site.pilotos.relacaoForcas.form', compact('piloto', 'ano_id', 'model'));
    }

    public function relacaoForcasUpdate(Request $request){

        $model = ForcaPiloto::where('piloto_id', $request->piloto_id)
                            ->where('ano_id', $request->ano_id)
                            ->where('user_id', Auth::user()->id)
                            ->first();

        if(isset($model)){
            $model->forca = $request->forca;
            $model->update();
        }else{
            $newModel = new ForcaPiloto;
            $newModel->piloto_id = $request->piloto_id;
            $newModel->ano_id = $request->ano_id;
            $newModel->user_id = Auth::user()->id;
            $newModel->forca = $request->forca;
            $newModel->save();
        }
               
        return redirect()->back()->with('status', 'Salvo com sucesso');
    }
}
