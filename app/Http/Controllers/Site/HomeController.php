<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use App\Models\Site\Titulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(){

       $temporadas = Temporada::where('user_id', Auth::user()->id)->get();
       $vitoriasDosPilotos = [];
       $vitoriasDasEquipes = [];
       $polePositionDosPilotos = [];
       $polePositionDasEquipes = [];
       $podiosDosPilotos = [];
       $podiosDasEquipes = [];
       $abandonosDosPilotos = [];
       $abandonosDasEquipes = [];
       $chegadasTop10Pilotos = [];
       $chegadasTop10Equipes = [];

        $resultados = $vitoriaEquipes = Resultado::join('piloto_equipes', 'pilotoEquipe_id', 'piloto_equipes.id')
                                                // ->join('pilotos', 'piloto_equipes.piloto_id', 'pilotos.id')
                                                // ->join('equipes', 'piloto_equipes.equipe_id', 'equipes.id')
                                                ->join('corridas', 'resultados.corrida_id', 'corridas.id')
                                                ->where('resultados.user_id', Auth::user()->id)
                                                ->where('corridas.flg_sprint', 'N')
                                                // ->where('corridas.temporada_id', 3)
                                                ->get();

        foreach($resultados as $resultado){
            if($resultado->chegada > 0 && $resultado->chegada <=1){
                $vitoriasDosPilotos[] = $resultado->pilotoEquipe->piloto->nomeCompleto();
                $vitoriasDasEquipes[] = $resultado->pilotoEquipe->equipe->nome;
            }

            if($resultado->largada > 0 && $resultado->largada <=1){
                $polePositionDosPilotos[] = $resultado->pilotoEquipe->piloto->nomeCompleto();
                $polePositionDasEquipes[] = $resultado->pilotoEquipe->equipe->nome;
            }

            if($resultado->chegada >= 1 && $resultado->chegada <=3){
                $podiosDosPilotos[] = $resultado->pilotoEquipe->piloto->nomeCompleto();
                $podiosDasEquipes[] = $resultado->pilotoEquipe->equipe->nome;
            }

            if($resultado->flg_abandono == 'S'){
                $abandonosDosPilotos[] = $resultado->pilotoEquipe->piloto->nomeCompleto();
                $abandonosDasEquipes[] = $resultado->pilotoEquipe->equipe->nome;
            }

            if($resultado->chegada > 0 && $resultado->chegada <= 10){
                $chegadasTop10Pilotos[] = $resultado->pilotoEquipe->piloto->nomeCompleto();
                $chegadasTop10Equipes[] = $resultado->pilotoEquipe->equipe->nome;
            }
        }

        $totalVitoriasPorPiloto = array_count_values($vitoriasDosPilotos);
        arsort($totalVitoriasPorPiloto);

        $totalVitoriasPorEquipe = array_count_values($vitoriasDasEquipes);
        arsort($totalVitoriasPorEquipe);

        $totalPolePositionsPorPiloto = array_count_values($polePositionDosPilotos);
        arsort($totalPolePositionsPorPiloto);

        $totalPolePositionsPorEquipe = array_count_values($polePositionDasEquipes);
        arsort($totalPolePositionsPorEquipe);

        $totalPodiosPorPiloto = array_count_values($podiosDosPilotos);
        arsort($totalPodiosPorPiloto);

        $totalPodiosPorEquipe = array_count_values($podiosDasEquipes);
        arsort($totalPodiosPorEquipe);

        $totalAbandonosPorPiloto = array_count_values($abandonosDosPilotos);
        arsort($totalAbandonosPorPiloto);

        $totalAbandonosPorEquipe = array_count_values($abandonosDasEquipes);
        arsort($totalAbandonosPorEquipe);

        $totalTop10PorPiloto = array_count_values($chegadasTop10Pilotos);
        arsort($totalTop10PorPiloto);

        $totalTop10PorEquipe = array_count_values($chegadasTop10Equipes);
        arsort($totalTop10PorEquipe);

        //Calculo dos títulos conquistados pelos pilotos e equipes 

        $titulosPorPilotos = [];
        $titulosPorEquipes = [];

        $titulos = Titulo::where('user_id', Auth::user()->id)->get();

        foreach($titulos as $item){
            array_push($titulosPorPilotos, $item->pilotoEquipe->piloto->nomeCompleto());
            array_push($titulosPorEquipes, $item->equipe->nome);
        }

        $totalTitulosPorPiloto = array_count_values($titulosPorPilotos);
        arsort($totalTitulosPorPiloto);

        $totalTitulosPorEquipe = array_count_values($titulosPorEquipes);
        arsort($totalTitulosPorEquipe);
        

        // dd([
        //     // 'total de vitórias dos pilotos ' => $totalVitoriasPorPiloto,
        //     // 'total de vitórias das equipes ' => $totalVitoriasPorEquipe,
        //     // 'total de pole positions dos pilotos ' => $totalPolePositionsPorPiloto,
        //     // 'total de pole position das equipes ' => $totalPolePositionsPorEquipe
        //     // 'total de podios dos equipes ' => $totalPodiosPorPiloto,
        //     // 'total de podios dos pilotos ' => $totalPodiosPorEquipe,
        //     // 'total de abandonos dos pilotos ' => $totalAbandonosPorPiloto,
        //     // 'total de abandonos das equipes ' => $totalAbandonosPorEquipe
        //     // 'total de top 10 dos pilotos ' => $totalTop10PorPiloto,
        //     // 'total de top 10 das equipes ' => $totalTop10PorEquipe
        //     // 'total de titulos dos pilotos ' => $totalTitulosPorPiloto,
        //     // 'total de titulos das equipes ' => $totalTitulosPorEquipe
        // ]);

        //FINAL DOS ESTUDOS UTILIZANDO APENAS UM LOOP DE RESULTADOS

        return view('home.home', compact(
            'totalVitoriasPorPiloto',
            'totalVitoriasPorEquipe',
            'totalPolePositionsPorPiloto',
            'totalPolePositionsPorEquipe',
            'temporadas',
            'totalPodiosPorPiloto',
            'totalPodiosPorEquipe',
            'totalAbandonosPorPiloto',
            'totalAbandonosPorEquipe',
            'totalTop10PorPiloto',
            'totalTop10PorEquipe',
            'totalTitulosPorPiloto',
            'totalTitulosPorEquipe'
        ));
    }

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

    public function ajaxGetChegadasPilotosPorTemporada(Request $request){

        $temporada_id = $request->post('temporada');
        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }

        $totPorPiloto = DB::select('select
                                        pilotos.id, COUNT(*) as chegadas, concat(pilotos.nome," ",pilotos.sobrenome) as nome
                                    from resultados
                                    join corridas on (corridas.id = resultados.corrida_id)
                                    join piloto_equipes on (resultados.pilotoEquipe_id = piloto_equipes.id)
                                    join pilotos on (piloto_equipes.piloto_id = pilotos.id)
                                    where corridas.temporada_id '.$operadorConsulta.' '.$condicao.'
                                    and resultados.chegada >= '.$request->inicio.'
                                    and resultados.chegada <= '.$request->fim.'
                                    and corridas.flg_sprint = "N"
                                    and corridas.user_id = '.Auth::user()->id.'
                                    group by pilotos.id 
                                    order by chegadas desc');

        return response()->json([
            'message' => 'ajaxGetChegadasPilotosPorTemporada',
            'totPorPiloto' => $totPorPiloto
        ]);
    }

    public function ajaxGetChegadasEquipesPorTemporada(Request $request){

        $temporada_id = $request->post('temporada');
        $operadorConsulta = '=';
        $condicao = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsulta = '>';
            $condicao = 0; 
        }

        $totPorEquipe = DB::select('select
                                        equipes.id, COUNT(*) as chegadas, equipes.nome as nome
                                    from resultados
                                    join corridas on (corridas.id = resultados.corrida_id)
                                    join piloto_equipes on (resultados.pilotoEquipe_id = piloto_equipes.id)
                                    join equipes on (piloto_equipes.equipe_id = equipes.id)
                                    where corridas.temporada_id '.$operadorConsulta.' '.$condicao.'
                                    and resultados.chegada >= '.$request->inicio.'
                                    and resultados.chegada <= '.$request->fim.'
                                    and corridas.flg_sprint = "N"
                                    and corridas.user_id = '.Auth::user()->id.'
                                    group by equipe_id 
                                    order by chegadas desc;');

        return response()->json([
            'message' => 'ajaxGetChegadasEquipesPorTemporada',
            'totPorEquipe' => $totPorEquipe
        ]);


    }

}
