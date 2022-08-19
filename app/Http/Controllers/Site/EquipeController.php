<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Equipe;
use App\Models\Site\Pais;
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

        return redirect()->route('equipes.index');
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
