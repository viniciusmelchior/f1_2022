@php
    use App\Models\Site\Equipe;
@endphp

@extends('layouts.main')

@section('section')

<style>
    .pe-pagina { padding-bottom: 90px; }

    .pe-filtros {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 12px;
        align-items: end;
    }

    @media (min-width: 768px) {
        .pe-filtros { grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.5fr) minmax(0, 0.8fr) auto; }
    }

    .pe-filtros .form-label { font-weight: 600; font-size: 13px; margin-bottom: 4px; }

    .pe-filtros-acoes { display: flex; gap: 8px; }
    .pe-filtros-acoes .btn { flex: 1; white-space: nowrap; }

    .pe-temporada { border: 1px solid #dee2e6; border-radius: 10px; margin-bottom: 20px; overflow: hidden; background: #fff; }

    .pe-temporada-cabecalho {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px 16px;
        padding: 12px 16px;
        background-color: #11101d;
        color: #fff;
    }

    .pe-temporada-titulo { font-size: 18px; font-weight: 700; margin: 0; }
    .pe-temporada-titulo small { font-weight: 400; color: #c5c5d2; font-size: 14px; }
    .pe-temporada-info { font-size: 13px; color: #c5c5d2; }

    .pe-check-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        margin: 0;
        user-select: none;
    }

    .pe-check-label input { width: 18px; height: 18px; cursor: pointer; flex-shrink: 0; }

    .pe-equipe {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        border-top: 1px solid #eef0f2;
    }

    .pe-equipe:first-child { border-top: none; }

    @media (min-width: 992px) {
        .pe-equipe { grid-template-columns: 260px minmax(0, 1fr); }
    }

    .pe-equipe-nome {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        background-color: #f8f9fa;
        font-weight: 700;
    }

    @media (min-width: 992px) {
        .pe-equipe-nome { border-right: 1px solid #eef0f2; }
    }

    .pe-icone-equipe {
        width: 30px;
        height: 30px;
        padding: 3px;
        border-radius: 50%;
        object-fit: contain;
        box-shadow: 0 0 0 1px rgba(0,0,0,.15);
        flex-shrink: 0;
    }

    .pe-pilotos { list-style: none; margin: 0; padding: 0; }

    .pe-piloto {
        display: grid;
        grid-template-columns: auto 34px minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        border-top: 1px dashed #eef0f2;
    }

    .pe-piloto:first-child { border-top: none; }
    .pe-piloto.pe-selecionado { background-color: #e8f1ff; }
    .pe-piloto.pe-inativo .pe-piloto-nome { color: #8a8f98; text-decoration: line-through; }

    .pe-foto {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        background-color: #e9ecef;
        box-shadow: 0 0 0 1px rgba(0,0,0,.1);
    }

    .pe-piloto-nome { font-weight: 600; }
    .pe-piloto-extra { font-size: 12px; color: #6c757d; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .pe-acoes { display: inline-flex; gap: 4px; }

    /* Barra de seleção fixa no rodapé */
    .pe-barra-selecao {
        position: fixed;
        /* centraliza na área de conteúdo, descontando o menu lateral recolhido (78px) */
        left: calc(50% + 39px);
        bottom: 16px;
        transform: translateX(-50%);
        z-index: 1040;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: calc(100% - 78px - 32px);
        max-width: 620px;
        padding: 10px 14px;
        border-radius: 10px;
        background-color: #11101d;
        color: #fff;
        box-shadow: 0 6px 20px rgba(0,0,0,.3);
    }

    .pe-barra-selecao strong { margin-right: auto; }

    @media (max-width: 575.98px) {
        .pe-barra-selecao strong { margin-right: 0; width: 100%; text-align: center; }
        .pe-piloto { grid-template-columns: auto 30px minmax(0, 1fr); padding: 8px 12px; }
        .pe-piloto .pe-acoes { grid-column: 2 / -1; }
        .pe-foto { width: 30px; height: 30px; }
        .pe-equipe-nome { padding: 10px 12px; }
    }

    .pe-paginacao {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 16px;
    }

    /* o texto "Showing x to y of z results" da view padrão do Laravel fica em inglês; uso o resumo próprio acima */
    .pe-paginacao nav > div.d-sm-flex > div:first-child,
    .pe-paginacao nav > div.d-sm-none { display: none !important; }
    .pe-paginacao nav > div.d-sm-flex { display: flex !important; }
    .pe-paginacao .pagination { margin: 0; flex-wrap: wrap; }

    .pe-lista-selecionados { max-height: 220px; overflow-y: auto; padding-left: 18px; margin-bottom: 0; }

    #modalReplicar .select2-container { width: 100% !important; }

    /* Select2 com o mesmo visual dos inputs .form-control (altura, borda, raio e fonte) */
    .pe-filtros .select2-container .select2-selection--single,
    #modalReplicar .select2-container .select2-selection--single {
        height: calc(1.5em + .75rem + 2px);
        border: 1px solid #ced4da;
        border-radius: .375rem;
        background: #fff;
        display: flex;
        align-items: center;
    }
    .pe-filtros .select2-container .select2-selection__rendered,
    #modalReplicar .select2-container .select2-selection__rendered {
        padding-left: .75rem;
        padding-right: 2rem;
        font-size: 1rem;
        color: #212529;
        line-height: 1.5;
        width: 100%;
    }
    .pe-filtros .select2-container .select2-selection__arrow,
    #modalReplicar .select2-container .select2-selection__arrow {
        height: 100%;
        right: .5rem;
    }
    .pe-filtros .select2-container--open .select2-selection--single,
    .pe-filtros .select2-container--focus .select2-selection--single,
    #modalReplicar .select2-container--open .select2-selection--single,
    #modalReplicar .select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
    }
</style>

<div class="container-fluid mt-3 mb-3 pe-pagina">

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pilotos e Equipes por Temporada</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="m-0">Pilotos e Equipes por Temporada</h4>
        <a href="{{route('pilotoEquipe.create')}}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Adicionar dupla</a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{route('pilotoEquipe.index')}}" class="card mb-3">
        <div class="card-body pe-filtros">
            <div>
                <label for="filtroAno" class="form-label">Temporada</label>
                <select name="ano_id" id="filtroAno" class="form-select">
                    <option value="">Todas as temporadas</option>
                    @foreach($anos as $ano)
                        <option value="{{$ano->id}}" @selected($filtros['ano_id'] == $ano->id)>@if(isset($temporadas[$ano->id]) && $temporadas[$ano->id]->des_temporada){{$temporadas[$ano->id]->des_temporada}} ({{$temporadas[$ano->id]->referencia ?: $ano->ano}})@else{{$temporadas[$ano->id]->referencia ?? null ?: $ano->ano}}@endif</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filtroBusca" class="form-label">Piloto ou equipe</label>
                <input type="search" name="busca" id="filtroBusca" class="form-control" value="{{$filtros['busca']}}" placeholder="Ex.: Alonso, Ferrari...">
            </div>
            <div>
                <label for="filtroStatus" class="form-label">Status</label>
                <select name="status" id="filtroStatus" class="form-select">
                    <option value="" @selected($filtros['status'] == '')>Todos</option>
                    <option value="S" @selected($filtros['status'] == 'S')>Ativos</option>
                    <option value="N" @selected($filtros['status'] == 'N')>Inativos</option>
                </select>
            </div>
            <div class="pe-filtros-acoes">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                <a href="{{route('pilotoEquipe.index')}}" class="btn btn-outline-secondary" title="Limpar filtros"><i class="bi bi-x-lg"></i></a>
            </div>
        </div>
    </form>

    @forelse($anosPaginados as $ano)
        @php
            $temporada = $temporadas[$ano->id] ?? null;
            $duplasDoAno = ($pilotoEquipes[$ano->id] ?? collect())
                ->sortBy(fn ($item) => $item->piloto->nomeCompleto())
                ->groupBy('equipe_id')
                ->sortBy(fn ($itens) => $itens->first()->equipe->nome);
        @endphp
        <section class="pe-temporada" data-temporada="{{$ano->id}}">
            <div class="pe-temporada-cabecalho">
                <div>
                    <h5 class="pe-temporada-titulo">
                        @if($temporada && $temporada->des_temporada){{$temporada->des_temporada}} ({{$temporada->referencia ?: $ano->ano}})@else{{$temporada->referencia ?? null ?: $ano->ano}}@endif
                    </h5>
                    <div class="pe-temporada-info">{{$duplasDoAno->count()}} {{$duplasDoAno->count() == 1 ? 'equipe' : 'equipes'}} · {{$duplasDoAno->flatten(1)->count()}} {{$duplasDoAno->flatten(1)->count() == 1 ? 'piloto' : 'pilotos'}}</div>
                </div>
                <label class="pe-check-label">
                    <input type="checkbox" class="pe-check-temporada" aria-label="Selecionar todos os pilotos da temporada {{$ano->ano}}">
                    Selecionar temporada
                </label>
            </div>

            @foreach($duplasDoAno as $equipeId => $duplas)
                @php $equipe = $duplas->first()->equipe; @endphp
                <div class="pe-equipe" data-equipe="{{$equipeId}}">
                    <label class="pe-equipe-nome pe-check-label">
                        <input type="checkbox" class="pe-check-equipe" aria-label="Selecionar a dupla da {{$equipe->nome}}">
                        @if($equipe->imagem)
                            <img src="{{asset('images/'.$equipe->imagem)}}" alt="" class="pe-icone-equipe" style="background-color: {{Equipe::corFundoIcone($equipe->des_cor, $equipe->imagem)}};">
                        @endif
                        <span>{{$equipe->nome}}</span>
                    </label>
                    <ul class="pe-pilotos">
                        @foreach($duplas as $pilotoEquipe)
                            <li class="pe-piloto @if($pilotoEquipe->flg_ativo != 'S') pe-inativo @endif">
                                <input type="checkbox" class="pe-check-piloto" form="formReplicar" name="pilotoEquipe_ids[]" value="{{$pilotoEquipe->id}}"
                                    data-descricao="{{$pilotoEquipe->piloto->nomeCompleto()}} ({{$equipe->nome}} · {{$temporada->referencia ?? null ?: $ano->ano}})"
                                    aria-label="Selecionar {{$pilotoEquipe->piloto->nomeCompleto()}}" style="width: 18px; height: 18px; cursor: pointer;">
                                <img src="{{$pilotoEquipe->piloto->imagem ? asset('images/'.$pilotoEquipe->piloto->imagem) : \App\Support\ImageUrl::PLACEHOLDER}}" alt="" class="pe-foto">
                                <div style="min-width: 0;">
                                    <div class="pe-piloto-nome">
                                        {{$pilotoEquipe->piloto->nomeCompleto()}}
                                        @if($pilotoEquipe->flg_ativo != 'S')<span class="badge bg-secondary ms-1">Inativo</span>@endif
                                        @if($pilotoEquipe->flg_super_corrida == 'S')<span class="badge bg-warning text-dark ms-1">Super corrida</span>@endif
                                    </div>
                                    <div class="pe-piloto-extra">
                                        {{$pilotoEquipe->modelo_carro ?? 'Carro não informado'}}@if(isset($pilotoEquipe->skin)) · Skin: {{$pilotoEquipe->skin->skin}}@endif
                                    </div>
                                </div>
                                <div class="pe-acoes">
                                    <button type="button" class="btn btn-sm btn-outline-success pe-replicar-um" data-toggle="tooltip" title="Replicar só este piloto" value="{{$pilotoEquipe->id}}"><i class="bi bi-files"></i></button>
                                    <a href="{{route('pilotoEquipe.edit', [$pilotoEquipe->id])}}" class="btn btn-sm btn-outline-primary" data-toggle="tooltip" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-danger pe-excluir" data-toggle="tooltip" title="Excluir"
                                        data-url="{{route('pilotoEquipe.delete', [$pilotoEquipe->id])}}"
                                        data-descricao="{{$pilotoEquipe->piloto->nomeCompleto()}} ({{$equipe->nome}} · {{$temporada->referencia ?? null ?: $ano->ano}})"><i class="bi bi-trash"></i></button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </section>
    @empty
        <div class="alert alert-light border text-center py-4">
            Nenhuma dupla encontrada para os filtros escolhidos.
        </div>
    @endforelse

    @if($anosPaginados->total() > 0)
        <div class="pe-paginacao">
            <span class="text-muted small">Temporadas {{$anosPaginados->firstItem()}} a {{$anosPaginados->lastItem()}} de {{$anosPaginados->total()}}</span>
            {{ $anosPaginados->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    @endif

    <a href="{{route('dashboard')}}" class="btn btn-secondary">Voltar</a>
</div>

{{-- Barra de seleção (aparece quando há pilotos marcados) --}}
<div class="pe-barra-selecao" id="barraSelecao" hidden>
    <strong id="textoSelecao">0 pilotos selecionados</strong>
    <button type="button" class="btn btn-sm btn-outline-light" id="botaoLimparSelecao">Limpar</button>
    <button type="button" class="btn btn-sm btn-danger" id="botaoAbrirExcluirSelecionados"><i class="bi bi-trash"></i> Excluir</button>
    <button type="button" class="btn btn-sm btn-success" id="botaoAbrirReplicar"><i class="bi bi-files"></i> Replicar para temporada...</button>
</div>

 <!-- Modal replicar duplas de pilotos -->
 <form id="formReplicar" method="POST" action="{{route('pilotoEquipe.replicarPilotoEquipe')}}">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div class="modal fade" id="modalReplicar" tabindex="-1" role="dialog" aria-labelledby="modalReplicarLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReplicarLabel">Replicar <span id="quantidadeReplicar"></span></h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <ul class="pe-lista-selecionados mb-3" id="listaReplicar"></ul>
                    <label for="selectReplicarPiloto" class="form-label fw-semibold">Temporada de destino</label>
                    <select name="ano_id" id="selectReplicarPiloto" class="form-select" required>
                        @foreach($anos as $ano)
                            <option value="{{$ano->id}}">@if(isset($temporadas[$ano->id]) && $temporadas[$ano->id]->des_temporada){{$temporadas[$ano->id]->des_temporada}} ({{$temporadas[$ano->id]->referencia ?: $ano->ano}})@else{{$temporadas[$ano->id]->referencia ?? null ?: $ano->ano}}@endif</option>
                        @endforeach
                    </select>
                    <div class="form-text">Pilotos que já estão na mesma equipe na temporada de destino são ignorados.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</form>

 <!-- Modal exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1" role="dialog" aria-labelledby="modalExcluirLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExcluirLabel">Confirmar</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Deseja excluir <strong id="descricaoExcluir"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Não</button>
                <a href="#" class="btn btn-danger" id="confirmarExcluir">Sim</a>
            </div>
        </div>
    </div>
</div>

 <!-- Modal exclusão dos selecionados -->
<form id="formExcluirSelecionados" method="POST" action="{{route('pilotoEquipe.deleteSelecionados')}}">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div id="idsExcluirSelecionados"></div>
    <div class="modal fade" id="modalExcluirSelecionados" tabindex="-1" role="dialog" aria-labelledby="modalExcluirSelecionadosLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalExcluirSelecionadosLabel">Excluir <span id="quantidadeExcluir"></span></h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Deseja excluir os pilotos abaixo?</p>
                    <ul class="pe-lista-selecionados mb-3" id="listaExcluir"></ul>
                    <div class="form-text">Pilotos que já têm resultados ou títulos lançados não são excluídos.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Não</button>
                    <button type="submit" class="btn btn-danger">Sim, excluir</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // O Select2 está no jQuery carregado no <head>
    const jqSelect2 = window.jQuery;
    const idiomaSelect2 = { noResults: () => 'Nenhuma temporada encontrada' };

    jqSelect2('#selectReplicarPiloto').select2({
        width: '100%',
        dropdownParent: jqSelect2('#modalReplicar'),
        language: idiomaSelect2
    });

    // O layout aplica o Select2 (tema classic) em todos os selects no document.ready, depois deste script;
    // o setTimeout faz a reaplicação rodar após todos os callbacks do ready, mantendo o mesmo visual do modal
    jqSelect2(function () {
        setTimeout(function () {
            jqSelect2('#filtroAno').select2({ width: '100%', language: idiomaSelect2 });
            jqSelect2('#filtroStatus').select2({ width: '100%', minimumResultsForSearch: Infinity });
        }, 0);
    });

    const checksPilotos = () => Array.from(document.querySelectorAll('.pe-check-piloto'));

    // Atualiza destaque das linhas, checkboxes de equipe/temporada e a barra de seleção
    function atualizaSelecao() {
        checksPilotos().forEach(check => check.closest('.pe-piloto').classList.toggle('pe-selecionado', check.checked));

        document.querySelectorAll('.pe-equipe').forEach(equipe => marcaGrupo(equipe.querySelector('.pe-check-equipe'), equipe));
        document.querySelectorAll('.pe-temporada').forEach(temporada => marcaGrupo(temporada.querySelector('.pe-check-temporada'), temporada));

        const quantidade = checksPilotos().filter(check => check.checked).length;
        document.getElementById('barraSelecao').hidden = quantidade === 0;
        document.getElementById('textoSelecao').innerText = quantidade + (quantidade === 1 ? ' piloto selecionado' : ' pilotos selecionados');
    }

    function marcaGrupo(checkGrupo, grupo) {
        const checks = Array.from(grupo.querySelectorAll('.pe-check-piloto'));
        const marcados = checks.filter(check => check.checked).length;
        checkGrupo.checked = marcados > 0 && marcados === checks.length;
        checkGrupo.indeterminate = marcados > 0 && marcados < checks.length;
    }

    document.addEventListener('change', function (e) {
        const alvo = e.target;
        if (alvo.classList.contains('pe-check-equipe')) {
            alvo.closest('.pe-equipe').querySelectorAll('.pe-check-piloto').forEach(check => check.checked = alvo.checked);
        } else if (alvo.classList.contains('pe-check-temporada')) {
            alvo.closest('.pe-temporada').querySelectorAll('.pe-check-piloto').forEach(check => check.checked = alvo.checked);
        } else if (!alvo.classList.contains('pe-check-piloto')) {
            return;
        }
        atualizaSelecao();
    });

    document.getElementById('botaoLimparSelecao').addEventListener('click', function () {
        checksPilotos().forEach(check => check.checked = false);
        atualizaSelecao();
    });

    function abreModalReplicar() {
        const selecionados = checksPilotos().filter(check => check.checked);
        const lista = document.getElementById('listaReplicar');
        lista.innerHTML = '';
        selecionados.forEach(check => {
            const item = document.createElement('li');
            item.textContent = check.dataset.descricao;
            lista.appendChild(item);
        });
        document.getElementById('quantidadeReplicar').innerText = selecionados.length + (selecionados.length === 1 ? ' piloto' : ' pilotos');
        window.jQuery('#modalReplicar').modal('show');
    }

    document.getElementById('botaoAbrirReplicar').addEventListener('click', abreModalReplicar);

    // Os checkboxes pertencem ao form de replicar; para excluir, copia os ids marcados para o form de exclusão
    document.getElementById('botaoAbrirExcluirSelecionados').addEventListener('click', function () {
        const selecionados = checksPilotos().filter(check => check.checked);
        const lista = document.getElementById('listaExcluir');
        const ids = document.getElementById('idsExcluirSelecionados');
        lista.innerHTML = '';
        ids.innerHTML = '';
        selecionados.forEach(check => {
            const item = document.createElement('li');
            item.textContent = check.dataset.descricao;
            lista.appendChild(item);

            const campo = document.createElement('input');
            campo.type = 'hidden';
            campo.name = 'pilotoEquipe_ids[]';
            campo.value = check.value;
            ids.appendChild(campo);
        });
        document.getElementById('quantidadeExcluir').innerText = selecionados.length + (selecionados.length === 1 ? ' piloto' : ' pilotos');
        window.jQuery('#modalExcluirSelecionados').modal('show');
    });

    document.addEventListener('click', function (e) {
        const replicarUm = e.target.closest('.pe-replicar-um');
        if (replicarUm) {
            checksPilotos().forEach(check => check.checked = check.value === replicarUm.value);
            atualizaSelecao();
            abreModalReplicar();
            return;
        }

        const excluir = e.target.closest('.pe-excluir');
        if (excluir) {
            document.getElementById('descricaoExcluir').innerText = excluir.dataset.descricao;
            document.getElementById('confirmarExcluir').href = excluir.dataset.url;
            window.jQuery('#modalExcluir').modal('show');
        }
    });

    // Volta para a seleção ao reabrir a página pelo histórico do navegador
    window.addEventListener('pageshow', atualizaSelecao);
</script>

@endsection
