<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Ano;
use App\Models\Site\Equipe;
use App\Models\Site\Pais;
use App\Models\Site\Piloto;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\Resultado;
use App\Models\Site\Skin;
use App\Models\Site\Temporada;
use App\Models\Site\Titulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PilotoEquipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userId = Auth::user()->id;
        $anos = Ano::where('user_id', $userId)->orderBy('ano', 'DESC')->get();
        $temporadas = Temporada::where('user_id', $userId)->get(['ano_id', 'des_temporada', 'referencia'])->keyBy('ano_id');

        $filtros = [
            'ano_id' => $request->input('ano_id'),
            'busca' => trim((string) $request->input('busca')),
            'status' => in_array($request->input('status'), ['S', 'N']) ? $request->input('status') : '',
        ];

        $consulta = PilotoEquipe::where('user_id', $userId)
            ->when($filtros['ano_id'], fn ($query, $anoId) => $query->where('ano_id', $anoId))
            ->when($filtros['status'], fn ($query, $status) => $query->where('flg_ativo', $status))
            ->when($filtros['busca'] !== '', function ($query) use ($filtros) {
                $termo = '%'.$filtros['busca'].'%';
                $query->where(function ($query) use ($termo) {
                    $query->whereHas('piloto', fn ($piloto) => $piloto->whereRaw("CONCAT(nome, ' ', sobrenome) LIKE ?", [$termo]))
                          ->orWhereHas('equipe', fn ($equipe) => $equipe->where('nome', 'LIKE', $termo));
                });
            });

        //a paginação é por temporada (ano), para que as duplas de uma mesma temporada fiquem sempre juntas;
        //com busca por piloto/equipe cada temporada tem poucas linhas, então mostra mais temporadas por página
        $anosPaginados = Ano::where('user_id', $userId)
            ->whereIn('id', (clone $consulta)->select('ano_id'))
            ->orderBy('ano', 'DESC')
            ->paginate($filtros['busca'] !== '' ? 10 : 3)
            ->withQueryString();

        $pilotoEquipes = (clone $consulta)
            ->whereIn('ano_id', $anosPaginados->pluck('id'))
            ->with(['piloto', 'equipe', 'skin'])
            ->get()
            ->groupBy('ano_id');

        return view('site.pilotoEquipe.index', compact('pilotoEquipes', 'anos', 'anosPaginados', 'temporadas', 'filtros'));
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

    /**
     * Replica uma ou mais duplas (piloto/equipe) para a temporada de destino.
     * Aceita pilotoEquipe_ids[] (várias) e também pilotoEquipe_id (uma só, como era antes).
     * Pilotos que já estão na mesma equipe na temporada de destino não são duplicados.
     */
    public function replicarPilotoEquipe(Request $request){
       try {
			$ids = (array) $request->input('pilotoEquipe_ids', []);
			if ($request->filled('pilotoEquipe_id')) {
				$ids[] = $request->pilotoEquipe_id;
			}
			$ids = array_unique(array_filter($ids));

			if (empty($ids) || !$request->filled('ano_id')) {
				return redirect()->back()->with('error', 'Selecione ao menos um piloto e a temporada de destino.');
			}

			$pilotoEquipesParaReplicar = PilotoEquipe::where('user_id', Auth::user()->id)->whereIn('id', $ids)->with(['piloto', 'equipe'])->get();

			$jaCadastrados = PilotoEquipe::where('user_id', Auth::user()->id)
										->where('ano_id', $request->ano_id)
										->get(['piloto_id', 'equipe_id'])
										->map(fn ($item) => $item->piloto_id.'-'.$item->equipe_id)
										->all();

			$replicados = 0;
			$ignorados = [];

			foreach ($pilotoEquipesParaReplicar as $pilotoEquipeParaReplicar) {
				$chave = $pilotoEquipeParaReplicar->piloto_id.'-'.$pilotoEquipeParaReplicar->equipe_id;
				if (in_array($chave, $jaCadastrados)) {
					$ignorados[] = $pilotoEquipeParaReplicar->piloto->nomeCompleto().' ('.$pilotoEquipeParaReplicar->equipe->nome.')';
					continue;
				}

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
				$jaCadastrados[] = $chave;
				$replicados++;
			}

			$ano = Ano::find($request->ano_id);
			$mensagem = $replicados.($replicados == 1 ? ' piloto replicado' : ' pilotos replicados').' para '.$ano?->ano.'.';
			if (count($ignorados) > 0) {
				$mensagem .= ' Já estavam cadastrados e foram ignorados: '.implode(', ', $ignorados).'.';
			}

			return redirect()->back()->with('status', $mensagem);
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

    /**
     * Exclui as duplas selecionadas na listagem.
     * Duplas com resultados ou títulos lançados não são excluídas (chave estrangeira) e são informadas na mensagem.
     */
    public function destroySelecionados(Request $request)
    {
        $ids = array_unique(array_filter((array) $request->input('pilotoEquipe_ids', [])));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Selecione ao menos um piloto para excluir.');
        }

        $pilotoEquipes = PilotoEquipe::where('user_id', Auth::user()->id)->whereIn('id', $ids)->with(['piloto', 'equipe', 'ano'])->get();

        $comVinculo = Resultado::whereIn('pilotoEquipe_id', $pilotoEquipes->pluck('id'))->pluck('pilotoEquipe_id')
            ->merge(Titulo::whereIn('pilotoEquipe_id', $pilotoEquipes->pluck('id'))->pluck('pilotoEquipe_id'))
            ->unique()
            ->all();

        $referencias = Temporada::where('user_id', Auth::user()->id)->pluck('referencia', 'ano_id');

        $excluidos = 0;
        $ignorados = [];

        foreach ($pilotoEquipes as $pilotoEquipe) {
            if (in_array($pilotoEquipe->id, $comVinculo)) {
                $ignorados[] = $pilotoEquipe->piloto->nomeCompleto().' ('.$pilotoEquipe->equipe->nome.' · '.($referencias[$pilotoEquipe->ano_id] ?? null ?: $pilotoEquipe->ano?->ano).')';
                continue;
            }

            $pilotoEquipe->delete();
            $excluidos++;
        }

        $mensagem = $excluidos.($excluidos == 1 ? ' piloto excluído.' : ' pilotos excluídos.');
        if (count($ignorados) > 0) {
            $mensagem .= ' Não foram excluídos por já terem resultados ou títulos lançados: '.implode(', ', $ignorados).'.';
        }

        return redirect()->back()->with($excluidos > 0 ? 'status' : 'error', $mensagem);
    }
}
