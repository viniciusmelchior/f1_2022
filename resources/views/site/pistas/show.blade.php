{{-- MELHORIA 20261117: resultados na pista remodelados no visual dos resumos (dados em App\Services\PistaHistoricoService).
     Versão anterior: show.blade.php_20261117_remodelagem --}}
@extends('layouts.main')

@section('section')
@php
    $p = $dados['pista'];
    // sem corridas do usuário na pista: mantém o comportamento anterior (nome "-" e lista vazia)
    $nomePista = $dados['vazia'] ? '-' : $p['nome'];
    // mais recentes primeiro (como a tela antiga), sprint e corrida juntas, marcadas por tipo
    $lista = collect(array_merge($dados['corridas'], $dados['sprints']))
        ->sortBy([['temporada_id', 'desc'], ['ordem', 'asc'], ['sprint', 'desc']])->values();
    $referencias = $lista->pluck('referencia')->filter()->unique()->sort(SORT_NATURAL)->values();
    $cel = function ($x) {
        if (!$x) return '<span class="res-muted">-</span>';
        return '<span class="res-piloto-cel"><img class="res-logo mini" src="' . e($x['eq']['img']) . '" alt="" title="' . e($x['eq']['nome']) . '" style="background-color:' . e($x['eq']['fundo']) . '">'
            . '<a href="' . e($x['p']['url']) . '">' . e($x['p']['nome']) . '</a></span>';
    };
@endphp

@include('site.partials.resumo-estilos')
<style>
    .res-hero-sub { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px; color: #c3c2b7; font-size: .9rem; margin-top: 4px; }
    .res-hero-sub .res-bandeira { width: 28px; height: 19px; }
    .res-pill.info { background: rgba(255,255,255,.12); color: #fff; }
    .rp-filtros { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; padding: 14px 16px; margin-bottom: 14px; }
    .rp-filtro { display: flex; flex-direction: column; gap: 4px; }
    .rp-filtro label { font-size: .68rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--ink-3); margin: 0; }
    .rp-filtro input, .rp-filtro select { height: 36px; border: 1px solid #dcdcdc; border-radius: 8px; padding: 0 10px; font-size: .9rem; background: #fff; }
    .rp-filtro input { width: 240px; max-width: 100%; }
    .rp-filtro input:focus, .rp-filtro select:focus { outline: none; border-color: var(--f1-red); box-shadow: 0 0 0 3px rgba(225,6,0,.12); }
    .rp-tipos { display: inline-flex; border: 1px solid #dcdcdc; border-radius: 8px; overflow: hidden; height: 36px; }
    .rp-tipos button { border: 0; background: #fff; padding: 0 14px; font-size: .82rem; font-weight: 700; color: var(--ink-2); cursor: pointer; }
    .rp-tipos button + button { border-left: 1px solid #dcdcdc; }
    .rp-tipos button.ativo { background: var(--f1-darker); color: #fff; }
    .rp-contador { margin-left: auto; font-size: .82rem; color: var(--ink-2); font-weight: 700; }
    .res-tabela th.ordenavel.asc::after { content: ' ▲'; opacity: .9; }
    .res-tabela th.ordenavel.desc::after { content: ' ▼'; opacity: .9; }
    .res-tabela tr.rp-sprint td { background: #fffbeb; font-style: italic; }
    .res-tabela tr.rp-sprint td:first-child { box-shadow: inset 4px 0 0 #ffc107; }
    .res-tabela tr.sem-resultado td { color: var(--ink-3); }
    .rp-ref { display: inline-block; min-width: 46px; padding: 1px 8px; border-radius: 6px; font-weight: 800; background: #eef0f3; font-style: normal; }
    .rp-etapa-sprint { display: inline-block; font-size: .66rem; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; padding: 1px 6px; border-radius: 4px; background: #ffc107; color: #15151e; font-style: normal; }
    .rp-vazio td { text-align: center !important; color: var(--ink-3); padding: 18px !important; }
    @media (max-width: 700px) { .rp-filtro, .rp-filtro input, .rp-filtro select { width: 100%; } .rp-contador { margin-left: 0; } }
</style>

<div class="res-page">
    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('pistas.index')}}">Pistas</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $nomePista }}</li>
        </ol>
    </nav>

    <header class="res-hero">
        <div>
            <small class="res-kicker">Resultados na pista</small>
            <h1>{{ $nomePista }}</h1>
            @unless($dados['vazia'])
                <div class="res-hero-sub">
                    @if($p['bandeira'])<img class="res-bandeira" src="{{ $p['bandeira'] }}" alt="">@endif
                    <span>{{ $p['pais'] ?? '-' }}@if($p['continente']) · {{ $p['continente'] }}@endif</span>
                    <span class="res-pill info">{{ count($dados['corridas']) }} {{ count($dados['corridas']) == 1 ? 'corrida' : 'corridas' }}</span>
                    @if($dados['sprints'])<span class="res-pill info">{{ count($dados['sprints']) }} {{ count($dados['sprints']) == 1 ? 'sprint' : 'sprints' }}</span>@endif
                </div>
            @endunless
        </div>
        <div class="res-hero-acoes">
            <div class="res-links">
                <a href="{{ route('pistas.historico', [$id]) }}"><i class="bi bi-newspaper"></i> Histórico e estatísticas</a>
                <a href="{{ route('pistas.index') }}"><i class="bi bi-arrow-left"></i> Pistas</a>
            </div>
        </div>
    </header>

    @if($dados['vazia'])
        <div class="res-card res-vazio" style="margin-top: 20px;">Você ainda não tem corridas nesta pista.</div>
    @else
        <div class="res-kpis">
            @foreach($dados['kpis'] as $k)
                <div class="res-kpi"><span><i class="bi {{ $k['icone'] }}"></i> {{ $k['rotulo'] }}</span><strong>{{ $k['valor'] }}</strong><small>{{ $k['detalhe'] }}</small></div>
            @endforeach
        </div>

        <div class="res-card rp-filtros">
            <div class="rp-filtro">
                <label>Tipo</label>
                <div class="rp-tipos" role="group" aria-label="Tipo de corrida">
                    <button type="button" class="ativo" data-tipo="">Todas</button>
                    <button type="button" data-tipo="corrida">Corridas</button>
                    @if($dados['sprints'])<button type="button" data-tipo="sprint">Sprints</button>@endif
                </div>
            </div>
            <div class="rp-filtro">
                {{-- id na lista de exceções do select2 global (layouts/main) --}}
                <label for="filtroRefHistorico">Ano de referência</label>
                <select id="filtroRefHistorico">
                    <option value="">Todos</option>
                    @foreach($referencias as $ref)
                        <option value="{{ $ref }}">{{ $ref }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rp-filtro">
                <label for="caixaBusca">Pesquisar</label>
                <input type="text" id="caixaBusca" placeholder="Piloto, evento, temporada..." autocomplete="off">
            </div>
            <span class="rp-contador" id="contadorCorridas"></span>
        </div>

        <div class="res-card">
            <div class="res-tabela-wrap">
                <table class="res-tabela" id="tabelaResultadosPista">
                    <thead><tr>
                        <th>#</th>
                        <th class="esq ordenavel" data-tipo="num">Temporada</th>
                        <th class="ordenavel" data-tipo="num">Referência</th>
                        <th class="ordenavel" data-tipo="num">Etapa</th>
                        <th class="esq ordenavel" data-tipo="texto">Evento</th>
                        <th class="ordenavel" data-tipo="num" title="Dificuldade da IA">IA</th>
                        <th class="esq ordenavel" data-tipo="texto">Pole Position</th>
                        <th class="esq ordenavel" data-tipo="texto">Primeiro</th>
                        <th class="esq ordenavel" data-tipo="texto">Segundo</th>
                        <th class="esq ordenavel" data-tipo="texto">Terceiro</th>
                        <th>Ações</th>
                    </tr></thead>
                    <tbody>
                        @foreach($lista as $i => $c)
                            <tr data-ref="{{ $c['referencia'] }}" data-tipo="{{ $c['sprint'] ? 'sprint' : 'corrida' }}"
                                class="{{ $c['sprint'] ? 'rp-sprint' : '' }} {{ $c['tem_resultado'] ? '' : 'sem-resultado' }}">
                                <td>{{ $i + 1 }}</td>
                                <td class="esq" data-valor="{{ $c['temporada_id'] * 1000 + $c['ordem'] }}">{{ $c['temporada'] }}</td>
                                <td data-valor="{{ $c['referencia'] ?? 0 }}">@if($c['referencia'])<span class="rp-ref">{{ $c['referencia'] }}</span>@else<span class="res-muted">-</span>@endif</td>
                                <td data-valor="{{ $c['ordem'] }}">@if($c['sprint'])<span class="rp-etapa-sprint">Sprint</span>@else{{ $c['ordem'] }}ª @endif</td>
                                <td class="esq">
                                    {{ $c['evento'] ?? '-' }}
                                    @if($c['clima_icone'])<i class="{{ $c['clima_icone'] }}" title="{{ $c['clima'] }}"></i>@endif
                                    @if($c['safety_car'] > 0)<i class="bi bi-car-front-fill" title="Safety car"></i>@endif
                                </td>
                                <td data-valor="{{ $c['ia'] ?? 0 }}">{{ $c['ia'] ?? '-' }}</td>
                                <td class="esq" data-valor="{{ $c['pole']['p']['nome'] ?? '' }}">{!! $cel($c['pole']) !!}</td>
                                <td class="esq forte" data-valor="{{ $c['p1']['p']['nome'] ?? '' }}">{!! $cel($c['p1']) !!}</td>
                                <td class="esq" data-valor="{{ $c['p2']['p']['nome'] ?? '' }}">{!! $cel($c['p2']) !!}</td>
                                <td class="esq" data-valor="{{ $c['p3']['p']['nome'] ?? '' }}">{!! $cel($c['p3']) !!}</td>
                                <td class="text-nowrap">
                                    <div class="d-inline-flex gap-1">
                                        <a class="btn btn-sm btn-outline-secondary" data-toggle="tooltip" data-placement="top" title="Visualizar resultados" href="{{ route('resultados.show', [$c['id']]) }}"><i class="bi bi-eye"></i></a>
                                        <a class="btn btn-sm btn-outline-dark" data-toggle="tooltip" data-placement="top" title="Resumo e estatísticas da corrida" href="{{ route('corridas.resumo', [$c['id']]) }}"><i class="bi bi-newspaper"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="rp-vazio" data-sem-linhas hidden><td colspan="11">Nenhuma corrida com esses filtros.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div style="margin-top: 20px; display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('pistas.historico', [$id]) }}" class="btn btn-dark"><i class="bi bi-newspaper"></i> Histórico e estatísticas</a>
        <a href="{{ route('pistas.index') }}" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<script>
(function () {
    const tabela = document.getElementById('tabelaResultadosPista');
    if (!tabela) return;
    const tbody = tabela.tBodies[0];
    const vazio = tbody.querySelector('[data-sem-linhas]');
    const linhas = Array.from(tbody.rows).filter(tr => tr !== vazio);
    const selRef = document.getElementById('filtroRefHistorico');
    const busca = document.getElementById('caixaBusca');
    const contador = document.getElementById('contadorCorridas');
    const norm = t => (t || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toUpperCase();
    let tipo = '';

    function filtrar() {
        const ref = selRef.value;
        const termo = norm(busca.value.trim());
        let visiveis = 0;
        linhas.forEach(tr => {
            const ok = (!tipo || tr.dataset.tipo === tipo) && (!ref || tr.dataset.ref === ref) && (!termo || norm(tr.textContent).includes(termo));
            tr.style.display = ok ? '' : 'none';
            if (ok) visiveis++;
        });
        vazio.hidden = visiveis > 0;
        contador.textContent = visiveis + ' de ' + linhas.length;
    }

    document.querySelectorAll('.rp-tipos button').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('.rp-tipos button').forEach(o => o.classList.toggle('ativo', o === b));
        tipo = b.dataset.tipo;
        filtrar();
    }));
    selRef.addEventListener('change', filtrar);
    busca.addEventListener('input', filtrar);

    tabela.querySelectorAll('th.ordenavel').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.cellIndex;
            const num = th.dataset.tipo === 'num';
            const asc = !th.classList.contains('asc');
            tabela.querySelectorAll('th.ordenavel').forEach(o => o.classList.remove('asc', 'desc'));
            th.classList.add(asc ? 'asc' : 'desc');
            const valor = tr => {
                const td = tr.cells[col];
                const v = td.dataset.valor !== undefined ? td.dataset.valor : td.textContent.trim();
                return num ? (parseFloat(v) || 0) : norm(v === '-' ? '' : v);
            };
            linhas.sort((a, b) => {
                const va = valor(a), vb = valor(b);
                const c = num ? va - vb : va.localeCompare(vb, 'pt-BR');
                return asc ? c : -c;
            });
            linhas.forEach((tr, i) => { tr.cells[0].textContent = i + 1; tbody.insertBefore(tr, vazio); });
        });
    });

    filtrar();
})();
</script>
@endsection
