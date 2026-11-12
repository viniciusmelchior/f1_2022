<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Corrida;
use App\Models\Site\Equipe;
use App\Models\Site\ForcaEquipe;
use App\Models\Site\Pais;
use App\Models\Site\Piloto;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use App\Models\Site\Titulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $equipes = Equipe::buscaEstatisticasListagemequipes(Auth::user()->id);
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
        // if ($request->has('flg_ativo')) {
        //     $equipe->flg_ativo = $request->flg_ativo;
        // } else {
        //     $equipe->flg_ativo = 'N';
        // }

        $equipe->flg_ativo = 'S';

        if($request->imagem == ''){
            $newImageName = '';
        } else {
           $newImageName = time().'-'.$request->nome.'.'.$request->imagem->extension();
           $request->imagem->move(public_path('images'), $newImageName);
        }

        $equipe->imagem = $newImageName;

        $equipe->save();

        return redirect()->back()->with('status', 'A Equipe '.$equipe->nome.' foi registrada');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * MELHORIA 20261105: perfil da equipe no padrão da tela de pilotos.
     * Antes eram milhares de consultas (N+1, ~7s); agora são poucas consultas agrupadas e a tela
     * monta as estatísticas no navegador, inclusive ao trocar a temporada (sem AJAX).
     * A versão anterior está em EquipeController.php_20261105.
     */
    public function show($id)
    {
        $userId = Auth::user()->id;

        $modelEquipe = Equipe::with('pais')
                                ->where('user_id', Auth::user()->id)
                                ->where('id', $id)
                                ->firstOrFail();

        //todos os resultados da equipe (GP e sprint) em uma única consulta, já em ordem cronológica
        $linhas = DB::table('resultados')
                    ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                    ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
                    ->where('resultados.user_id', $userId)
                    ->where('piloto_equipes.equipe_id', $modelEquipe->id)
                    ->orderBy('corridas.temporada_id')
                    ->orderBy('corridas.ordem')
                    ->orderBy('corridas.id')
                    ->orderBy('resultados.id')
                    ->get([
                        'resultados.corrida_id', 'resultados.pilotoEquipe_id', 'resultados.largada', 'resultados.chegada',
                        'resultados.pontuacao', 'resultados.flg_abandono',
                        'piloto_equipes.piloto_id',
                        'corridas.temporada_id', 'corridas.ordem', 'corridas.flg_sprint', 'corridas.pista_id',
                        'corridas.evento_id', 'corridas.volta_rapida',
                    ]);

        //corridas: [temporada_id, ordem, sprint, pista_id, evento_id]
        //resultados: [corrida_id, piloto_id, largada, chegada, pontos, abandono, volta_rapida]
        $corridas = [];
        $resultados = [];
        foreach($linhas as $l){
            $corridas[$l->corrida_id] = [(int) $l->temporada_id, (int) $l->ordem, $l->flg_sprint == 'S' ? 1 : 0, (int) $l->pista_id, $l->evento_id ? (int) $l->evento_id : null];
            $resultados[] = [
                (int) $l->corrida_id,
                (int) $l->piloto_id,
                $l->largada ? (int) $l->largada : null,
                $l->chegada ? (int) $l->chegada : null,
                (float) $l->pontuacao,
                $l->flg_abandono == 'S' ? 1 : 0,
                $l->volta_rapida && $l->volta_rapida == $l->pilotoEquipe_id ? 1 : 0,
            ];
        }

        //duplas da equipe por ano (inclui temporadas ainda sem resultados)
        $duplas = DB::table('piloto_equipes')
                    ->where('user_id', $userId)
                    ->where('equipe_id', $modelEquipe->id)
                    ->orderBy('id')
                    ->get(['piloto_id', 'ano_id']);

        $temporadasIds = collect($corridas)->pluck(0)->unique()->values();
        $temporadas = DB::table('temporadas')
                        ->where('user_id', $userId)
                        ->where(fn ($q) => $q->whereIn('id', $temporadasIds)->orWhereIn('ano_id', $duplas->pluck('ano_id')->unique()->values()))
                        ->orderBy('id')
                        ->get(['id', 'des_temporada', 'referencia', 'ano_id']);

        //títulos: construtores (titulos.equipe_id) e de pilotos (campeão correndo pela equipe)
        $titulosConstrutores = DB::table('titulos')
                                ->where('user_id', $userId)
                                ->where('equipe_id', $modelEquipe->id)
                                ->pluck('temporada_id')->map(fn ($t) => (int) $t)->values();

        $titulosPilotos = DB::table('titulos')
                            ->join('piloto_equipes', 'piloto_equipes.id', '=', 'titulos.pilotoEquipe_id')
                            ->where('titulos.user_id', $userId)
                            ->where('piloto_equipes.equipe_id', $modelEquipe->id)
                            ->get(['titulos.temporada_id', 'piloto_equipes.piloto_id'])
                            ->map(fn ($t) => [(int) $t->temporada_id, (int) $t->piloto_id])->values();

        // MELHORIA 20261110: títulos da classificação exclusiva das sprints
        $titulosSprintConstrutores = DB::table('titulos_sprint')
                                ->where('user_id', $userId)
                                ->where('equipe_id', $modelEquipe->id)
                                ->pluck('temporada_id')->map(fn ($t) => (int) $t)->values();

        $titulosSprintPilotos = DB::table('titulos_sprint')
                            ->join('piloto_equipes', 'piloto_equipes.id', '=', 'titulos_sprint.pilotoEquipe_id')
                            ->where('titulos_sprint.user_id', $userId)
                            ->where('piloto_equipes.equipe_id', $modelEquipe->id)
                            ->get(['titulos_sprint.temporada_id', 'piloto_equipes.piloto_id'])
                            ->map(fn ($t) => [(int) $t->temporada_id, (int) $t->piloto_id])->values();

        // MELHORIA 20261110 (playoff): títulos do mata-mata conquistados por pilotos da equipe
        $titulosPlayoffPilotos = DB::table('titulos_playoff')
                            ->where('user_id', $userId)
                            ->where('equipe_id', $modelEquipe->id)
                            ->pluck('temporada_id')->map(fn ($t) => (int) $t)->values();

        //pilotos, pistas e eventos em dicionários para deixar o JSON enxuto
        $pilotosIds = collect($resultados)->pluck(1)->merge($duplas->pluck('piloto_id'))->unique()->values();
        $pilotos = DB::table('pilotos')
                    ->leftJoin('paises', 'paises.id', '=', 'pilotos.pais_id')
                    ->whereIn('pilotos.id', $pilotosIds)
                    ->get(['pilotos.id', 'pilotos.nome', 'pilotos.sobrenome', 'pilotos.imagem', 'paises.imagem as bandeira'])
                    ->mapWithKeys(fn ($p) => [$p->id => [
                        'nome' => trim($p->nome.' '.$p->sobrenome),
                        'img' => $p->imagem ? asset('images/'.$p->imagem) : null,
                        'bandeira' => $p->bandeira ? asset('images/'.$p->bandeira) : null,
                        'link' => route('pilotos.show', [$p->id]),
                    ]]);

        $pistas = DB::table('pistas')
                    ->leftJoin('paises', 'paises.id', '=', 'pistas.pais_id')
                    ->whereIn('pistas.id', collect($corridas)->pluck(3)->unique()->values())
                    ->get(['pistas.id', 'pistas.nome', 'paises.imagem as bandeira'])
                    ->mapWithKeys(fn ($p) => [$p->id => [
                        'nome' => $p->nome,
                        'bandeira' => $p->bandeira ? asset('images/'.$p->bandeira) : null,
                    ]]);

        $eventos = DB::table('eventos')
                    ->whereIn('id', collect($corridas)->pluck(4)->filter()->unique()->values())
                    ->pluck('des_nome', 'id');

        //posição no campeonato de construtores: mesma regra da tela de classificação, uma única vez por temporada disputada
        $dadosTemporadas = $temporadas->map(function ($t) use ($modelEquipe, $duplas, $temporadasIds) {
            $campeonato = $temporadasIds->contains($t->id)
                            ? $this->posicaoCampeonatoConstrutores($t->id, $modelEquipe->id)
                            : ['posicaoEquipe' => '-', 'totalPontos' => '-'];
            $nome = strpos($t->des_temporada, ' ') !== false ? substr($t->des_temporada, 0, strpos($t->des_temporada, ' ')) : $t->des_temporada;
            return [
                'id' => (int) $t->id,
                'nome' => $nome,
                'completo' => $t->des_temporada.($t->referencia ? ' ('.$t->referencia.')' : ''),
                'ano' => $t->referencia,
                'posicao' => $campeonato['posicaoEquipe'],
                'pontos' => $campeonato['totalPontos'],
                'pilotos' => $duplas->where('ano_id', $t->ano_id)->pluck('piloto_id')->map(fn ($p) => (int) $p)->unique()->values(),
                'classificacao' => route('temporadas.classificacao', [$t->id]),
            ];
        })->values();

        $dadosEquipe = [
            'temporadas' => $dadosTemporadas,
            'pilotos' => $pilotos,
            'pistas' => $pistas,
            'eventos' => $eventos,
            'corridas' => (object) $corridas,
            'resultados' => $resultados,
            'titulosConstrutores' => $titulosConstrutores,
            'titulosPilotos' => $titulosPilotos,
            'titulosSprintConstrutores' => $titulosSprintConstrutores,
            'titulosSprintPilotos' => $titulosSprintPilotos,
            'titulosPlayoffPilotos' => $titulosPlayoffPilotos,
            'linkCorrida' => route('resultados.show', ['__ID__']),
        ];

        return view('site.equipes.show', compact('modelEquipe', 'dadosEquipe', 'dadosTemporadas'));
    }

    /**
     * MELHORIA 20261105: posição/pontos da equipe no campeonato de construtores.
     * Mesmo SQL e mesma ordenação (pontos + desempate por chegadas) de Temporada::getClassificacao()['resultadoEquipes'],
     * usado por Equipe::getInfoCampeonato, mas sem montar a classificação de pilotos (que dobrava o tempo da tela).
     */
    private function posicaoCampeonatoConstrutores($temporadaId, $equipeId)
    {
        $colunas = [];
        $ordenacao = '';
        for($cont = 1; $cont <= 43; $cont++){
            $colunas[] = '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = '.$cont.' and corridas.flg_sprint = "N") AS posicao_'.$cont;
            $ordenacao .= ', posicao_'.$cont.' desc';
        }

        $classificacao = DB::select('select equipe_id, sum(pontuacao) as total,'.implode(',', $colunas).' from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        where temporadas.id = ?
                                        and resultados.user_id = ?
                                        and (corridas.flg_sprint is null or corridas.flg_sprint <> "S")
                                        group by piloto_equipes.equipe_id
                                        order by total desc '.$ordenacao, [(int) $temporadaId, Auth::user()->id]);

        foreach($classificacao as $key => $item){
            if($item->equipe_id == $equipeId){
                return ['posicaoEquipe' => $key + 1, 'totalPontos' => $item->total];
            }
        }

        return ['posicaoEquipe' => '-', 'totalPontos' => '-'];
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

        //se tiver foto criar span no form pra nao precisar alterar,  caso queira, apagar a antiga e colocar outra
        if($request->imagem == ''){
            $newImageName = '';
        } else {
            if(file_exists(public_path('images/'.$equipe->imagem))){
                if($equipe->imagem != null){
                    unlink(public_path('images/'.$equipe->imagem));
                }
            }
           $newImageName = time().'-'.$request->nome.'.'.$request->imagem->extension();
           $request->imagem->move(public_path('images'), $newImageName);
           $equipe->imagem = $newImageName;
        }

        $equipe->update();

        return redirect()->route('equipes.index')->with('status', 'A Equipe '.$equipe->nome.' foi editada');
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

    public function relacaoForcasIndex(){
        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();

        return view('site.equipes.relacaoForcas.index', compact('temporadas'));
    }

    public function relacaoForcasIndexListagemEquipes($temporada_id){
        
        $temporada = Temporada::find($temporada_id);
       
        $equipes = PilotoEquipe::with('equipe')
                        ->where('user_id', Auth::user()->id)
                        ->where('ano_id', $temporada->ano->id)
                        ->orderBy('id')
                        ->groupBy('equipe_id')
                        ->get();

        return view('site.equipes.relacaoForcas.listagemEquipes', compact('equipes', 'temporada'));

    }

    public function relacaoForcasEdit($ano_id, $equipe_id){

        $equipe = Equipe::find($equipe_id);

        $model = ForcaEquipe::where('equipe_id', $equipe_id)
                            ->where('ano_id', $ano_id)
                            ->where('user_id', Auth::user()->id)
                            ->first();

        return view('site.equipes.relacaoForcas.form', compact('equipe', 'ano_id', 'model'));
    }

    public function relacaoForcasUpdate(Request $request){

        $model = ForcaEquipe::where('equipe_id', $request->equipe_id)
                            ->where('ano_id', $request->ano_id)
                            ->where('user_id', Auth::user()->id)
                            ->first();

        if(isset($model)){
            $model->forca = $request->forca;
            $model->update();
        }else{
            $newModel = new ForcaEquipe;
            $newModel->equipe_id = $request->equipe_id;
            $newModel->ano_id = $request->ano_id;
            $newModel->user_id = Auth::user()->id;
            $newModel->forca = $request->forca;
            $newModel->save();
        }
               
        return redirect()->back()->with('status', 'Salvo com sucesso');
    }


}
