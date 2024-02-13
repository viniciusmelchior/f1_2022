<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Pais;
use App\Models\Site\Pista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pistas = Pista::where('user_id', Auth::user()->id)->orderBy('nome')->get();

        return view('site.pistas.index', compact('pistas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $paises = Pais::where('user_id', Auth::user()->id)->get();
        return view('site.pistas.form', compact('paises'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        /**
        * Validação dos inputs
        */
        $rules = [
            'nome' => 'unique:pistas',
            'tamanho_km' => 'required',
        ];
    
        $messages = [
            'nome.unique' => 'Já existe uma pista cadastrada com este nome',
            'tamanho_km.required' => 'O campo :attribute é obrigatório',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);    

        if ($validator->fails()) {
            // Mensagens de erro personalizadas
            $errors = $validator->errors();
    
            // Trate os erros como desejar, por exemplo, redirecione com os erros de volta ao formulário
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $pista = new Pista();
        $pista->nome = $request->nome;
        $pista->user_id = Auth::user()->id;
        $pista->pais_id = $request->pais_id;
        $pista->qtd_carros = $request->qtd_carros;
        $pista->flg_ativo = 'S';
        // if ($request->has('flg_ativo')) {
        //     $pista->flg_ativo = $request->flg_ativo;
        // } else {
        //     $pista->flg_ativo = 'N';
        // }

        $pista->tamanho_km = $request->tamanho_km; 
        //calculo de voltas. Distância 50% em corridas da Indy e F1 (outras categorias, desconsiderar)
        $distanciaOficial = 300;
        $pista->qtd_voltas = round((($distanciaOficial/$pista->tamanho_km)*1000)/2)+1;

        $pista->save();

        return redirect()->back()->with('success', 'Pista '.$pista->nome.' cadastrada com sucesso.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        return view('site.pistas.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = Pista::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $paises = Pais::where('user_id', Auth::user()->id)->get();
        return view('site.pistas.form', compact('model','paises'));
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
        $pista = Pista::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $pista->nome = $request->nome;
        $pista->user_id = Auth::user()->id;
        $pista->pais_id = $request->pais_id;
        $pista->qtd_carros = $request->qtd_carros;
        if ($request->has('flg_ativo')) {
            $pista->flg_ativo = $request->flg_ativo;
        } else {
            $pista->flg_ativo = 'N';
        }

        $pista->tamanho_km = $request->tamanho_km; 
        //calculo de voltas. Distância 50% em corridas da Indy e F1 (outras categorias, desconsiderar)
        $distanciaOficial = 300;
        $pista->qtd_voltas = round((($distanciaOficial/$pista->tamanho_km)*1000)/2)+1;
        //dd($pista->qtd_voltas, $distanciaOficial, $pista->tamanho_km);

        $pista->update();

        return redirect()->route('pistas.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pista = Pista::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $pista->delete();
        return redirect()->back();
    }
}
