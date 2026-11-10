@php
    use App\Models\Site\Temporada;
    use App\Models\Site\Equipe;
@endphp

@extends('layouts.main')

@section('section')

{{-- MELHORIA 20261110: CSS dos ícones/modal ANTES da tabela (no fim da página os ícones apareciam como botão padrão até o CSS carregar) --}}
<style>
    /* descrição numa linha só, alinhada à esquerda, com os ícones logo depois */
    #tabelaTemporadas td.celula-descricao { text-align: left; white-space: nowrap; }
    .descricao-temporada { display: inline-flex; align-items: center; gap: 8px; }

    /* ícones ao lado da descrição: mesma família visual (letra dentro do círculo) */
    .icones-temporada { display: inline-flex; align-items: center; gap: 4px; }
    .icone-temporada {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        padding: 0;
        /* fonte do sistema: o app.css aplica Poppins em "*" e a troca da fonte web fazia o ícone "pular" ao carregar */
        font-family: Arial, Helvetica, sans-serif;
        border: 1.5px solid currentColor;
        border-radius: 50%;
        background: transparent;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
        cursor: pointer;
        transition: background-color 0.15s, color 0.15s, border-color 0.15s;
    }
    .icone-info { color: #0d6efd; font-family: Georgia, 'Times New Roman', serif; font-style: italic; }
    .icone-sprint { color: #d39e00; font-style: italic; }
    .icone-info:hover, .icone-info:focus-visible { background-color: #0d6efd; border-color: #0d6efd; color: #fff; outline: none; }
    .icone-sprint:hover, .icone-sprint:focus-visible { background-color: #ffc107; border-color: #ffc107; color: #15151e; outline: none; }
    /* MELHORIA 20261110 (playoff) */
    .icone-playoff { color: #00a896; }
    .icone-playoff:hover, .icone-playoff:focus-visible { background-color: #00d2be; border-color: #00d2be; color: #06332f; outline: none; }

    .sprint-modal-fundo {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background-color: rgba(10, 10, 15, 0.55);
        backdrop-filter: blur(2px);
    }
    .sprint-modal-fundo.aberto { display: flex; }
    .sprint-modal {
        --modal-acento: #ffc107;
        width: 100%;
        max-width: 360px;
        background-color: #15151e;
        color: #fff;
        border-radius: 12px;
        border-top: 4px solid var(--modal-acento);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
        padding: 18px 20px 16px;
        animation: sprintModalEntra 0.16s ease-out;
    }
    .sprint-modal.tipo-observacoes { --modal-acento: #3d8bfd; }
    .sprint-modal.tipo-playoff { --modal-acento: #00d2be; } /* MELHORIA 20261110 (playoff) */
    .po-modal-fase { padding: 10px 0 4px; border-top: 1px solid #2b2b35; font-size: 0.78rem; color: #8a8a94; }
    .po-modal-fase b { color: #fff; }
    .po-modal-confrontos { list-style: none; margin: 6px 0 0; padding: 0; }
    .po-modal-confrontos li { display: grid; grid-template-columns: 1fr auto 1fr; gap: 8px; align-items: center; padding: 5px 0; font-size: 0.85rem; border-top: 1px solid #1f1f28; }
    .po-modal-confrontos li span:first-child { text-align: right; }
    .po-modal-confrontos .placar { font-weight: 800; color: var(--modal-acento); min-width: 54px; text-align: center; }
    .po-modal-confrontos .venceu { color: var(--modal-acento); font-weight: 700; }
    @keyframes sprintModalEntra { from { opacity: 0; transform: translateY(8px) scale(0.98); } to { opacity: 1; transform: none; } }
    .sprint-modal-topo { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
    .sprint-modal-titulo { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 1.05rem; font-weight: 800; font-style: italic; text-transform: uppercase; letter-spacing: 0.5px; }
    .sprint-modal-titulo .marca {
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px; border-radius: 50%; font-size: 11px; font-weight: 800;
        background-color: var(--modal-acento); color: #15151e; text-transform: none; font-family: Arial, Helvetica, sans-serif;
    }
    .sprint-modal.tipo-observacoes .sprint-modal-titulo .marca { color: #fff; font-family: Georgia, 'Times New Roman', serif; }
    .sprint-modal-sub { display: block; margin-top: 2px; font-size: 0.78rem; color: #8a8a94; }
    .sprint-modal-fechar { border: none; background: none; color: #8a8a94; font-size: 1.3rem; line-height: 1; padding: 0 2px; cursor: pointer; }
    .sprint-modal-fechar:hover { color: #fff; }
    .sprint-modal-fechar:focus { outline: none; }
    .sprint-modal-fechar:focus-visible { outline: 1px solid #4a4a55; border-radius: 4px; }
    .sprint-modal-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-top: 1px solid #2b2b35; }
    .sprint-modal-item img, .sprint-modal-item .sem-logo {
        width: 38px; height: 38px; flex-shrink: 0; padding: 5px; border-radius: 50%;
        object-fit: contain; background-color: #2b2b35;
    }
    .sprint-modal-item .rotulo { display: block; font-size: 0.68rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--modal-acento); }
    .sprint-modal-item .nome { display: block; font-weight: 700; font-size: 1rem; }
    .sprint-modal-item .detalhe { display: block; font-size: 0.78rem; color: #8a8a94; }
    .sprint-modal-vazio { padding: 12px 0 4px; border-top: 1px solid #2b2b35; color: #8a8a94; font-size: 0.9rem; }
    .sprint-modal-texto { padding-top: 12px; border-top: 1px solid #2b2b35; font-size: 0.92rem; line-height: 1.5; color: #e4e4ea; white-space: pre-line; overflow-wrap: anywhere; max-height: 50vh; overflow-y: auto; }
    .sprint-modal-link { display: inline-block; margin-top: 10px; font-size: 0.82rem; font-weight: 700; color: var(--modal-acento); text-decoration: none; }
    .sprint-modal-link:hover { color: #fff; }
    .sprint-modal-link[hidden] { display: none; }
</style>

    @if (session('error'))
        <div class="alert alert-danger text-center">
            {{ session('error') }}
        </div>
    @endif

  <div class="mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Temporadas</li>
        </ol>
    </nav>

     <div class="card mt-3 mb-3 p-3">
        <div class="card-body">
        <label for="">Pesquisar</label>
        <input type="text" id="caixaBusca">
        </div>
    </div>

        @if(count($temporadas) > 0)
        <div class="table-responsive">
        <table class="custom-responsive-table align-middle" id="tabelaTemporadas">
            <thead>
                <tr>
                    <th>#</th>
                    <th onclick="sortTable(1, 'tabelaTemporadas')" style="cursor: pointer; text-align:center;">Ano</th>
                    <th style="text-align:left;">Descrição</th>
                    <th onclick="sortTable(3, 'tabelaTemporadas')" style="cursor: pointer; text-align:center;">Referência</th>
                    <th style="width: 20%; text-align:left;">Camp. Piloto</th>
                    <th style="width: 15%; text-align:left;">Camp. Construtores</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($temporadas as $key => $temporada)
                    <tr>
                        <td data-label="#">{{$key+1}}</td>
                        <td data-label="Ano">{{$temporada->ano->ano}}</td>
                        <td data-label="Temporada" class="celula-descricao">
                            {{-- MELHORIA 20261110: descrição + ícones numa linha só (um bloco, para o layout mobile não espalhar) --}}
                            <span class="descricao-temporada">
                            <span>{{$temporada->des_temporada}}</span>
                            {{-- MELHORIA 20261110: ícones da temporada, cada um abre um modal só com o que interessa
                                 (antes: tooltip no hover do "i", com a tag <i> sem fechar) --}}
                            @if(isset($temporada->observacoes) || isset($sprintsInfo[$temporada->id]) || isset($playoffInfo[$temporada->id]))
                                <span class="icones-temporada">
                                    @isset($temporada->observacoes)
                                        <button type="button" class="icone-temporada icone-info" data-modal="observacoes"
                                                data-temporada="{{ $temporada->id }}" data-observacoes="{{ $temporada->observacoes }}"
                                                aria-label="Observações da temporada">i</button>
                                    @endisset
                                    @isset($sprintsInfo[$temporada->id])
                                        <button type="button" class="icone-temporada icone-sprint" data-modal="sprint"
                                                data-temporada="{{ $temporada->id }}"
                                                aria-label="{{ $sprintsInfo[$temporada->id]['finalizada'] ? 'Campeões das sprints' : 'Líderes das sprints' }}">S</button>
                                    @endisset
                                    {{-- MELHORIA 20261110 (playoff): temporada teve playoff --}}
                                    @isset($playoffInfo[$temporada->id])
                                        <button type="button" class="icone-temporada icone-playoff" data-modal="playoff"
                                                data-temporada="{{ $temporada->id }}"
                                                aria-label="Playoff da temporada">P</button>
                                    @endisset
                                </span>
                            @endif
                            </span>
                        </td>
                        <td data-label="Referência">
                            {{$temporada->referencia ?? '-'}}
                        </td>
                        <td data-label="Camp. Piloto" style="text-align:left;">
                            @if(isset($temporada->titulo))
                                @php $equipePiloto = $temporada->titulo->pilotoEquipe->equipe; @endphp
                                <span>
                                    @if($equipePiloto->imagem)
                                        <img src="{{asset('images/'.$equipePiloto->imagem)}}" alt="" style="width: 26px; height:26px; padding: 3px; border-radius: 50%; object-fit: contain; vertical-align: middle; box-shadow: 0 0 0 1px rgba(0,0,0,.15); background-color: {{Equipe::corFundoIcone($equipePiloto->des_cor, $equipePiloto->imagem)}};">
                                    @endif
                                    {{$temporada->titulo->pilotoEquipe->piloto->nomeCompleto()}}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td data-label="Camp. Construtores" style="text-align:left;">
                            @if(isset($temporada->titulo))
                                @php $equipeCampea = $temporada->titulo->equipe; @endphp
                                <span>
                                    @if($equipeCampea->imagem)
                                        <img src="{{asset('images/'.$equipeCampea->imagem)}}" alt="" style="width: 26px; height:26px; padding: 3px; border-radius: 50%; object-fit: contain; vertical-align: middle; box-shadow: 0 0 0 1px rgba(0,0,0,.15); background-color: {{Equipe::corFundoIcone($equipeCampea->des_cor, $equipeCampea->imagem)}};">
                                    @endif
                                    {{$equipeCampea->nome}}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td data-label="Status">@if($temporada->flg_finalizada == 'S')<i class="bi bi-check-square-fill"></i>@else Em Andamento @endif</td>
                        <td data-label="Ações" class="text-nowrap">
                            <div class="d-inline-flex gap-1">
                                <a class="btn btn-sm btn-outline-warning" data-toggle="tooltip" data-placement="top" title="Classificação" href="{{route('temporadas.classificacao', [$temporada->id])}}"><i class="bi bi-trophy"></i></a>
                                <a class="btn btn-sm btn-outline-info" data-toggle="tooltip" data-placement="top" title="Resultados" href="{{route('temporadas.resultados', [$temporada->id])}}"><i class="bi bi-list-ol"></i></a>
                                {{-- MELHORIA 20261110 (resumo): dados, estatísticas, curiosidades e manchetes da temporada --}}
                                <a class="btn btn-sm btn-outline-dark" data-toggle="tooltip" data-placement="top" title="Resumo e estatísticas da temporada" href="{{route('temporadas.resumo', [$temporada->id])}}"><i class="bi bi-newspaper"></i></a>
                                {{-- MELHORIA 20261110: link "Resultados (pontuação)" removido; chegada/largada agora é alternada dentro da tela de resultados --}}
                                <a class="btn btn-sm" style="--bs-btn-color: #fd7e14; --bs-btn-border-color: #fd7e14; --bs-btn-hover-color: #fff; --bs-btn-hover-bg: #fd7e14; --bs-btn-hover-border-color: #fd7e14; --bs-btn-active-color: #fff; --bs-btn-active-bg: #fd7e14; --bs-btn-active-border-color: #fd7e14;" data-toggle="tooltip" data-placement="top" title="Visualizar Corridas" href="{{route('corridas.index', [$temporada->id])}}"><i class="bi bi-eye"></i></a>
                                <a class="btn btn-sm btn-outline-success" data-toggle="tooltip" data-placement="top" title="Adicionar Corridas" href="{{route('corridas.adicionar', [$temporada->id])}}"><i class="bi bi-plus-lg"></i></a>
                                <a class="btn btn-sm btn-outline-primary" data-toggle="tooltip" data-placement="top" title="Editar Temporada" href="{{route('temporadas.edit', [$temporada->id])}}"><i class="bi bi-pencil"></i></a>
                                <a class="btn btn-sm btn-outline-danger" data-toggle="tooltip" data-placement="top" title="Deletar" href="{{route('temporadas.delete', [$temporada->id])}}"><i class="bi bi-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
            <p>Nenhuma temporada cadastrada</p>
        @endif
    </div>
    <a href="{{route('temporadas.create')}}" class="btn btn-dark">Adicionar temporada</a>
    <a href="{{route('dashboard')}}" class="btn btn-danger ml-3">Voltar</a>
  </div>

<script src="{{asset('js/app.js')}}"></script>
<script src="{{asset('js/temporadas/index.js')}}"></script>

{{-- MELHORIA 20261110: modal leve compartilhado pelos ícones da temporada (observações e sprints).
     Sem JS do Bootstrap (layout mistura 4.1 e 5.2). --}}

<div class="sprint-modal-fundo" id="sprintModalFundo" aria-hidden="true">
    <div class="sprint-modal" id="sprintModalCaixa" role="dialog" aria-modal="true" aria-labelledby="sprintModalTitulo">
        <div class="sprint-modal-topo">
            <div>
                <h2 class="sprint-modal-titulo" id="sprintModalTitulo"></h2>
                <span class="sprint-modal-sub" id="sprintModalSub"></span>
            </div>
            <button type="button" class="sprint-modal-fechar" id="sprintModalFechar" aria-label="Fechar">&times;</button>
        </div>
        <div id="sprintModalCorpo"></div>
        <a class="sprint-modal-link" id="sprintModalLink" href="#" hidden><span id="sprintModalLinkTexto">Ver classificação das sprints</span> <i class="bi bi-arrow-right"></i></a>
    </div>
</div>

@php
    // nomes das temporadas para o subtítulo do modal de observações
    $nomesTemporadas = $temporadas->mapWithKeys(fn ($t) => [$t->id => $t->des_temporada . ($t->referencia ? ' · ' . $t->referencia : '')]);
@endphp
<script>
(function () {
    const dados = @json($sprintsInfo ?? []);
    const dadosPlayoff = @json($playoffInfo ?? []); // MELHORIA 20261110 (playoff)
    const nomes = @json($nomesTemporadas);
    const fundo = document.getElementById('sprintModalFundo');
    if (!fundo) return;
    const caixa = document.getElementById('sprintModalCaixa');
    const link = document.getElementById('sprintModalLink');
    const esc = t => String(t ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const pts = v => (v === undefined || v === null) ? '' : String(v).replace('.', ',') + ' pts';
    let origem = null;

    function item(rotulo, x, detalhe) {
        const logo = x.img
            ? '<img src="' + esc(x.img) + '" alt="" style="background-color:' + esc(x.fundo || '#2b2b35') + '">'
            : '<span class="sem-logo"></span>';
        return '<div class="sprint-modal-item">' + logo + '<div>' +
            '<span class="rotulo">' + esc(rotulo) + '</span>' +
            '<span class="nome">' + esc(x.nome) + '</span>' +
            (detalhe ? '<span class="detalhe">' + esc(detalhe) + '</span>' : '') +
            '</div></div>';
    }

    function mostrar(tipo, titulo, marca, sub, corpo, href, textoLink) {
        caixa.classList.toggle('tipo-observacoes', tipo === 'observacoes');
        caixa.classList.toggle('tipo-playoff', tipo === 'playoff');
        document.getElementById('sprintModalLinkTexto').textContent = textoLink || 'Ver classificação das sprints';
        document.getElementById('sprintModalTitulo').innerHTML = '<span class="marca">' + marca + '</span>' + esc(titulo);
        document.getElementById('sprintModalSub').textContent = sub;
        document.getElementById('sprintModalCorpo').innerHTML = corpo;
        link.hidden = !href;
        if (href) link.href = href;
        fundo.classList.add('aberto');
        fundo.setAttribute('aria-hidden', 'false');
        document.getElementById('sprintModalFechar').focus();
    }

    function abrirSprint(id) {
        const d = dados[id];
        if (!d) return;
        const prefixo = d.finalizada ? 'Campeão' : 'Líder';
        let corpo = '';
        if (d.piloto) corpo += item(prefixo + ' · Pilotos', d.piloto, [d.piloto.equipe, pts(d.piloto.pontos)].filter(Boolean).join(' · '));
        if (d.equipe) corpo += item((d.finalizada ? 'Campeã' : 'Líder') + ' · Construtores', d.equipe, pts(d.equipe.pontos));
        if (!corpo) corpo = '<div class="sprint-modal-vazio">' + (d.finalizada ? 'Título das sprints ainda não calculado — salve a temporada para recalcular.' : 'Sem resultados de sprint ainda.') + '</div>';
        mostrar('sprint', 'Sprints', 'S',
            d.temporada + ' · ' + d.qtd + (d.qtd === 1 ? ' sprint' : ' sprints') + (d.finalizada ? '' : ' · em andamento'),
            corpo, d.link);
    }

    // MELHORIA 20261110 (playoff): campeão/vice ou a fase atual com os confrontos em andamento
    function abrirPlayoff(id) {
        const d = dadosPlayoff[id];
        if (!d) return;
        const placar = v => (v === null || v === undefined) ? '-' : String(v).replace('.', ',');
        let corpo = '';
        if (d.campeao) corpo += item('Campeão do playoff', d.campeao, d.campeao.equipe);
        if (d.vice) corpo += item('Vice', d.vice, d.vice.equipe);
        if (!d.campeao) {
            corpo += '<div class="po-modal-fase">Fase atual: <b>' + esc(d.fase_atual) + '</b>';
            if (d.confrontos.length) {
                corpo += '<ul class="po-modal-confrontos">' + d.confrontos.map(c =>
                    '<li><span class="' + (c.vencedor === c.a ? 'venceu' : '') + '">' + esc(c.a) + '</span>' +
                    '<span class="placar">' + placar(c.pa) + ' x ' + placar(c.pb) + '</span>' +
                    '<span class="' + (c.vencedor === c.b ? 'venceu' : '') + '">' + esc(c.b) + '</span></li>').join('') + '</ul>';
            }
            corpo += '</div>';
        }
        mostrar('playoff', 'Playoff', 'P',
            d.temporada + ' · ' + d.formato + (d.finalizada ? '' : ' · em andamento'),
            corpo, d.link, 'Ver chaveamento');
    }

    function abrirObservacoes(id, texto) {
        mostrar('observacoes', 'Observações', 'i', nomes[id] || '', '<div class="sprint-modal-texto">' + esc(texto) + '</div>', null);
    }

    function fechar() {
        fundo.classList.remove('aberto');
        fundo.setAttribute('aria-hidden', 'true');
        if (origem) origem.focus();
    }

    document.addEventListener('click', e => {
        const icone = e.target.closest('.icone-temporada[data-modal]');
        if (icone) {
            e.preventDefault();
            origem = icone;
            if (icone.dataset.modal === 'sprint') abrirSprint(icone.dataset.temporada);
            else if (icone.dataset.modal === 'playoff') abrirPlayoff(icone.dataset.temporada);
            else abrirObservacoes(icone.dataset.temporada, icone.dataset.observacoes);
            return;
        }
        if (e.target === fundo || e.target.closest('#sprintModalFechar')) fechar();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && fundo.classList.contains('aberto')) fechar(); });
})();
</script>
@endsection
