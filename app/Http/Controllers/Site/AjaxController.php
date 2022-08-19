<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Temporada;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{
    public function classificacaoPorTemporada(Request $request){
        
        $usuario = Auth::user()->id; 
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $request->temporada_id)->first();

        $resultadosPilotos = DB::select('select piloto_id, concat(pilotos.nome, " ", pilotos.sobrenome) as nome, equipes.nome as equipe, sum(pontuacao) as total from resultados
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
}
