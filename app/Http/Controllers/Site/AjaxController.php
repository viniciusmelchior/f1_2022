<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Corrida;
use App\Models\Site\Equipe;
use App\Models\Site\Pais;
use App\Models\Site\Piloto;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use App\Models\Site\Titulo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{
    public function classificacaoPorTemporada(Request $request){
        
        $usuario = Auth::user()->id; 
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $request->temporada_id)->first();

        $resultadosPilotos = DB::select('select piloto_id, concat(pilotos.nome, " ", pilotos.sobrenome) as nome, equipes.nome as equipe, equipes.imagem, sum(pontuacao) as total from resultados
                                            join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                            join pilotos on pilotos.id = piloto_equipes.piloto_id
                                            join equipes on equipes.id = piloto_equipes.equipe_id
                                            join corridas on corridas.id = resultados.corrida_id
                                            join temporadas on temporadas.id = corridas.temporada_id
                                            where temporadas.id = '.$request->temporada_id.'
                                            and resultados.user_id = '.$usuario.'
                                            group by piloto_equipes.piloto_id
                                            order by total desc');
            
        $resultadosEquipes = DB::select('select equipe_id, equipes.nome as nome, sum(pontuacao) as total from resultados
                            join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                            join equipes on equipes.id = piloto_equipes.equipe_id
                            join corridas on corridas.id = resultados.corrida_id
                            join temporadas on temporadas.id = corridas.temporada_id
                            where temporadas.id = '.$request->temporada_id.'
                            and resultados.user_id = '.$usuario.'
                            group by piloto_equipes.equipe_id
                            order by total desc');


        return response()->json([
            'message' => 'Chegamos no Controller',
            'temporada' => $temporada,
            'resultadosPilotos' => $resultadosPilotos,
            'resultadosEquipes' => $resultadosEquipes,
        ]);
    }

    public function classificacaoHistorica(){
        
    }

    /**
     * Função que traz os dados dos comparativos entre os pilotos
     */
    public function comparativos(Request $request){

        $request = $request->post();
        // dd($request, Auth::user()->id);

        if($request['temporada_id'] == null){
           //$temporada_id = [1,2,3,4,5,6,7,8,9,10];
        }else{
            $temporada_id = $request['temporada_id'];
        }
        
        //dados base para montagem das pesquisas
        // $temporada_id = 1;
        $usuario_id = Auth::user()->id;
        $piloto1_id = $request['pilotosId'][0]; //verstappen
        $piloto2_id = $request['pilotosId'][1]; //perez

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

        //Só contar nas corridas em que ambos correram
        $corridasEmComum = [];

        foreach($dadosPiloto1 as $dadoPiloto1){
            $corridasEmComum[] = $dadoPiloto1->corrida_id;
        }

        foreach($dadosPiloto2 as $dadoPiloto2){
            $corridasEmComum[] = $dadoPiloto2->corrida_id;
        }

        $countedValues = array_count_values($corridasEmComum);

        // Filtra os valores que ocorrem mais de uma vez
        $duplicatedValues = array_filter($countedValues, function ($count) {
            return $count > 1;
        });

        $corridasEmComum = array_keys($duplicatedValues);

         //loop que faz os comparativos
        foreach($dadosPiloto1 as $key1 => $piloto1){

                if(in_array($piloto1->corrida_id, $corridasEmComum)){
                    $newResultado = Resultado::where('corrida_id', $piloto1->corrida_id)
                                                ->join('piloto_equipes', 'pilotoEquipe_id', 'piloto_equipes.id')
                                                ->whereIn('piloto_id', [$piloto1_id, $piloto2_id])
                                                ->get();

                    //comparação de resultados de largada
                    if($newResultado[0]->largada < $newResultado[1]->largada){
                        if($newResultado[0]->piloto_id == $piloto1_id){
                            $piloto1Largada++;
                        }else{
                            $piloto2Largada++;
                        }
                    }

                    if($newResultado[0]->largada > $newResultado[1]->largada){
                        if($newResultado[1]->piloto_id == $piloto1_id){
                            $piloto1Largada++;
                        }else{
                            $piloto2Largada++;
                        }
                    }

                    //comparação dos resultados de chegada
                    if($newResultado[0]->chegada < $newResultado[1]->chegada){
                        if($newResultado[0]->piloto_id == $piloto1_id){
                            $piloto1Chegada++;
                        }else{
                            $piloto2Chegada++;
                        }
                    }

                    if($newResultado[0]->chegada > $newResultado[1]->chegada){
                        if($newResultado[1]->piloto_id == $piloto1_id){
                            $piloto1Chegada++;
                        }else{
                            $piloto2Chegada++;
                        }
                    }
                    
                }

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
 
         //loop do segundo piloto para dados especificos dele. Os do primeiro piloto ja são tratados como os dados base do loop comparativo
         foreach($dadosPiloto2 as $key1 => $piloto2){
            
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
        
        return response()->json([
            'message' => 'ok',
            'request' => $request,
            'dadosPiloto1' => $dadosPiloto1,
            'dadosPiloto2' => $dadosPiloto2,
            'piloto1TotPontos' => $piloto1TotPontos,
            'piloto2TotPontos' => $piloto2TotPontos,
            'piloto1TotVitorias' => $piloto1TotVitorias,
            'piloto2TotVitorias' => $piloto2TotVitorias,
            'piloto1TotPolePositions' => $piloto1TotPolePositions,
            'piloto2TotPolePositions' => $piloto2TotPolePositions,
            'piloto1Chegada' => $piloto1Chegada,
            'piloto2Chegada' => $piloto2Chegada,
            'piloto1Largada' => $piloto1Largada,
            'piloto2Largada' => $piloto2Largada,
            'piloto1TotPodios' => $piloto1TotPodios,
            'piloto2TotPodios' => $piloto2TotPodios,
            'piloto1TotAbandonos' => $piloto1TotAbandonos,
            'piloto2TotAbandonos' => $piloto2TotAbandonos,
            'piloto1TotVoltasRapidas' => $piloto1TotVoltasRapidas,
            'piloto2TotVoltasRapidas' => $piloto2TotVoltasRapidas,
            'piloto1MelhorChegada' => $piloto1MelhorChegada,
            'piloto2MelhorChegada' => $piloto2MelhorChegada,
            'piloto1PiorChegada' => $piloto1PiorChegada,
            'piloto2PiorChegada' => $piloto2PiorChegada,
            'piloto1MelhorLargada' => $piloto1MelhorLargada,
            'piloto2MelhorLargada' => $piloto2MelhorLargada,
            'piloto1PiorLargada' => $piloto1PiorLargada,
            'piloto2PiorLargada' => $piloto2PiorLargada,
        ]);
    }

    public function getPilotosPorTemporada(Request $request){

        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $request->temporada_id)->first();
        $pilotoEquipes = PilotoEquipe::where('user_id', Auth::user()->id)->where('ano_id', $temporada->ano_id)->get();
        $pilotos = [];

        foreach($pilotoEquipes as $piloto){
            array_push($pilotos, $piloto->piloto);
        }

        return response()->json([
            'message' => 'Chegamos no getPilotosPorTemporada',
            'pilotos' => $pilotos
        ]);
    }

    public function estudos(){
       /* $paises = Pais::where('user_id', Auth::user()->id)->get();
       foreach($paises as $pais){
        // echo('<h3>'.$pais->des_nome.'</h3>'.' =>'. $pais->pistas. '<br>');
        echo('<h3>'.$pais->des_nome.'</h3>'.' =>'. count($pais->pistas). '<br>');
        echo('------------------------------------------------------------');
       } */
    //    $pilotoEquipe = PilotoEquipe::where('user_id', Auth::user()->id)->get();

    //    $paises = Pais::where('user_id', Auth::user()->id)
    //                 // ->whereIn('id', [7,15,17,18,6,19,10])
    //                 ->orderBy('des_nome')
    //                 ->get();

    //     //possível fazer assim, acrescentando o join após a consulta inicial e dando get na hora de puxar os dados
    //     $dados = DB::table('titulos')
    //                 ->select('*')
    //                 ->join('piloto_equipes', function($join){
    //                     $join->on('piloto_equipes.id', '=', 'titulos.pilotoEquipe_id');
    //                 });
                   
    //     $dados->join('pilotos', function($join){
    //         $join->on('pilotos.id', '=', 'piloto_equipes.piloto_id');
    //     });

    //     //ou encadeando e dando get no final . 
    //     $dados2 = DB::table('titulos')
    //                 ->select('*')
    //                 ->join('piloto_equipes', function($join){
    //                     $join->on('piloto_equipes.id', '=', 'titulos.pilotoEquipe_id');
    //                 })
    //                 ->join('pilotos', function($join){
    //                     $join->on('pilotos.id', '=', 'piloto_equipes.piloto_id')
    //                         ->where('pilotos.id','=', 4);
    //                 })->get();

    //     // dd($dados->get(), $dados2);

    //    return view('estudos', compact('pilotoEquipe', 'paises'));
    }

    public function ajaxGetStatsEquipePorTemporada(Request $request){
       
        $temporada_id = $request->temporada_id;
        $id = $request->equipe_id;

        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }
        $modelEquipe = Equipe::where('id', $id)
                                ->where('user_id', Auth::user()->id)
                                ->first();

        $resultados = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                            ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
                            ->where('resultados.user_id', Auth::user()->id)
                            ->where('corridas.flg_sprint', 'N')
                            ->where('piloto_equipes.equipe_id', $modelEquipe->id)
                            ->where('corridas.temporada_id', $operadorConsulta, $condicao)
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

        $totVoltasRapidas = 0;
        $totDobradinhas = 0;

        $possiveisDobradinhas = [];

        //Consideramos apenas o id da equipe pois levamos em consideração a junção do resultado da dupla de pilotos
        foreach($resultados as $resultado){

            //total de largadas
            if($resultado->pilotoEquipe->equipe->id == $id){
                $totCorridas++;
            }

            //calculo do total de vitórias
            if($resultado->chegada == 1){
                if($resultado->pilotoEquipe->equipe->id == $id){
                    $possiveisDobradinhas[] = $resultado;
                    $totVitorias++;
                }
            }

            if($resultado->chegada == 2){
                if($resultado->pilotoEquipe->equipe->id == $id){
                    $possiveisDobradinhas[] = $resultado;
                }
            }

             //calculo do total de pole positions
             if($resultado->largada == 1){
                if($resultado->pilotoEquipe->equipe->id == $id){
                    $totPoles++;
                }
            }

             //calculo de podios
             if($resultado->chegada <= 3){
                if($resultado->pilotoEquipe->equipe->id == $id){
                    $totPodios++;
                }
            }

             //calculo de chegadas no top 10
             if($resultado->chegada <= 10){
                if($resultado->pilotoEquipe->equipe->id == $id){
                    $totTopTen++;
                }
            }

             //calculo de total de abandonos
             if($resultado->flg_abandono == 'S'){
                if($resultado->pilotoEquipe->equipe->id == $id){
                    $totAbandonos++;
                }
            }

             //calculo da Média de Largada
            if($resultado->pilotoEquipe->equipe->id == $id){
                $gridMedio += $resultado->largada;
                // $gridMedio = $gridMedio/$totCorridas;
            }

             //calculo da Média de Chegada
            if($resultado->pilotoEquipe->equipe->id == $id){
                $mediaChegada += $resultado->chegada;
                // $mediaChegada = $mediaChegada/$totCorridas;
            }

            //calculo de melhor posição de largada
            if($resultado->pilotoEquipe->equipe->id == $id){
                if($resultado->largada <= $melhorPosicaoLargada){
                    $melhorPosicaoLargada = $resultado->largada;
                }    
            }

            //calculo pior posição de largada
            if($resultado->pilotoEquipe->equipe->id == $id){
                if($resultado->largada > $piorPosicaoLargada){
                    $piorPosicaoLargada = $resultado->largada;
                }    
            }

            //calculo melhor posição de chegada
            if($resultado->pilotoEquipe->equipe->id == $id){
                if($resultado->chegada <= $melhorPosicaoChegada){
                    $melhorPosicaoChegada = $resultado->chegada;
                }    
            }

            //calculo pior posição de chegada 
            if($resultado->pilotoEquipe->equipe->id == $id){
                if($resultado->chegada > $piorPosicaoChegada){
                    $piorPosicaoChegada = $resultado->chegada;
                }    
            }
        }

        $idCorridasPossiveisDobradinhas = [];

        foreach($possiveisDobradinhas as $possivelDobradinha){
            if(!in_array($possivelDobradinha->corrida_id,$idCorridasPossiveisDobradinhas)){
                $idCorridasPossiveisDobradinhas[] = $possivelDobradinha->corrida_id;
            }
        }

    
        foreach($idCorridasPossiveisDobradinhas as $item){
            $newCorrida = Corrida::find($item);
            // $resultado = Resultado::where('corrida_id', $newCorrida->id)->where('chegada', '<=', 2 )->get();

            $primeiro = Resultado::where('corrida_id', $newCorrida->id)
                                    ->where('chegada', 1 )
                                    ->where('user_id', Auth::user()->id)
                                    ->first();

            $segundo = Resultado::where('corrida_id', $newCorrida->id)
                                    ->where('chegada', 2)
                                    ->where('user_id', Auth::user()->id)
                                    ->first();

            //compara os equipe id, se for igual soma
            if($primeiro->pilotoEquipe->equipe->id == $segundo->pilotoEquipe->equipe->id){
                if($primeiro->pilotoEquipe->equipe->id == $id){
                    $totDobradinhas++;
                }
            }
        }

        //calculo de titulo de construtores
        $totTitulos = count(Titulo::where('equipe_id', $id)->where('user_id', Auth::user()->id)->get());

        //calculo do titulo de pilotos
        $totTitulosPilotos = count(Titulo::join('piloto_equipes', 'piloto_equipes.id', '=', 'titulos.pilotoEquipe_id')
                                            ->where('titulos.user_id', Auth::user()->id)
                                            ->where('piloto_equipes.equipe_id', $id)
                                            ->get());

        //Total de Pontos tem cálculo diferente pois envolve sprints   
        $resultados = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                ->where('resultados.user_id', Auth::user()->id)
                                ->where('corridas.temporada_id', $operadorConsulta, $condicao)
                                ->get();                  
        $totPontos = 0;
        foreach($resultados as $resultado){
        if($resultado->pilotoEquipe->equipe->id == $id){
                $totPontos += $resultado->pontuacao;
            }
        }

        $pilotoEquipe = PilotoEquipe::where('equipe_id', $id)
                                    ->where('user_id', Auth::user()->id)
                                    ->get();

        $ids = [];
        foreach($pilotoEquipe as $equipeId){
            array_push($ids, $equipeId->id);
        }

        $resultado = Corrida::where('user_id', Auth::user()->id)
                            ->where('flg_sprint', 'N')
                            ->where('volta_rapida', '!=', null)
                            ->whereIn('volta_rapida', $ids)
                            ->where('temporada_id', $operadorConsulta, $condicao)
                            ->get();

        $totVoltasRapidas = count($resultado); 

         //devolve os anos em que o piloto esteve inscrito para correr - Dinamicamente com base nos registros da tabela piloto_equipes
         $temporadasDisputadas = [];
         //calculo da pontuação por temporada disputada
         $pontuacaoPorTemporada = [];

        //temporadas em que a equipes está inscrita 
        $temporadasEquipe = PilotoEquipe::where('equipe_id', $id)->groupBy('ano_id')->get();
        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();
        
        foreach($temporadasEquipe as $equipeTemporada){
            $retorno =  DB::select('select
            sum(pontuacao) as totPontos, temporadas.ano_id
            from resultados
            join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
            join equipes on equipes.id = piloto_equipes.equipe_id
            join corridas on corridas.id = resultados.corrida_id
            join pilotos on piloto_equipes.piloto_id = pilotos.id
            join temporadas on temporadas.id = corridas.temporada_id
            where temporadas.ano_id = '.$equipeTemporada->ano_id.'
            and resultados.user_id = '. Auth::user()->id.'
            and equipe_id = '.$equipeTemporada->equipe_id.'');
        
            array_push($pontuacaoPorTemporada, $retorno[0]->totPontos);
            array_push($temporadasDisputadas, $equipeTemporada->ano->ano);
        }

        if($totCorridas > 0){
            $gridMedio = round($gridMedio/$totCorridas);
            $mediaChegada = round($mediaChegada/$totCorridas);
        }

        // return view('site.equipes.show', compact('modelEquipe','totTitulos', 'totCorridas','totTitulosPilotos', 'totVitorias','totPontos', 'totPodios', 'totTopTen','piorPosicaoLargada','totPoles', 'melhorPosicaoLargada','melhorPosicaoChegada', 'piorPosicaoChegada','totVoltasRapidas','temporadasDisputadas','pontuacaoPorTemporada','temporadas'));

        return response()->json([
            'message' => 'OK',
            'totVitorias' => $totVitorias,
            'totCorridas' => $totCorridas,
            'totPontos' => $totPontos,
            'totPodios' => $totPodios,
            'totTopTen' => $totTopTen,
            'piorPosicaoLargada' => $piorPosicaoLargada,
            'totPoles' => $totPoles,
            'melhorPosicaoLargada' => $melhorPosicaoLargada,
            'melhorPosicaoChegada' => $melhorPosicaoChegada,
            'piorPosicaoChegada' => $piorPosicaoChegada,
            'totVoltasRapidas' => $totVoltasRapidas,
            'totAbandonos' => $totAbandonos,
            'gridMedio' => $gridMedio,
            'mediaChegada' => $mediaChegada,
            'totDobradinhas' => $totDobradinhas
        ]);

    }

    public function ajaxGetStatsPilotoPorTemporada(Request $request){
        //Dados do Piloto 
        $temporada_id = $request->temporada_id;
        $id = $request->piloto_id;

        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }
        $modelPiloto = Piloto::where('id', $id)
                                ->where('user_id', Auth::user()->id)
                                ->first();

        $resultados = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                            ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
                            ->where('resultados.user_id', Auth::user()->id)
                            ->where('corridas.flg_sprint', 'N')
                            ->where('piloto_equipes.piloto_id', $modelPiloto->id)
                            ->where('corridas.temporada_id', $operadorConsulta, $condicao)
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
        
        foreach($resultados as $resultado){
            if($resultado->pilotoEquipe->piloto->id == $id){
                $totCorridas++;
            }

            //calculo do total de vitórias
            if($resultado->chegada == 1){
                if($resultado->pilotoEquipe->piloto->id == $id){
                    $totVitorias++;
                }
            }

            //calculo do total de pole positions
            if($resultado->largada == 1){
                if($resultado->pilotoEquipe->piloto->id == $id){
                    $totPoles++;
                }
            }

             //calculo de podios
             if($resultado->chegada <= 3){
                if($resultado->pilotoEquipe->piloto->id == $id){
                    $totPodios++;
                }
            }

            //calculo de chegadas no top 10
            if($resultado->chegada <= 10){
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
            if($resultado->pilotoEquipe->piloto->id == $id){
                if($resultado->chegada <= $melhorPosicaoChegada){
                    $melhorPosicaoChegada = $resultado->chegada;
                }    
            }

            //calculo pior posição de chegada 
            if($resultado->pilotoEquipe->piloto->id == $id){
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
                                    ->where('corridas.temporada_id', $operadorConsulta, $condicao)
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
                                ->where('temporada_id', $operadorConsulta, $condicao)
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

        return response()->json([
            'message' => 'OK',
            'totVitorias' => $totVitorias,
            'totCorridas' => $totCorridas,
            'totPontos' => $totPontos,
            'totPodios' => $totPodios,
            'totTopTen' => $totTopTen,
            'piorPosicaoLargada' => $piorPosicaoLargada,
            'totPoles' => $totPoles,
            'melhorPosicaoLargada' => $melhorPosicaoLargada,
            'melhorPosicaoChegada' => $melhorPosicaoChegada,
            'piorPosicaoChegada' => $piorPosicaoChegada,
            'totVoltasRapidas' => $totVoltasRapidas,
            'totAbandonos' => $totAbandonos,
            'gridMedio' => $gridMedio,
            'mediaChegada' => $mediaChegada
        ]);  
    }
}
