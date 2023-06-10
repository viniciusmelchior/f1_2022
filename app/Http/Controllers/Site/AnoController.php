<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Ano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $anos = Ano::where('user_id', Auth::user()->id)->get();
        
        return view('site.anos.index', compact('anos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('site.anos.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ano = new Ano();
        $ano->user_id = Auth::user()->id;
        $ano->ano = $request->ano;
       /*  if ($request->has('flg_ativo')) {
            $ano->flg_ativo = $request->flg_ativo;
        } else {
            $ano->flg_ativo = 'N';
        } */

        $ano->flg_ativo = 'S';

        $ano->save();

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
        $model = Ano::where('user_id', Auth::user()->id)->where('id', $id)->first();
        return view('site.anos.form', compact('model'));
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
        $ano = Ano::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $ano->user_id = Auth::user()->id;
        $ano->ano = $request->ano;
        if ($request->has('flg_ativo')) {
            $ano->flg_ativo = $request->flg_ativo;
        } else {
            $ano->flg_ativo = 'N';
        }

        $ano->update();

        return redirect()->route('anos.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ano = Ano::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $ano->delete();
        return redirect()->back();
    }
}
