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
        $corridas = Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $temporadaId)->orderBy('ordem')->get();
        return view('site.corridas.index', compact('corridas', 'temporada'));
    }

    public function adicionar($temporadaId){
        //dd('adicionando corridas para temporada: '.$temporadaId);
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $temporadaId)->first();
        $model = Pista::where('user_id', Auth::user()->id)->where('flg_ativo', 'S')->get();
        
        return view('site.corridas.form', compact('model', 'temporada'));
    }

    public function alterar(Request $request, $temporadaId){
        //dd($request->all());

        $corrida = new Corrida();
        $corrida->temporada_id = $request->temporada_id;
        $corrida->pista_id = $request->pista_id;
        $corrida->ordem = $request->ordem;
        $corrida->user_id = Auth::user()->id;
        $corrida->dificuldade_ia = $request->dificuldade_ia;
        $corrida->qtd_safety_car = $request->qtd_safety_car;

        $corrida->save();

        /* foreach($request->input('pista_id') as $key => $pista){
            $ordem = $request->input('ordem')[$key];
            if($ordem != null){

            $corrida = new Corrida();
            $corrida->temporada_id = $request->temporada_id;
            $corrida->pista_id = $pista;
            $corrida->ordem = $ordem;
            $corrida->user_id = Auth::user()->id;
    
            $corrida->save();
            }
        } */

        return redirect()->back();
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        $resultados = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $id)->get();

        foreach($resultados as $resultado){
            $resultado->delete();
        }

        $corrida = Corrida::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $corrida->delete();
        return redirect()->back();
    }
}
