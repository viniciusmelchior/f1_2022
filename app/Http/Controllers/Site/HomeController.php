<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(){
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

        $totPorPiloto = array_count_values($vencedores);
        arsort($totPorPiloto);

        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();

        return view('home.home', compact('totPorPiloto', 'temporadas'));
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
}
