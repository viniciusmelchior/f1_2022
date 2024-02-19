<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Corrida;
use App\Models\Site\Pista;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorridaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($temporadaId)
    {   
        $temporada = Temporada::where('id', $temporadaId)->first();
        $corridas = Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $temporadaId)->orderBy('ordem')->orderBy('flg_sprint', 'DESC')->get();
        return view('site.corridas.index', compact('corridas', 'temporada'));
    }

    public function adicionar($temporadaId){
        
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $temporadaId)->first();
        $model = Pista::where('user_id', Auth::user()->id)->where('flg_ativo', 'S')->get();
        
        return view('site.corridas.form', compact('model', 'temporada'));
    }

    /**
     * função utilizada para criar as corridas
     */
    public function store(Request $request, $temporadaId){

        $corrida = new Corrida();
        $corrida->temporada_id = $request->temporada_id;
        $corrida->pista_id = $request->pista_id;
        $corrida->ordem = $request->ordem;
        $corrida->user_id = Auth::user()->id;
        $corrida->dificuldade_ia = $request->dificuldade_ia;
        $corrida->flg_sprint = $request->flg_sprint == 'S' ? 'S' : 'N';
        $corrida->qtd_safety_car = $request->qtd_safety_car;

        $corrida->save();

        return redirect()->back()->with('status', 'GP '.$corrida->pista->nome.' criado com sucesso!');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($temporada_id, $corrida_id)
    {   
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $temporada_id)->first();
        $model = Pista::where('user_id', Auth::user()->id)->where('flg_ativo', 'S')->orderBy('nome')->get();
        $modelCorrida = Corrida::where('id', $corrida_id)->first();
        
        return view('site.corridas.form', compact('model','modelCorrida', 'temporada'));
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
        $corrida = Corrida::where('id', $request->corrida_id)->first();
        $corrida->ordem = $request->ordem;
        $corrida->pista_id = $request->pista_id;
        $corrida->flg_sprint = $request->flg_sprint == 'S' ? 'S' : 'N';
        $corrida->update();

        return redirect()->route('corridas.index',[$request->temporada_id])->with('status', 'O GP '.$corrida->pista->nome.' foi editado');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   
        
        $resultados = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $request->corrida_id)->get();

        if(count($resultados) > 0){
            foreach($resultados as $resultado){
                $resultado->delete();
            }
        }

        $corrida = Corrida::where('id', $request->corrida_id)->where('user_id', Auth::user()->id)->first();
        $corrida->delete();

        return redirect()->back()->with('status', 'O GP de '.$corrida->pista->nome.' foi excluído com sucesso');
    }
}
