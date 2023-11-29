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
        $totVitoriasPorPiloto = DB::select('select
                                                pilotos.id, COUNT(*) as vitorias, concat(pilotos.nome," ",pilotos.sobrenome) as nome
                                            from resultados
                                            join corridas on (corridas.id = resultados.corrida_id)
                                            join piloto_equipes on (resultados.pilotoEquipe_id = piloto_equipes.id)
                                            join pilotos on (piloto_equipes.piloto_id = pilotos.id)
                                            where corridas.temporada_id > 0
                                            and resultados.chegada = 1
                                            and corridas.flg_sprint = "N"
                                            and corridas.user_id = '.Auth::user()->id.'
                                            group by pilotos.id 
                                            order by vitorias desc');

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

    // public function visualizarVitoriasPiloto(Request $request){
    //     dd($request->query());
    // }

    public function ajaxGetVitoriasPilotoPorTemporada(Request $request){

        $temporada_id = $request->post('vitoriasPilotosTemporadaId');
        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }

        $totPorPiloto = DB::select('select
                                        pilotos.id, COUNT(*) as vitorias, concat(pilotos.nome," ",pilotos.sobrenome) as nome
                                    from resultados
                                    join corridas on (corridas.id = resultados.corrida_id)
                                    join piloto_equipes on (resultados.pilotoEquipe_id = piloto_equipes.id)
                                    join pilotos on (piloto_equipes.piloto_id = pilotos.id)
                                    where corridas.temporada_id '.$operadorConsulta.' '.$condicao.'
                                    and resultados.chegada = 1
                                    and corridas.flg_sprint = "N"
                                    and corridas.user_id = '.Auth::user()->id.'
                                    group by pilotos.id 
                                    order by vitorias desc');

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

    public function ajaxGetPolesEquipesPorTemporada(Request $request){
        $temporada_id = $request->post('polesEquipesTemporadaId');
        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }
        
         //Consulta Dinâmica utiliza os operadores e condicoes dependendo do fato de ter ou nao temporada
         $polesEquipe = Resultado::where('resultados.user_id', Auth::user()->id)->where('largada', 1)
         ->join('corridas', function($join) use ($temporada_id, $operadorConsulta, $condicao){
             $join->on('corridas.id', '=', 'resultados.corrida_id')
                 ->where('corridas.temporada_id',$operadorConsulta,$condicao);
         })->get();
 
         $poles = [];
         foreach($polesEquipe as $item){
             if($item->corrida->flg_sprint == 'N'){
                 array_push($poles, $item->pilotoEquipe->equipe->nome);
             }
         }
 
         $totPolesPorEquipe = array_count_values($poles);
         arsort($totPolesPorEquipe);
 
         return response()->json([
             'message' => 'ajaxGetVitoriasEquipesPorTemporada',
             'totPolesPorEquipe' => $totPolesPorEquipe
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

    public function ajaxGetPodiosPilotosPorTemporada(Request $request){
        $temporada_id = $request->post('podiosPilotosTemporadaId');
        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }

        $totPorPiloto = DB::select('select
                                        pilotos.id, COUNT(*) as podios, concat(pilotos.nome," ",pilotos.sobrenome) as nome
                                    from resultados
                                    join corridas on (corridas.id = resultados.corrida_id)
                                    join piloto_equipes on (resultados.pilotoEquipe_id = piloto_equipes.id)
                                    join pilotos on (piloto_equipes.piloto_id = pilotos.id)
                                    where corridas.temporada_id '.$operadorConsulta.' '.$condicao.'
                                    and resultados.chegada >= 1
                                    and resultados.chegada <=3
                                    and corridas.flg_sprint = "N"
                                    and corridas.user_id = '.Auth::user()->id.'
                                    group by pilotos.id 
                                    order by podios desc');

        return response()->json([
            'message' => 'ajaxGetPodiosPilotoPorTemporada',
            'totPorPiloto' => $totPorPiloto
        ]);
    }

    public function ajaxGetPodiosEquipesPorTemporada(Request $request){
        
        $temporada_id = $request->post('podiosEquipesTemporadaId');
        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }

        $totPorEquipe = DB::select('select
                                        equipes.id, COUNT(*) as podios, equipes.nome as nome
                                    from resultados
                                    join corridas on (corridas.id = resultados.corrida_id)
                                    join piloto_equipes on (resultados.pilotoEquipe_id = piloto_equipes.id)
                                    join equipes on (piloto_equipes.equipe_id = equipes.id)
                                    where corridas.temporada_id '.$operadorConsulta.' '.$condicao.'
                                    and resultados.chegada > 0 
                                    and resultados.chegada <= 3
                                    and corridas.flg_sprint = "N"
                                    and corridas.user_id = '.Auth::user()->id.'
                                    group by equipe_id 
                                    order by podios desc;');

        return response()->json([
            'message' => 'ajaxGetPodiosPilotoPorTemporada',
            'totPorEquipe' => $totPorEquipe
        ]);
    }

}
