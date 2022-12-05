<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\CondicaoClimatica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CondicaoClimaticaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $condicaoClimaticas = CondicaoClimatica::where('user_id', Auth::user()->id)->get();

        return view('site.condicaoClimatica.index', compact('condicaoClimaticas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('site.condicaoClimatica.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $condicaoClimatica = new CondicaoClimatica();
        $condicaoClimatica->descricao = $request->descricao;
        $condicaoClimatica->des_icone = $request->des_icone;
        $condicaoClimatica->user_id = Auth::user()->id;
        $condicaoClimatica->save();

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
        $model = CondicaoClimatica::where('id', $id)->where('user_id', Auth::user()->id)->first();
        return view('site.condicaoClimatica.form', compact('model'));
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
        $condicaoClimatica = CondicaoClimatica::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $condicaoClimatica->descricao = $request->descricao;
        $condicaoClimatica->des_icone = $request->des_icone;
        $condicaoClimatica->update();

        return redirect()->route('condicaoClimatica.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $condicaoClimatica = CondicaoClimatica::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $condicaoClimatica->delete();
        return redirect()->back();
    }
}
