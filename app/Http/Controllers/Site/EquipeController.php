<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Corrida;
use App\Models\Site\Equipe;
use App\Models\Site\ForcaEquipe;
use App\Models\Site\Pais;
use App\Models\Site\Piloto;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use App\Models\Site\Titulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $equipes = Equipe::where('user_id', Auth::user()->id)->get();

        return view('site.equipes.index', compact('equipes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $paises = Pais::where('user_id', Auth::user()->id)->get();
        return view('site.equipes.form', compact('paises'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $equipe = new Equipe();
        $equipe->nome = $request->nome;
        $equipe->des_cor = $request->des_cor;
        $equipe->user_id = Auth::user()->id;
        $equipe->pais_id = $request->pais_id;
        // if ($request->has('flg_ativo')) {
        //     $equipe->flg_ativo = $request->flg_ativo;
        // } else {
        //     $equipe->flg_ativo = 'N';
        // }

        $equipe->flg_ativo = 'S';

        if($request->imagem == ''){
            $newImageName = '';
        } else {
           $newImageName = time().'-'.$request->nome.'.'.$request->imagem->extension();
           $request->imagem->move(public_path('images'), $newImageName);
        }

        $equipe->imagem = $newImageName;

        $equipe->save();

        return redirect()->back()->with('status', 'A Equipe '.$equipe->nome.' foi registrada');
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
         $modelEquipe = Equipe::where('id', $id)
                                ->where('user_id', Auth::user()->id)
                                ->first();

        //total de corridas
        $resultados = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
                                ->join('temporadas', 'temporadas.id', '=', 'corridas.temporada_id')
                                // ->join('pilotos', 'piloto_equipes.piloto_id', 'pilotos.id')
                                ->where('resultados.user_id', Auth::user()->id)
                                // ->where('corridas.flg_sprint', 'N')
                                ->where('piloto_equipes.equipe_id', $modelEquipe->id)
                                 ->orderBy('temporadas.id', 'DESC')
                                ->orderBy('resultados.id', 'DESC')
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
        $totDobradinhas = 0;
        $listagemVitorias = [];
        $listagemPolePositions = [];
        $totPontos = 0;

        $totVoltasRapidas = 0;

        $possiveisDobradinhas = [];

        //Consideramos apenas o id da equipe pois levamos em consideração a junção do resultado da dupla de pilotos

        foreach($resultados as $resultado){
            if($resultado->corrida->flg_sprint == 'N'){

                if($resultado->chegada == 2){
                    if($resultado->pilotoEquipe->equipe->id == $id){
                        $possiveisDobradinhas[] = $resultado;
                    }
                }

                //total de largadas
                if($resultado->pilotoEquipe->equipe->id == $id){
                    $totCorridas++;
                }

                //calculo do total de vitórias
                if($resultado->chegada == 1){
                    if($resultado->pilotoEquipe->equipe->id == $id){
                        $possiveisDobradinhas[] = $resultado;
                        $totVitorias++;
                        $listagemVitorias[] = $resultado;
                    }
                }

                //calculo do total de pole positions
                if($resultado->largada == 1){
                    if($resultado->pilotoEquipe->equipe->id == $id){
                        $totPoles++;
                        $listagemPolePositions[] = $resultado;
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

            if($resultado->pilotoEquipe->equipe->id == $id){
                $totPontos += $resultado->pontuacao;
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

        $pilotoEquipe = PilotoEquipe::where('equipe_id', $id)
                                    ->where('user_id', Auth::user()->id)
                                    ->get();

        //lista os pilotos que fizeram/fazem parte da equipe
        $ids = [];
        foreach($pilotoEquipe as $equipeId){
            array_push($ids, $equipeId->id);
        }

        $totVoltasRapidas = Corrida::where('user_id', Auth::user()->id)
                                    ->where('flg_sprint', 'N')
                                    ->where('volta_rapida', '!=', null)
                                    ->whereIn('volta_rapida', $ids)
                                    ->count();

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

        $corridasPorPiloto = Resultado::join('corridas', 'resultados.corrida_id', '=', 'corridas.id')
                                        ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                        ->join('pilotos', 'pilotos.id', 'piloto_equipes.piloto_id')
                                        ->where('resultados.user_id', Auth::user()->id)
                                        ->where('piloto_equipes.equipe_id', $modelEquipe->id)
                                        ->where('corridas.flg_sprint', '<>', 'S')
                                        ->groupBy('piloto_equipes.piloto_id')
                                        ->select('piloto_equipes.piloto_id', 'pilotos.nome', 'pilotos.sobrenome', 'pilotos.imagem', DB::raw('COUNT(*) as quantidade'))
                                        ->orderBy('quantidade', 'DESC')
                                        ->get();

                                        // dd($corridasPorPiloto);


        return view('site.equipes.show', compact('modelEquipe','totTitulos', 'totCorridas','totTitulosPilotos', 'totVitorias','totPontos', 'totPodios', 'totTopTen','piorPosicaoLargada','totPoles', 'melhorPosicaoLargada','melhorPosicaoChegada', 'piorPosicaoChegada','totVoltasRapidas','temporadasDisputadas','pontuacaoPorTemporada','temporadas','totAbandonos','gridMedio','mediaChegada','totDobradinhas','listagemVitorias', 'listagemPolePositions','corridasPorPiloto'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = Equipe::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $paises = Pais::where('user_id', Auth::user()->id)->get();
        return view('site.equipes.form', compact('model','paises'));
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
        $equipe = Equipe::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $equipe->nome = $request->nome;
        $equipe->des_cor = $request->des_cor;
        $equipe->user_id = Auth::user()->id;
        $equipe->pais_id = $request->pais_id;
        if ($request->has('flg_ativo')) {
            $equipe->flg_ativo = $request->flg_ativo;
        } else {
            $equipe->flg_ativo = 'N';
        }

        //se tiver foto criar span no form pra nao precisar alterar,  caso queira, apagar a antiga e colocar outra
        if($request->imagem == ''){
            $newImageName = '';
        } else {
            if(file_exists(public_path('images/'.$equipe->imagem))){
                if($equipe->imagem != null){
                    unlink(public_path('images/'.$equipe->imagem));
                }
            }
           $newImageName = time().'-'.$request->nome.'.'.$request->imagem->extension();
           $request->imagem->move(public_path('images'), $newImageName);
           $equipe->imagem = $newImageName;
        }

        $equipe->update();

        return redirect()->route('equipes.index')->with('status', 'A Equipe '.$equipe->nome.' foi editada');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $equipe = Equipe::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $equipe->delete();
        return redirect()->back();
    }

    public function relacaoForcasIndex(){
        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();

        return view('site.equipes.relacaoForcas.index', compact('temporadas'));
    }

    public function relacaoForcasIndexListagemEquipes($temporada_id){
        
        $temporada = Temporada::find($temporada_id);
       
        $equipes = PilotoEquipe::with('equipe')
                        ->where('user_id', Auth::user()->id)
                        ->where('ano_id', $temporada->ano->id)
                        ->orderBy('id')
                        ->groupBy('equipe_id')
                        ->get();

        return view('site.equipes.relacaoForcas.listagemEquipes', compact('equipes', 'temporada'));

    }

    public function relacaoForcasEdit($ano_id, $equipe_id){

        $equipe = Equipe::find($equipe_id);

        $model = ForcaEquipe::where('equipe_id', $equipe_id)
                            ->where('ano_id', $ano_id)
                            ->where('user_id', Auth::user()->id)
                            ->first();

        return view('site.equipes.relacaoForcas.form', compact('equipe', 'ano_id', 'model'));
    }

    public function relacaoForcasUpdate(Request $request){

        $model = ForcaEquipe::where('equipe_id', $request->equipe_id)
                            ->where('ano_id', $request->ano_id)
                            ->where('user_id', Auth::user()->id)
                            ->first();

        if(isset($model)){
            $model->forca = $request->forca;
            $model->update();
        }else{
            $newModel = new ForcaEquipe;
            $newModel->equipe_id = $request->equipe_id;
            $newModel->ano_id = $request->ano_id;
            $newModel->user_id = Auth::user()->id;
            $newModel->forca = $request->forca;
            $newModel->save();
        }
               
        return redirect()->back()->with('status', 'Salvo com sucesso');
    }


}
