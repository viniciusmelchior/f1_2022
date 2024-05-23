<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\r;
use App\Models\Site\Equipe;
use App\Models\Site\Skin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $model = Skin::where('user_id', Auth::user()->id)->get();

        return view('site.skins.index', compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $equipes = Equipe::where('user_id', Auth::user()->id)->get();

        return view('site.skins.form', compact('equipes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $skin = new Skin();
        $skin->skin = $request->skin;
        $skin->equipe_id = $request->equipe_id;
        $skin->user_id = Auth::user()->id;
        $skin->save();

        return redirect()->back()->with('status', 'Salvo com sucesso');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\r  $r
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\r  $r
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = Skin::find($id);
        $equipes = Equipe::where('user_id', Auth::user()->id)->get();

        return view('site.skins.form', compact('equipes', 'model'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\r  $r
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {   
        $skin = Skin::find($id);
        $skin->skin = $request->skin;
        $skin->equipe_id = $request->equipe_id;
        $skin->user_id = Auth::user()->id;
        $skin->update();

        return redirect()->back()->with('status', 'Salvo com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\r  $r
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }
}
