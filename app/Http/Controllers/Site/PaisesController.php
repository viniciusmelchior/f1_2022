<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Continente;
use App\Models\Site\Corrida;
use App\Models\Site\Pais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaisesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $paises = Pais::where('user_id', Auth::user()->id)->get();

        return view('site.paises.index', compact('paises'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $continentes = Continente::all();

        return view('site.paises.form', compact('continentes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $pais = new Pais();
        $pais->des_nome = $request->des_nome;
        $pais->continente_id = $request->continente_id;
        $pais->user_id = Auth::user()->id;

        if($request->imagem == ''){
            $newImageName = '';
        } else {
           $newImageName = time().'-'.$request->nome.'.'.$request->imagem->extension();
           $request->imagem->move(public_path('images'), $newImageName);
        }

        $pais->imagem = $newImageName;

        $pais->save();

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $resultadoCorridas = Corrida::where('user_id', Auth::user()->id)
                                    ->whereHas('pista', function ($query) use ($id) {
                                        $query->where('pais_id', $id);
                                    })
                                    ->orderBy('temporada_id', 'DESC')
                                    ->orderBy('ordem', 'DESC')
                                    ->get();

        if(count($resultadoCorridas) > 0){
            return view('site.paises.show', compact('resultadoCorridas'));
        }else{
            return redirect()->route('paises.index')->with('error', 'Não existem corridas disputadas no país selecionado');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = Pais::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $continentes = Continente::all();

        return view('site.paises.form', compact('model','continentes'));
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
        $pais = Pais::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $pais->des_nome = $request->des_nome;

        //se tiver foto criar span no form pra nao precisar alterar,  caso queira, apagar a antiga e colocar outra
        if($request->imagem == ''){
            $newImageName = '';
        } else {
            if(file_exists(public_path('images/'.$pais->imagem))){
                if($pais->imagem != null){
                    unlink(public_path('images/'.$pais->imagem));
                }
            }
           $newImageName = time().'-'.$request->nome.'.'.$request->imagem->extension();
           $request->imagem->move(public_path('images'), $newImageName);
           $pais->continente_id = $request->continente_id;
           $pais->imagem = $newImageName;
        }

        $pais->update();

        return redirect()->route('paises.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request)
    {      
        
        try {
            $id = $request->pais_id;
            $pais = Pais::where('id', $id)->where('user_id', Auth::user()->id)->first();
            $pais->delete();
    
            //apagando a foto do país
            if($pais->imagem != null){
                if(file_exists(public_path('images/'.$pais->imagem))){
                    unlink(public_path('images/'.$pais->imagem));
                }
            }
        
            return redirect()->route('paises.index')->with('status', 'O pais '.$pais->des_nome.' foi excluído com sucesso');
        } catch (\Throwable $th) {
            return redirect()->route('paises.index')->with('error', 'Não foi possível excluir o país selecionado');
        }
    }
}
