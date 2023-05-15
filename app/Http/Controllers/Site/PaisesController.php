<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
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
        return view('site.paises.form');
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
        return view('site.paises.form', compact('model'));
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
    public function destroy($id)
    {
        $pais = Pais::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $pais->delete();
        return redirect()->back();
    }
}
