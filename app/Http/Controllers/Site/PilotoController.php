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
use App\Models\Site\PilotoEquipe;
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
        $pilotos = Piloto::where('user_id', Auth::user()->id)
                            ->orderBy('id', 'ASC')
                            ->orderBy('flg_ativo', 'DESC')
                            ->get();

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
        if ($request->has('flg_ativo')) {
            $piloto->flg_ativo = $request->flg_ativo;
        } else {
            $piloto->flg_ativo = 'N';
        }

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
    
        //Total de Pontos tem cálculo diferente pois envolve sprints
        $resultados =  Resultado::where('user_id', Auth::user()->id)->get();                     
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
       
        return view('site.pilotos.show', compact('modelPiloto', 'totCorridas', 'totVitorias','totPontos', 'totPodios', 'totTopTen','piorPosicaoLargada','totPoles', 'melhorPosicaoLargada','melhorPosicaoChegada', 'piorPosicaoChegada','totVoltasRapidas'));
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
}
