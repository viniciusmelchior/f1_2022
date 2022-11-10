<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Ano;
use App\Models\Site\Temporada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TemporadaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();

        return view('site.temporadas.index', compact('temporadas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $anos = Ano::where('user_id', Auth::user()->id)->get();
        return view('site.temporadas.form', compact('anos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $temporada = new Temporada();
        $temporada->des_temporada = $request->des_temporada;
        $temporada->user_id = Auth::user()->id;
        $temporada->ano_id = $request->ano_id;
        $temporada->flg_finalizada = 'N';

        $temporada->save();

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
        //aqui exibe as corridas
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = Temporada::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $anos = Ano::where('user_id', Auth::user()->id)->get();
        return view('site.temporadas.form', compact('model','anos'));
    }

    public function classificacao($id){
        $usuario = Auth::user()->id; 
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $id)->first();

        $resultadosPilotos = DB::select('select piloto_id, concat(pilotos.nome, " ", pilotos.sobrenome) as nome, equipes.nome as equipe, sum(pontuacao) as total from resultados
                                            join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                            join pilotos on pilotos.id = piloto_equipes.piloto_id
                                            join equipes on equipes.id = piloto_equipes.equipe_id
                                            join corridas on corridas.id = resultados.corrida_id
                                            join temporadas on temporadas.id = corridas.temporada_id
                                            where temporadas.id = '.$temporada->id.'
                                            and resultados.user_id = '.$usuario.'
                                            group by piloto_equipes.piloto_id
                                            order by total desc');
            
        $resultadosEquipes = DB::select('select equipe_id, equipes.nome as nome, sum(pontuacao) as total from resultados
                            join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                            join equipes on equipes.id = piloto_equipes.equipe_id
                            join corridas on corridas.id = resultados.corrida_id
                            join temporadas on temporadas.id = corridas.temporada_id
                            where temporadas.id = '.$temporada->id.'
                            and resultados.user_id = '.$usuario.'
                            group by piloto_equipes.equipe_id
                            order by total desc');

        return view('site.temporadas.classificacao', compact('temporada', 'resultadosPilotos','resultadosEquipes'));
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
        $temporada = Temporada::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $temporada->des_temporada = $request->des_temporada;
        $temporada->user_id = Auth::user()->id;
        $temporada->ano_id = $request->ano_id;
        if ($request->has('flg_finalizada')) {
            $temporada->flg_finalizada = $request->flg_finalizada;
        } else {
            $temporada->flg_finalizada = 'N';
        }

        $temporada->update();

        return redirect()->route('temporadas.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $temporada = Temporada::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $temporada->delete();
        return redirect()->back();
    }
}
