<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Autor;
use App\Models\Site\Corrida;
use App\Models\Site\Pais;
use App\Models\Site\Pista;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use App\Services\PistaHistoricoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        // $pistas = Pista::where('user_id', Auth::user()->id)->orderBy('nome')->get();
        $pistas = Pista::with(['pais.continente'])->where('user_id', 3)->orderBy('nome')->get();

        // MELHORIA 20261117: contagens numa consulta só (antes eram ~4 consultas por pista na view)
        $estatisticas = [];
        $linhas = DB::table('corridas as c')
            ->leftJoin('temporadas as t', 't.id', '=', 'c.temporada_id')
            ->where('c.user_id', Auth::user()->id)
            ->orderBy('c.temporada_id')->orderBy('c.ordem')->orderBy('c.id')
            ->get(['c.pista_id', 'c.flg_sprint', 'c.temporada_id', 't.des_temporada', 't.referencia']);
        foreach ($linhas as $l) {
            $e = &$estatisticas[$l->pista_id];
            $e ??= ['corridas' => 0, 'sprints' => 0, 'referencias' => [], 'ultima' => null];
            $e[$l->flg_sprint === 'S' ? 'sprints' : 'corridas']++;
            if (filled($l->referencia)) {
                $e['referencias'][trim($l->referencia)] = true;
            }
            // linhas em ordem cronológica: a última lida é a corrida mais recente
            $e['ultima'] = ['temporada' => $l->des_temporada ?? '-', 'referencia' => $l->referencia, 'ordem' => (int) $l->temporada_id];
            unset($e);
        }
        foreach ($estatisticas as &$e) {
            $e['referencias'] = array_keys($e['referencias']);
            sort($e['referencias'], SORT_NATURAL);
        }
        unset($e);

        // anos de referência usados nas temporadas do usuário (filtro "já recebeu / nunca recebeu")
        $referencias = Temporada::where('user_id', Auth::user()->id)->whereNotNull('referencia')->where('referencia', '<>', '')
            ->pluck('referencia')->map(fn ($r) => trim($r))->unique()->sort(SORT_NATURAL)->values();

        return view('site.pistas.index', compact('pistas', 'estatisticas', 'referencias'));
    }

    /**
     * MELHORIA 20261117: histórico e estatísticas da pista (corridas e sprints separadas,
     * anos de referência já recebidos/nunca recebidos, curiosidades e todas as corridas).
     */
    public function historico($id)
    {
        $pista = Pista::findOrFail($id);
        $dados = (new PistaHistoricoService())->gerar(Auth::user()->id, $pista);

        return view('site.pistas.historico', compact('dados', 'pista'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $paises = Pais::where('user_id', Auth::user()->id)->get();
        $autores = Autor::where('user_id', Auth::user()->id)->orderBy('nome')->get();
        $modelDrs = Pista::DRS;
        $tipos = Pista::TIPO;

        return view('site.pistas.form', compact('paises', 'autores', 'modelDrs', 'tipos'));
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
        $pista->autor_id = $request->autor_id;
        $pista->drs = $request->drs;
        $pista->tipo = $request->tipo;
        $pista->flg_ativo = 'S';
        // if ($request->has('flg_ativo')) {
        //     $pista->flg_ativo = $request->flg_ativo;
        // } else {
        //     $pista->flg_ativo = 'N';
        // }

        $pista->tamanho_km = $request->tamanho_km;
        //calculo de voltas. Distância 50% em corridas da Indy e F1 (outras categorias, desconsiderar)
        $distanciaOficial = 300;
        $pista->qtd_voltas = round((($distanciaOficial / $pista->tamanho_km) * 1000) / 2) + 1;

        $pista->save();

        return redirect()->back()->with('success', 'Pista ' . $pista->nome . ' cadastrada com sucesso.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = Corrida::where('pista_id', $id)->get();

        if (count($model) == 0) {
            return redirect()->back()->with('error', 'Não existem corridas cadastradas para a pista selecionada');
        }

        // MELHORIA 20261117: tela remodelada, dados das corridas vêm do mesmo service do histórico
        $pista = Pista::findOrFail($id);
        $dados = (new PistaHistoricoService())->gerar(Auth::user()->id, $pista);

        return view('site.pistas.show', compact('id', 'dados'));
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
        $autores = Autor::where('user_id', Auth::user()->id)->orderBy('nome')->get();
        $modelDrs = Pista::DRS;
        $tipos = Pista::TIPO;
        return view('site.pistas.form', compact('model', 'paises', 'autores', 'modelDrs', 'tipos'));
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
        $pista->autor_id = $request->autor_id;
        $pista->drs = $request->drs;
        $pista->tipo = $request->tipo;
        if ($request->has('flg_ativo')) {
            $pista->flg_ativo = $request->flg_ativo;
        } else {
            $pista->flg_ativo = 'N';
        }

        $pista->tamanho_km = $request->tamanho_km;
        //calculo de voltas. Distância 50% em corridas da Indy e F1 (outras categorias, desconsiderar)
        $distanciaOficial = 300;
        $pista->qtd_voltas = round((($distanciaOficial / $pista->tamanho_km) * 1000) / 2) + 1;
        //dd($pista->qtd_voltas, $distanciaOficial, $pista->tamanho_km);

        $pista->update();

        return redirect()->route('pistas.index');
    }

    public function adicionarAutor(Request $request)
    {
       
        try {

            $model = new Autor();
            $model->nome = $request->autor;
            $model->user_id = Auth::user()->id;
            $model->save();

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Autor já cadastrado anteriormente');
        }

        // if($model == true){
        //     return redirect()->back()->with('error', 'Autor já cadastrado anteriormente');
        // }

        return redirect()->back();
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
