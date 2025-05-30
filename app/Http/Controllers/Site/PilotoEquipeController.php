<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Ano;
use App\Models\Site\Equipe;
use App\Models\Site\Pais;
use App\Models\Site\Piloto;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\Skin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PilotoEquipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $anos = Ano::where('user_id', Auth::user()->id)->orderBy('ano', 'DESC')->get();
        $pilotoEquipes = PilotoEquipe::where('user_id', Auth::user()->id)->orderBy('ano_id', 'DESC')->orderBy('equipe_id')->get();

        return view('site.pilotoEquipe.index', compact('pilotoEquipes', 'anos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $paises = Pais::where('user_id', Auth::user()->id)->get();
        $paises = Pais::where('user_id', 3)->get();
        // $pilotos = Piloto::where('user_id', Auth::user()->id)->where('flg_ativo', 'S')->get();
        $pilotos = Piloto::where('user_id', 3)->where('flg_ativo', 'S')->get();
        // $equipes = Equipe::where('user_id', Auth::user()->id)->where('flg_ativo', 'S')->get();
        $equipes = Equipe::where('user_id', 3)->where('flg_ativo', 'S')->get();
        // $anos = Ano::where('user_id', Auth::user()->id)->orderBy('ano', 'DESC')->get();
        $anos = Ano::where('user_id', 3)->orderBy('ano', 'DESC')->get();
        $skins = Skin::where('user_id', Auth::user()->id)->get();

        return view('site.pilotoEquipe.form', compact('paises', 'pilotos', 'equipes', 'anos', 'skins'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $pilotoEquipe = new PilotoEquipe();
        $pilotoEquipe->piloto_id = $request->piloto_id;
        $pilotoEquipe->equipe_id = $request->equipe_id;
        $pilotoEquipe->modelo_carro = $request->modelo_carro;
        $pilotoEquipe->user_id = Auth::user()->id;
        $pilotoEquipe->ano_id = $request->ano_id;
        $pilotoEquipe->flg_ativo = 'S';
        $pilotoEquipe->skin_id = $request->skin_id;

        if ($request->has('flg_super_corrida')) {
            $pilotoEquipe->flg_super_corrida = $request->flg_super_corrida;
        } else {
            $pilotoEquipe->flg_super_corrida = 'N';
        }

        $pilotoEquipe->save();

        return redirect()->back();
    }

    public function replicarPilotoEquipe(Request $request){
       try {
			// dd($request->all());

			$pilotoEquipeParaReplicar = PilotoEquipe::find($request->pilotoEquipe_id);

			if($pilotoEquipeParaReplicar){
				$pilotoEquipe = new PilotoEquipe();
				$pilotoEquipe->piloto_id = $pilotoEquipeParaReplicar->piloto_id;
				$pilotoEquipe->equipe_id = $pilotoEquipeParaReplicar->equipe_id;
				$pilotoEquipe->modelo_carro = $pilotoEquipeParaReplicar->modelo_carro;
				$pilotoEquipe->user_id = Auth::user()->id;
				$pilotoEquipe->ano_id = $request->ano_id;
				$pilotoEquipe->flg_ativo = 'S';
				$pilotoEquipe->skin_id = $pilotoEquipeParaReplicar->skin_id;

			if ($request->has('flg_super_corrida')) {
				$pilotoEquipe->flg_super_corrida = $pilotoEquipeParaReplicar->flg_super_corrida;
			} else {
				$pilotoEquipe->flg_super_corrida = 'N';
			}

			$pilotoEquipe->save();

			return redirect()->back()->with('status', 'Piloto/Equipe replicado com sucesso');;
			}
       } catch (\Throwable $th) {
        	throw $th;
       }
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
        $model = PilotoEquipe::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $pilotos = Piloto::where('user_id', Auth::user()->id)->where('flg_ativo', 'S')->get();
        $equipes = Equipe::where('user_id', Auth::user()->id)->where('flg_ativo', 'S')->get();
        $anos = Ano::where('user_id', Auth::user()->id)->orderBy('ano', 'DESC')->get();
        $skins = Skin::where('user_id', Auth::user()->id)->get();

        return view('site.pilotoEquipe.form', compact('model', 'pilotos', 'equipes', 'anos', 'skins'));
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
        $pilotoEquipe = PilotoEquipe::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $pilotoEquipe->piloto_id = $request->piloto_id;
        $pilotoEquipe->equipe_id = $request->equipe_id;
        $pilotoEquipe->modelo_carro = $request->modelo_carro;
        $pilotoEquipe->user_id = Auth::user()->id;
        $pilotoEquipe->ano_id = $request->ano_id;
        $pilotoEquipe->skin_id = $request->skin_id;
        if ($request->has('flg_ativo')) {
            $pilotoEquipe->flg_ativo = $request->flg_ativo;
        } else {
            $pilotoEquipe->flg_ativo = 'N';
        }

        if ($request->has('flg_super_corrida')) {
            $pilotoEquipe->flg_super_corrida = $request->flg_super_corrida;
        } else {
            $pilotoEquipe->flg_super_corrida = 'N';
        }

        $pilotoEquipe->update();

        return redirect()->route('pilotoEquipe.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pilotoEquipe = PilotoEquipe::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $pilotoEquipe->delete();
        return redirect()->back();
    }
}
