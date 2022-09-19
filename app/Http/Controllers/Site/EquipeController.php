<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Corrida;
use App\Models\Site\Equipe;
use App\Models\Site\Pais;
use App\Models\Site\Piloto;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if ($request->has('flg_ativo')) {
            $equipe->flg_ativo = $request->flg_ativo;
        } else {
            $equipe->flg_ativo = 'N';
        }

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
                                ->where('resultados.user_id', Auth::user()->id)
                                ->where('corridas.flg_sprint', 'N')
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

        $totVoltasRapidas = 0;

        //Consideramos apenas o id da equipe pois levamos em consideração a junção do resultado da dupla de pilotos
        foreach($resultados as $resultado){

            //total de largadas
            if($resultado->pilotoEquipe->equipe->id == $id){
                $totCorridas++;
            }

            //calculo do total de vitórias
            if($resultado->chegada == 1){
                if($resultado->pilotoEquipe->equipe->id == $id){
                    $totVitorias++;
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

        //Total de Pontos tem cálculo diferente pois envolve sprints
        $resultados =  Resultado::where('user_id', Auth::user()->id)->get();                     
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
                            ->get();

        $totVoltasRapidas = count($resultado); 

        
        return view('site.equipes.show', compact('modelEquipe', 'totCorridas', 'totVitorias','totPontos', 'totPodios', 'totTopTen','piorPosicaoLargada','totPoles', 'melhorPosicaoLargada','melhorPosicaoChegada', 'piorPosicaoChegada','totVoltasRapidas'));
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
}
