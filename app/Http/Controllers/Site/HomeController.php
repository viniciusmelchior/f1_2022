<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(){

        /**Vitoria dos Pilotos */
        $vitoriasPiloto = Resultado::where('resultados.user_id', Auth::user()->id)->where('chegada', 1)
                                    ->join('corridas', function($join){
                                        $join->on('corridas.id', '=', 'resultados.corrida_id')
                                            ->where('corridas.temporada_id', ">", 0);
                                    })->get();

        $vencedores = [];
        foreach($vitoriasPiloto as $item){
            if($item->corrida->flg_sprint == 'N'){
                array_push($vencedores, $item->pilotoEquipe->piloto->nomeCompleto());
            }
        }

        $totVitoriasPorPiloto = array_count_values($vencedores);
        arsort($totVitoriasPorPiloto);

        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();

         /**Vitoria das Equipes */
        $vitoriaEquipes = Resultado::where('user_id', Auth::user()->id)->where('chegada', 1)->get();
        $vencedores = [];
        foreach($vitoriaEquipes as $item){
            if($item->corrida->flg_sprint == 'N'){
                array_push($vencedores, $item->pilotoEquipe->equipe->nome);
            }
        }

        $totVitoriasPorEquipe = array_count_values($vencedores);
        arsort($totVitoriasPorEquipe);

        /**Pole Position dos Pilotos */
        $polePilotos = Resultado::where('user_id', Auth::user()->id)->where('largada', 1)->get();
                        $poles = [];
                        foreach($polePilotos as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($poles, $item->pilotoEquipe->piloto->nomeCompleto());
                            }
                        }

                        $totPolesPorPiloto = array_count_values($poles);
                        arsort($totPolesPorPiloto);

        /**Retorna dados para montar a classificação geral histórica de pilotos e equipes */

        $usuario = Auth::user()->id; 

        $resultadosPilotosGeral = DB::select('select piloto_id, concat(pilotos.nome, " ", pilotos.sobrenome) as nome, equipes.nome as equipe, sum(pontuacao) as total from resultados
                                            join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                            join pilotos on pilotos.id = piloto_equipes.piloto_id
                                            join equipes on equipes.id = piloto_equipes.equipe_id
                                            join corridas on corridas.id = resultados.corrida_id
                                            join temporadas on temporadas.id = corridas.temporada_id
                                            where temporadas.id > 0
                                            and resultados.user_id = '.$usuario.'
                                            group by piloto_equipes.piloto_id
                                            order by total desc');
            
        $resultadosEquipesGeral = DB::select('select equipe_id, equipes.nome as nome, sum(pontuacao) as total from resultados
                            join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                            join equipes on equipes.id = piloto_equipes.equipe_id
                            join corridas on corridas.id = resultados.corrida_id
                            join temporadas on temporadas.id = corridas.temporada_id
                            where temporadas.id > 0
                            and resultados.user_id = '.$usuario.'
                            group by piloto_equipes.equipe_id
                            order by total desc');

        return view('home.home', compact('totVitoriasPorPiloto','totVitoriasPorEquipe','totPolesPorPiloto', 'temporadas','resultadosPilotosGeral','resultadosEquipesGeral'));
    }

    public function ajaxGetVitoriasPilotoPorTemporada(Request $request){

        $temporada_id = $request->post('vitoriasPilotosTemporadaId');
        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }

        //Consulta Dinâmica utiliza os operadores e condicoes dependendo do fato de ter ou nao temporada
        $vitoriasPiloto = Resultado::where('resultados.user_id', Auth::user()->id)->where('chegada', 1)
        ->join('corridas', function($join) use ($temporada_id, $operadorConsulta, $condicao){
            $join->on('corridas.id', '=', 'resultados.corrida_id')
                ->where('corridas.temporada_id',$operadorConsulta,$condicao);
        })->get();

        $vencedores = [];
        foreach($vitoriasPiloto as $item){
            if($item->corrida->flg_sprint == 'N'){
                array_push($vencedores, $item->pilotoEquipe->piloto->nomeCompleto());
            }
        }

        $totPorPiloto = array_count_values($vencedores);
        arsort($totPorPiloto);

        return response()->json([
            'message' => 'ajaxGetVitoriasPilotoPorTemporada',
            'totPorPiloto' => $totPorPiloto
        ]);
    }

    public function ajaxGetVitoriasEquipesPorTemporada(Request $request){
        $temporada_id = $request->post('vitoriasEquipesTemporadaId');
        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }
        
         //Consulta Dinâmica utiliza os operadores e condicoes dependendo do fato de ter ou nao temporada
         $vitoriasEquipe = Resultado::where('resultados.user_id', Auth::user()->id)->where('chegada', 1)
         ->join('corridas', function($join) use ($temporada_id, $operadorConsulta, $condicao){
             $join->on('corridas.id', '=', 'resultados.corrida_id')
                 ->where('corridas.temporada_id',$operadorConsulta,$condicao);
         })->get();
 
         $vencedores = [];
         foreach($vitoriasEquipe as $item){
             if($item->corrida->flg_sprint == 'N'){
                 array_push($vencedores, $item->pilotoEquipe->equipe->nome);
             }
         }
 
         $totVitoriasPorEquipe = array_count_values($vencedores);
         arsort($totVitoriasPorEquipe);
 
         return response()->json([
             'message' => 'ajaxGetVitoriasEquipesPorTemporada',
             'totVitoriasPorEquipe' => $totVitoriasPorEquipe
         ]);

    }

    public function ajaxGetPolesPilotosPorTemporada(Request $request){
        $temporada_id = $request->post('polesPilotosTemporadaId');
        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }

         //Consulta Dinâmica utiliza os operadores e condicoes dependendo do fato de ter ou nao temporada
         $polesPiloto = Resultado::where('resultados.user_id', Auth::user()->id)->where('largada', 1)
         ->join('corridas', function($join) use ($temporada_id, $operadorConsulta, $condicao){
             $join->on('corridas.id', '=', 'resultados.corrida_id')
                 ->where('corridas.temporada_id',$operadorConsulta,$condicao);
         })->get();
 
         $polePositions = [];
         foreach($polesPiloto as $item){
             if($item->corrida->flg_sprint == 'N'){
                 array_push($polePositions, $item->pilotoEquipe->piloto->nomeCompleto());
             }
         }
 
         $totPolesPorPiloto = array_count_values($polePositions);
         arsort($totPolesPorPiloto);
 
         return response()->json([
             'message' => 'ajaxGetPolesPilotoPorTemporada',
             'totPolesPorPiloto' => $totPolesPorPiloto
         ]);
    }
}