<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Pais;
use App\Models\Site\PilotoEquipe;
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

         //loop que faz os comparativos
         foreach($dadosPiloto1 as $key1 => $piloto1){
            // if(isset($dadosPiloto2[$key1]) && $piloto1->corrida_id == $dadosPiloto2[$key1]->corrida_id){
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
            // }   
         }
 
         //loop do segundo piloto para dados especificos dele. Os do primeiro piloto ja são tratados como os dados base do loop comparativo
         foreach($dadosPiloto2 as $key1 => $piloto2){
            //  if(isset($dadosPiloto1[$key1]) && $piloto2->corrida_id == $dadosPiloto1[$key1]->corrida_id){
                 $piloto2->chegada <= 3 ? $piloto2TotPodios++ : "";
                 $piloto2->flg_abandono == 'S' ? $piloto2TotAbandonos++ : '';
                 $piloto2->volta_rapida == $piloto2->pilotoEquipe_id ? $piloto2TotVoltasRapidas++ : '';
                 $piloto2->chegada == 1 ? $piloto2TotVitorias++ : "";
                 $piloto2->largada == 1 ? $piloto2TotPolePositions++ : "";
                 $piloto2->chegada < $piloto2MelhorChegada ? $piloto2MelhorChegada = $piloto2->chegada : "";
                 $piloto2->chegada > $piloto2PiorChegada ? $piloto2PiorChegada = $piloto2->chegada : "";
                 $piloto2->largada > $piloto2PiorLargada ? $piloto2PiorLargada = $piloto2->largada : "";
                 $piloto2->largada < $piloto2MelhorLargada ? $piloto2MelhorLargada = $piloto2->largada : "";
            //  }
         }
        
        return response()->json([
            'message' => 'Chegamos no Controller',
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
       $pilotoEquipe = PilotoEquipe::where('user_id', Auth::user()->id)->get();

       $paises = Pais::where('user_id', Auth::user()->id)
                    // ->whereIn('id', [7,15,17,18,6,19,10])
                    ->orderBy('des_nome')
                    ->get();

        //possível fazer assim, acrescentando o join após a consulta inicial e dando get na hora de puxar os dados
        $dados = DB::table('titulos')
                    ->select('*')
                    ->join('piloto_equipes', function($join){
                        $join->on('piloto_equipes.id', '=', 'titulos.pilotoEquipe_id');
                    });
                   
        $dados->join('pilotos', function($join){
            $join->on('pilotos.id', '=', 'piloto_equipes.piloto_id');
        });

        //ou encadeando e dando get no final . 
        $dados2 = DB::table('titulos')
                    ->select('*')
                    ->join('piloto_equipes', function($join){
                        $join->on('piloto_equipes.id', '=', 'titulos.pilotoEquipe_id');
                    })
                    ->join('pilotos', function($join){
                        $join->on('pilotos.id', '=', 'piloto_equipes.piloto_id')
                            ->where('pilotos.id','=', 4);
                    })->get();

        // dd($dados->get(), $dados2);

       return view('estudos', compact('pilotoEquipe', 'paises'));
    }
}
