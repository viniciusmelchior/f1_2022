<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Continente;
use App\Models\Site\Corrida;
use App\Models\Site\Resultado;
use App\Models\Site\Pais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaisesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $paises = Pais::where('user_id', Auth::user()->id)->orderBy('des_nome')->get();
       $paises = Pais::where('user_id', 3)
                    ->whereHas('continente') // Isso verifica se o país tem um continente relacionado
                    ->orderBy('des_nome')
                    ->get();

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

        if ($request->imagem == '') {
            $newImageName = '';
        } else {
            $newImageName = time() . '-' . $request->nome . '.' . $request->imagem->extension();
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
        $pais = Pais::find($id);

        $totalVitoriasPorPiloto = DB::table('resultados')
                                    ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->join('pistas', 'pistas.id', '=', 'corridas.pista_id')
                                    ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                    ->join('pilotos', 'pilotos.id', '=', 'piloto_equipes.piloto_id')
                                    ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
                                    ->where('resultados.user_id',  Auth::user()->id)
                                    ->where('resultados.chegada', '>=', 1)
                                    ->where('resultados.chegada', '<=', 1)
                                    ->where('corridas.flg_sprint', 'N')
                                    ->where('pistas.pais_id',  $pais->id)
                                    ->select('pilotos.id as piloto_id',
                                                DB::raw("CONCAT(pilotos.nome, ' ', pilotos.sobrenome) as piloto_nome_completo"),
                                                DB::raw('COUNT(resultados.id) as vitorias'))
                                    ->groupBy('pilotos.id', 'pilotos.nome')
                                    ->orderByDesc('vitorias')
                                    ->get();

        $totalLargadasPorPiloto = DB::table('resultados')
                                    ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->join('pistas', 'pistas.id', '=', 'corridas.pista_id')
                                    ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                    ->join('pilotos', 'pilotos.id', '=', 'piloto_equipes.piloto_id')
                                    ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
                                    ->where('resultados.user_id',  Auth::user()->id)
                                    ->where('resultados.largada', '>=', 1)
                                    ->where('resultados.largada', '<=', 1)
                                    ->where('corridas.flg_sprint', 'N')
                                    ->where('pistas.pais_id',  $pais->id)
                                    ->select('pilotos.id as piloto_id',
                                                DB::raw("CONCAT(pilotos.nome, ' ', pilotos.sobrenome) as piloto_nome_completo"),
                                                DB::raw('COUNT(resultados.id) as largadas'))
                                    ->groupBy('pilotos.id', 'pilotos.nome')
                                    ->orderByDesc('largadas')
                                    ->get();
        
        $totalVitoriasPorEquipe = DB::table('resultados')
                                    ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->join('pistas', 'pistas.id', '=', 'corridas.pista_id')
                                    ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                    ->join('pilotos', 'pilotos.id', '=', 'piloto_equipes.piloto_id')
                                    ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
                                    ->where('resultados.user_id',  Auth::user()->id)
                                    ->where('resultados.chegada', '>=', 1)
                                    ->where('resultados.chegada', '<=', 1)
                                    ->where('corridas.flg_sprint', 'N')
                                    ->where('pistas.pais_id',  $pais->id)
                                    ->select('equipes.id as equipe_id',
                                                'equipes.nome as equipe_nome',
                                                DB::raw('COUNT(resultados.id) as vitorias'))
                                    ->groupBy('equipes.id', 'equipes.nome')
                                    ->orderByDesc('vitorias')
                                    ->get();

        $totalLargadasPorEquipe = DB::table('resultados')
                                    ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->join('pistas', 'pistas.id', '=', 'corridas.pista_id')
                                    ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                    ->join('pilotos', 'pilotos.id', '=', 'piloto_equipes.piloto_id')
                                    ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
                                    ->where('resultados.user_id',  Auth::user()->id)
                                    ->where('resultados.largada', '>=', 1)
                                    ->where('resultados.largada', '<=', 1)
                                    ->where('corridas.flg_sprint', 'N')
                                    ->where('pistas.pais_id',  $pais->id)
                                    ->select('equipes.id as equipe_id',
                                                'equipes.nome as equipe_nome',
                                                DB::raw('COUNT(resultados.id) as largadas'))
                                    ->groupBy('equipes.id', 'equipes.nome')
                                    ->orderByDesc('largadas')
                                    ->get();

        $resultadoCorridas = Corrida::where('user_id', Auth::user()->id)
            ->whereHas('pista', function ($query) use ($id) {
                $query->where('pais_id', $id);
            })
            ->orderBy('temporada_id', 'DESC')
            ->orderBy('ordem', 'DESC')
            ->get();

        if (count($resultadoCorridas) > 0) {
            return view('site.paises.show', compact('resultadoCorridas', 'totalVitoriasPorPiloto', 'totalVitoriasPorEquipe', 'totalLargadasPorPiloto', 'totalLargadasPorEquipe'));
        } else {
            return redirect()->route('paises.index')->with('error', 'Não existem corridas disputadas no país selecionado');
        }
    }

    /**
     * Função responsável por buscar e montar a tabela de chegadas, de acordo com o que foi colocado nos inputs de inicio e fim
     */
    public function ajaxGetChegadasPilotos(Request $request){
        
        try {

            $inicio = $request->inicio;
            $fim = $request->fim;
            $pais_id = $request->pais_id;
           
            $totalVitoriasPorPiloto = DB::table('resultados')
                                        ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                        ->join('pistas', 'pistas.id', '=', 'corridas.pista_id')
                                        ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                        ->join('pilotos', 'pilotos.id', '=', 'piloto_equipes.piloto_id')
                                        ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
                                        ->where('resultados.user_id',  Auth::user()->id)
                                        ->where('resultados.chegada', '>=', $inicio)
                                        ->where('resultados.chegada', '<=', $fim)
                                        ->where('corridas.flg_sprint', 'N')
                                        ->where('pistas.pais_id',  $pais_id)
                                        ->select('pilotos.id as piloto_id',
                                                    DB::raw("CONCAT(pilotos.nome, ' ', pilotos.sobrenome) as piloto_nome_completo"),
                                                    DB::raw('COUNT(resultados.id) as chegadas'))
                                        ->groupBy('pilotos.id', 'pilotos.nome')
                                        ->orderByDesc('chegadas')
                                        ->get();

            return response()->json([
                'message' => 'OK',
                'totalVitoriasPorPiloto' => $totalVitoriasPorPiloto
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Erro na requisição'
            ]);

        }

    }

    /**
     * Função responsável por buscar e montar a tabela de chegadas, de acordo com o que foi colocado nos inputs de inicio e fim
     */
    public function ajaxGetChegadasEquipes(Request $request){
        
        try {

            $inicio = $request->inicio;
            $fim = $request->fim;
            $pais_id = $request->pais_id;
           
            $totalVitoriasPorEquipe = DB::table('resultados')
                                    ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->join('pistas', 'pistas.id', '=', 'corridas.pista_id')
                                    ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                    ->join('pilotos', 'pilotos.id', '=', 'piloto_equipes.piloto_id')
                                    ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
                                    ->where('resultados.user_id',  Auth::user()->id)
                                    ->where('resultados.chegada', '>=', $inicio)
                                    ->where('resultados.chegada', '<=', $fim)
                                    ->where('corridas.flg_sprint', 'N')
                                    ->where('pistas.pais_id', $pais_id)
                                    ->select('equipes.id as equipe_id',
                                                'equipes.nome as equipe_nome',
                                                DB::raw('COUNT(resultados.id) as chegadas'))
                                    ->groupBy('equipes.id', 'equipes.nome')
                                    ->orderByDesc('chegadas')
                                    ->get();

            return response()->json([
                'message' => 'OK',
                'totalVitoriasPorEquipe' => $totalVitoriasPorEquipe
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Erro na requisição'
            ]);

        }

    }

    /**
     * Função responsável por buscar e montar a tabela de chegadas, de acordo com o que foi colocado nos inputs de inicio e fim
     */
    public function ajaxGetLargadasPilotos(Request $request){
        
        try {
            $inicio = $request->inicio;
            $fim = $request->fim;
            $pais_id = $request->pais_id;
           
            $totalLargadasPorPiloto = DB::table('resultados')
                                        ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                        ->join('pistas', 'pistas.id', '=', 'corridas.pista_id')
                                        ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                        ->join('pilotos', 'pilotos.id', '=', 'piloto_equipes.piloto_id')
                                        ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
                                        ->where('resultados.user_id',  Auth::user()->id)
                                        ->where('resultados.largada', '>=', $inicio)
                                        ->where('resultados.largada', '<=', $fim)
                                        ->where('corridas.flg_sprint', 'N')
                                        ->where('pistas.pais_id',  $pais_id)
                                        ->select('pilotos.id as piloto_id',
                                                    DB::raw("CONCAT(pilotos.nome, ' ', pilotos.sobrenome) as piloto_nome_completo"),
                                                    DB::raw('COUNT(resultados.id) as largadas'))
                                        ->groupBy('pilotos.id', 'pilotos.nome')
                                        ->orderByDesc('largadas')
                                        ->get();

            return response()->json([
                'message' => 'OK',
                'totalLargadasPorPiloto' => $totalLargadasPorPiloto
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Erro na requisição'
            ]);

        }

    }

    public function ajaxGetLargadasEquipes(Request $request){
        
        try {
            // dd("largadas Equipes");
            $inicio = $request->inicio;
            $fim = $request->fim;
            $pais_id = $request->pais_id;
           
            $totalLargadasPorEquipe = DB::table('resultados')
                                        ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                        ->join('pistas', 'pistas.id', '=', 'corridas.pista_id')
                                        ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                        ->join('pilotos', 'pilotos.id', '=', 'piloto_equipes.piloto_id')
                                        ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
                                        ->where('resultados.user_id',  Auth::user()->id)
                                        ->where('resultados.largada', '>=', $inicio)
                                        ->where('resultados.largada', '<=', $fim)
                                        ->where('corridas.flg_sprint', 'N')
                                        ->where('pistas.pais_id',  $pais_id)
                                        ->select('equipes.id as equipe_id',
                                                    'equipes.nome',
                                                    DB::raw('COUNT(resultados.id) as largadas'))
                                        ->groupBy('equipes.id', 'equipes.nome')
                                        ->orderByDesc('largadas')
                                        ->get();

            return response()->json([
                'message' => 'OK',
                'totalLargadasPorEquipe' => $totalLargadasPorEquipe
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Erro na requisição'
            ]);

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

        return view('site.paises.form', compact('model', 'continentes'));
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
        $pais->continente_id = $request->continente_id;

        //se tiver foto criar span no form pra nao precisar alterar,  caso queira, apagar a antiga e colocar outra
        if ($request->imagem == '') {
            $newImageName = '';
        } else {
            if (file_exists(public_path('images/' . $pais->imagem))) {
                if ($pais->imagem != null) {
                    unlink(public_path('images/' . $pais->imagem));
                }
            }
            $newImageName = time() . '-' . $request->nome . '.' . $request->imagem->extension();
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

    public function destroy(Request $request)
    {

        try {
            $id = $request->pais_id;
            $pais = Pais::where('id', $id)->where('user_id', Auth::user()->id)->first();
            $pais->delete();

            //apagando a foto do país
            if ($pais->imagem != null) {
                if (file_exists(public_path('images/' . $pais->imagem))) {
                    unlink(public_path('images/' . $pais->imagem));
                }
            }

            return redirect()->route('paises.index')->with('status', 'O pais ' . $pais->des_nome . ' foi excluído com sucesso');
        } catch (\Throwable $th) {
            return redirect()->route('paises.index')->with('error', 'Não foi possível excluir o país selecionado');
        }
    }
}
