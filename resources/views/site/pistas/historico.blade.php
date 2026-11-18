{{-- MELHORIA 20261117: histórico e estatísticas da pista. Dados em App\Services\PistaHistoricoService --}}
@extends('layouts.main')

@section('section')
@php
    $p = $dados['pista'];
    $refs = $dados['referencias'];
    // célula de piloto: logo da equipe + nome (mesmo padrão das grades de corridas/temporadas)
    $cel = function ($x) {
        if (!$x) return '<span class="res-muted">-</span>';
        return '<span class="res-piloto-cel"><img class="res-logo mini" src="' . e($x['eq']['img']) . '" alt="" title="' . e($x['eq']['nome']) . '" style="background-color:' . e($x['eq']['fundo']) . '">'
            . '<a href="' . e($x['p']['url']) . '">' . e($x['p']['nome']) . '</a></span>';
    };
    $num = fn ($v) => $v === null ? '-' : str_replace('.', ',', (string) $v);
@endphp

@include('site.partials.resumo-estilos')
<style>
    .res-hero-sub { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px; color: #c3c2b7; font-size: .9rem; margin-top: 4px; }
    .res-hero-sub .res-bandeira { width: 28px; height: 19px; }
    .res-pill.info { background: rgba(255,255,255,.12); color: #fff; }
    .res-pill.inativa { background: #6c757d; color: #fff; }
    .res-card h3.res-card-titulo { font-size: .9rem; font-weight: 800; text-transform: uppercase; margin: 0 0 10px; display: flex; align-items: center; gap: 8px; }
    .res-card h3.res-card-titulo i { color: var(--f1-red); }
    .res-card h3.res-card-titulo small { margin-left: auto; font-weight: 700; color: var(--ink-3); text-transform: none; }

    /* consulta de ano de referência */
    .his-consulta { display: flex; flex-wrap: wrap; gap: 12px 16px; align-items: stretch; }
    .his-campo { display: flex; flex-direction: column; justify-content: center; gap: 4px; flex: 0 0 auto; }
    .his-consulta label { display: block; font-size: .68rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--ink-3); margin: 0; line-height: 1.2; }
    .his-consulta select { display: block; width: 160px; height: 38px; margin: 0; border: 1px solid #dcdcdc; border-radius: 8px; padding: 0 10px; font-size: .95rem; font-weight: 700; background: #fff; color: var(--ink-1); }
    .his-consulta select:focus { outline: none; border-color: var(--f1-red); box-shadow: 0 0 0 3px rgba(225,6,0,.12); }
    @media (max-width: 700px) { .his-campo, .his-consulta select { width: 100%; } }
    .his-resposta { flex: 1 1 320px; padding: 10px 14px; border-radius: 10px; background: #f6f7f9; border-left: 4px solid #adb5bd; font-size: .92rem; min-height: 42px; }
    .his-resposta.sim { background: #effaf2; border-left-color: #16a34a; }
    .his-resposta.nao { background: #fff4f3; border-left-color: var(--f1-red); }
    .his-resposta b.grande { font-size: 1.05rem; }

    /* chips de anos */
    .his-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .his-chip { display: inline-flex; flex-direction: column; align-items: center; min-width: 74px; padding: 6px 10px; border-radius: 10px; border: 1px solid var(--linha); background: #fafafa; cursor: pointer; line-height: 1.1; transition: border-color .15s, background-color .15s, transform .15s; font-family: inherit; color: var(--ink-1); }
    .his-chip b { font-size: 1.05rem; font-weight: 900; }
    .his-chip small { font-size: .68rem; color: var(--ink-3); font-weight: 700; white-space: nowrap; }
    .his-chip.sim { border-left: 4px solid #16a34a; }
    .his-chip.nao { border-left: 4px solid #e5e7eb; color: var(--ink-2); }
    .his-chip:hover, .his-chip:focus-visible { border-color: var(--f1-red); transform: translateY(-1px); outline: none; }
    .his-chip.ativo { background: var(--f1-darker); color: #fff; border-color: var(--f1-darker); }
    .his-chip.ativo small { color: #c3c2b7; }
    .his-nota { font-size: .82rem; color: var(--ink-3); margin: 10px 0 0; }

    /* tabelas de corridas */
    .his-tabela-topo { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--linha); }
    .his-tabela-topo h3 { margin: 0; font-size: .95rem; font-weight: 800; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
    .his-tabela-topo h3 i { color: var(--f1-red); }
    .his-filtro-ativo { display: none; align-items: center; gap: 6px; font-size: .8rem; font-weight: 700; background: var(--f1-darker); color: #fff; border-radius: 999px; padding: 3px 4px 3px 12px; }
    .his-filtro-ativo.on { display: inline-flex; }
    .his-filtro-ativo button { border: 0; background: rgba(255,255,255,.15); color: #fff; border-radius: 999px; width: 22px; height: 22px; line-height: 1; cursor: pointer; }
    .res-tabela th.ordenavel.asc::after { content: ' ▲'; opacity: .9; }
    .res-tabela th.ordenavel.desc::after { content: ' ▼'; opacity: .9; }
    .res-tabela tr.sem-resultado td { color: var(--ink-3); }
    .his-ref { display: inline-block; min-width: 46px; padding: 1px 8px; border-radius: 6px; font-weight: 800; background: #eef0f3; }
    .his-sprint .his-ref { background: #fff3cd; }
    .res-pill.sprint { background: #ffc107; color: #15151e; }
    .his-vazio-linha td { text-align: center !important; color: var(--ink-3); padding: 18px !important; }
</style>

<div class="res-page">
    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pistas.index') }}">Pistas</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $p['nome'] }}</li>
        </ol>
    </nav>

    {{-- ================= HERO ================= --}}
    <header class="res-hero">
        <div>
            <small class="res-kicker">Histórico e estatísticas da pista</small>
            <h1>{{ $p['nome'] }}</h1>
            <div class="res-hero-sub">
                @if($p['bandeira'])<img class="res-bandeira" src="{{ $p['bandeira'] }}" alt="">@endif
                <span>{{ $p['pais'] ?? '-' }}@if($p['continente']) · {{ $p['continente'] }}@endif</span>
                @if($p['autor'])<span class="res-pill info"><i class="bi bi-person"></i> {{ $p['autor'] }}</span>@endif
                @if($p['drs'])<span class="res-pill info">DRS: {{ $p['drs'] }}</span>@endif
                <span class="res-pill info">{{ $p['qtd_carros'] ?? 30 }} carros</span>
                @if($p['tamanho_km'])<span class="res-pill info">{{ $p['tamanho_km'] > 100 ? number_format($p['tamanho_km'] / 1000, 3, ',', '.') . ' km' : $p['tamanho_km'] . ' km' }}</span>@endif
                @if($p['qtd_voltas'])<span class="res-pill info">{{ $p['qtd_voltas'] }} voltas</span>@endif
                @if(!$p['ativa'])<span class="res-pill inativa">Inativa</span>@endif
            </div>
        </div>
        <div class="res-hero-acoes">
            <div class="res-links">
                <a href="{{ route('pistas.show', [$p['id']]) }}"><i class="bi bi-eye"></i> Resultados</a>
                <a href="{{ route('pistas.edit', [$p['id']]) }}"><i class="bi bi-pencil"></i> Editar</a>
                <a href="{{ route('pistas.index') }}"><i class="bi bi-arrow-left"></i> Pistas</a>
            </div>
        </div>
    </header>

    <div class="res-kpis">
        @foreach($dados['kpis'] as $k)
            <div class="res-kpi"><span><i class="bi {{ $k['icone'] }}"></i> {{ $k['rotulo'] }}</span><strong>{{ $k['valor'] }}</strong><small>{{ $k['detalhe'] }}</small></div>
        @endforeach
    </div>

    <nav class="res-nav" aria-label="Seções">
        <a href="#referencias">Anos de referência</a>
        @if($dados['curiosidades'])<a href="#curiosidades">Curiosidades</a>@endif
        @if($dados['rankingPilotos'])<a href="#vencedores">Vencedores</a>@endif
        @if($dados['sprints'])<a href="#sprints">Sprints</a>@endif
        <a href="#todas">Todas as corridas</a>
    </nav>

    {{-- ================= ANOS DE REFERÊNCIA ================= --}}
    <section class="res-secao" id="referencias">
        <h2>Anos de referência</h2>
        <p class="res-sub">A pista já recebeu corridas de determinado ano de referência? Escolha o ano ou clique num dos quadros abaixo.</p>

        <div class="res-card res-card-pad" style="margin-bottom: 16px;">
            <div class="his-consulta">
                <div class="his-campo">
                    <label for="filtroRefHistorico">Ano de referência</label>
                    {{-- id na lista de exceções do select2 global (layouts/main) --}}
                    {{-- MELHORIA 20261117: só os anos de referência que já tiveram corridas aqui --}}
                    <select id="filtroRefHistorico" @if(!$refs['recebeu']) disabled @endif>
                        <option value="">{{ $refs['recebeu'] ? 'Todos' : 'Nenhum' }}</option>
                        @foreach($refs['recebeu'] as $r)
                            <option value="{{ $r['ano'] }}">{{ $r['ano'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="his-resposta" id="respostaRef" aria-live="polite">
                    @if(count($refs['recebeu']))
                        Já recebeu <b>{{ count($refs['recebeu']) }}</b> {{ count($refs['recebeu']) == 1 ? 'ano de referência' : 'anos de referência diferentes' }}
                        e nunca recebeu <b>{{ count($refs['nunca']) }}</b> dos usados nas suas temporadas.
                    @else
                        Esta pista ainda não recebeu corridas com ano de referência.
                    @endif
                </div>
            </div>
        </div>

        <div class="res-grid-2">
            <div class="res-card res-card-pad">
                <h3 class="res-card-titulo"><i class="bi bi-check-circle-fill"></i> Já recebeu <small>{{ count($refs['recebeu']) }}</small></h3>
                @if($refs['recebeu'])
                    <div class="his-chips">
                        @foreach($refs['recebeu'] as $r)
                            <button type="button" class="his-chip sim" data-ref="{{ $r['ano'] }}"
                                    title="{{ implode(', ', $r['temporadas']) }}">
                                <b>{{ $r['ano'] }}</b>
                                <small>{{ $r['corridas'] }} {{ $r['corridas'] == 1 ? 'corrida' : 'corridas' }}@if($r['sprints']) · {{ $r['sprints'] }} spr.@endif</small>
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="res-muted" style="margin: 0;">Nenhum ano de referência por aqui ainda.</p>
                @endif
                @if($refs['sem'])
                    <p class="his-nota"><i class="bi bi-info-circle"></i> Mais {{ $refs['sem']['corridas'] + $refs['sem']['sprints'] }} em temporadas sem referência cadastrada ({{ implode(', ', $refs['sem']['temporadas']) }}).</p>
                @endif
            </div>
            <div class="res-card res-card-pad">
                <h3 class="res-card-titulo"><i class="bi bi-x-circle-fill"></i> Nunca recebeu <small>{{ count($refs['nunca']) }}</small></h3>
                @if($refs['nunca'])
                    <div class="his-chips">
                        @foreach($refs['nunca'] as $r)
                            <button type="button" class="his-chip nao" data-ref="{{ $r['ano'] }}"
                                    title="Temporadas com essa referência: {{ implode(', ', $r['temporadas']) }}">
                                <b>{{ $r['ano'] }}</b>
                                <small>{{ count($r['temporadas']) }} {{ count($r['temporadas']) == 1 ? 'temporada' : 'temporadas' }}</small>
                            </button>
                        @endforeach
                    </div>
                    <p class="his-nota">Anos de referência usados nas suas temporadas em que esta pista não apareceu.</p>
                @else
                    <p class="res-muted" style="margin: 0;">Já recebeu todos os anos de referência usados nas suas temporadas.</p>
                @endif
            </div>
        </div>
    </section>

    @if($dados['vazia'])
        <div class="res-card res-vazio" style="margin-top: 24px;">Nenhuma corrida disputada nesta pista ainda.</div>
    @else

    {{-- ================= CURIOSIDADES ================= --}}
    @if($dados['curiosidades'])
    <section class="res-secao" id="curiosidades">
        <h2>Curiosidades</h2>
        <p class="res-sub">Considerando só as corridas principais. As sprints têm curiosidades próprias mais abaixo.</p>
        <div class="res-fatos">
            @foreach($dados['curiosidades'] as $f)
                <div class="res-fato"><i class="bi {{ $f['icone'] }}"></i><div><b>{{ $f['titulo'] }}</b><p>{{ $f['texto'] }}</p></div></div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ================= VENCEDORES ================= --}}
    @if($dados['rankingPilotos'])
    <section class="res-secao" id="vencedores">
        <h2>Vencedores e números</h2>
        <p class="res-sub">Desempenho de pilotos e equipes nas corridas principais desta pista.</p>
        <div class="res-grid-2">
            <div class="res-card">
                <div class="res-tabela-wrap">
                    <table class="res-tabela {{ count($dados['rankingPilotos']) > 15 ? 'recolhida' : '' }} his-ordenavel" id="tabelaRankingPilotos">
                        <thead><tr>
                            <th>#</th><th class="esq ordenavel" data-tipo="texto">Piloto</th>
                            <th class="ordenavel" data-tipo="num">Vit.</th><th class="ordenavel" data-tipo="num">Pódios</th>
                            <th class="ordenavel" data-tipo="num">Poles</th><th class="ordenavel" data-tipo="num" title="Voltas mais rápidas">VR</th>
                            <th class="ordenavel" data-tipo="num" title="Corridas disputadas">Corr.</th><th class="ordenavel" data-tipo="num" title="Melhor resultado">Melhor</th>
                            <th class="ordenavel" data-tipo="num" title="Média de chegada (sem abandonos)">Média</th>
                        </tr></thead>
                        <tbody>
                            @foreach($dados['rankingPilotos'] as $i => $r)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="esq" data-valor="{{ $r['p']['nome'] }}">{!! $cel($r) !!}</td>
                                    <td class="{{ $r['vitorias'] ? 'forte' : 'zero' }}">{{ $r['vitorias'] }}</td>
                                    <td class="{{ $r['podios'] ? '' : 'zero' }}">{{ $r['podios'] }}</td>
                                    <td class="{{ $r['poles'] ? '' : 'zero' }}">{{ $r['poles'] }}</td>
                                    <td class="{{ $r['vr'] ? '' : 'zero' }}">{{ $r['vr'] }}</td>
                                    <td>{{ $r['disputas'] }}</td>
                                    <td data-valor="{{ $r['melhor'] ?? 99 }}">{{ $r['melhor'] ? $r['melhor'] . 'º' : '-' }}</td>
                                    <td data-valor="{{ $r['media'] ?? 99 }}">{{ $num($r['media']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($dados['rankingPilotos']) > 15)
                    <button type="button" class="res-expandir" data-alvo="tabelaRankingPilotos">Ver todos os {{ count($dados['rankingPilotos']) }} pilotos</button>
                @endif
            </div>
            <div class="res-card">
                <div class="res-tabela-wrap">
                    <table class="res-tabela his-ordenavel" id="tabelaRankingEquipes">
                        <thead><tr>
                            <th>#</th><th class="esq ordenavel" data-tipo="texto">Equipe</th>
                            <th class="ordenavel" data-tipo="num">Vit.</th><th class="ordenavel" data-tipo="num">Pódios</th>
                            <th class="ordenavel" data-tipo="num">Poles</th><th class="ordenavel" data-tipo="num" title="1º e 2º da mesma equipe">Dobradinhas</th>
                        </tr></thead>
                        <tbody>
                            @forelse($dados['rankingEquipes'] as $i => $r)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="esq" data-valor="{{ $r['eq']['nome'] }}"><span class="res-piloto-cel"><img class="res-logo mini" src="{{ $r['eq']['img'] }}" alt="" style="background-color: {{ $r['eq']['fundo'] }}"><a href="{{ $r['eq']['url'] }}">{{ $r['eq']['nome'] }}</a></span></td>
                                    <td class="{{ $r['vitorias'] ? 'forte' : 'zero' }}">{{ $r['vitorias'] }}</td>
                                    <td class="{{ $r['podios'] ? '' : 'zero' }}">{{ $r['podios'] }}</td>
                                    <td class="{{ $r['poles'] ? '' : 'zero' }}">{{ $r['poles'] }}</td>
                                    <td class="{{ $r['dobradinhas'] ? '' : 'zero' }}">{{ $r['dobradinhas'] }}</td>
                                </tr>
                            @empty
                                <tr class="his-vazio-linha"><td colspan="6">Sem resultados cadastrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ================= SPRINTS ================= --}}
    @if($dados['sprints'])
    <section class="res-secao" id="sprints">
        <h2>Sprints</h2>
        <p class="res-sub">{{ count($dados['sprints']) }} {{ count($dados['sprints']) == 1 ? 'sprint disputada' : 'sprints disputadas' }} nesta pista. Não somam no campeonato e ficam separadas das corridas principais.</p>
        @if($dados['curiosidadesSprint'])
            <div class="res-fatos" style="margin-bottom: 16px;">
                @foreach($dados['curiosidadesSprint'] as $f)
                    <div class="res-fato"><i class="bi {{ $f['icone'] }}"></i><div><b>{{ $f['titulo'] }}</b><p>{{ $f['texto'] }}</p></div></div>
                @endforeach
            </div>
        @endif
        @if($dados['rankingPilotosSprint'])
            <div class="res-card">
                <div class="res-tabela-wrap">
                    <table class="res-tabela {{ count($dados['rankingPilotosSprint']) > 15 ? 'recolhida' : '' }} his-ordenavel" id="tabelaRankingSprint">
                        <thead><tr>
                            <th>#</th><th class="esq ordenavel" data-tipo="texto">Piloto</th>
                            <th class="ordenavel" data-tipo="num">Vit.</th><th class="ordenavel" data-tipo="num">Pódios</th>
                            <th class="ordenavel" data-tipo="num">Poles</th><th class="ordenavel" data-tipo="num" title="Sprints disputadas">Sprints</th>
                            <th class="ordenavel" data-tipo="num" title="Melhor resultado">Melhor</th>
                        </tr></thead>
                        <tbody>
                            @foreach($dados['rankingPilotosSprint'] as $i => $r)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="esq" data-valor="{{ $r['p']['nome'] }}">{!! $cel($r) !!}</td>
                                    <td class="{{ $r['vitorias'] ? 'forte' : 'zero' }}">{{ $r['vitorias'] }}</td>
                                    <td class="{{ $r['podios'] ? '' : 'zero' }}">{{ $r['podios'] }}</td>
                                    <td class="{{ $r['poles'] ? '' : 'zero' }}">{{ $r['poles'] }}</td>
                                    <td>{{ $r['disputas'] }}</td>
                                    <td data-valor="{{ $r['melhor'] ?? 99 }}">{{ $r['melhor'] ? $r['melhor'] . 'º' : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($dados['rankingPilotosSprint']) > 15)
                    <button type="button" class="res-expandir" data-alvo="tabelaRankingSprint">Ver todos os {{ count($dados['rankingPilotosSprint']) }} pilotos</button>
                @endif
            </div>
        @endif
    </section>
    @endif

    {{-- ================= TODAS AS CORRIDAS ================= --}}
    <section class="res-secao" id="todas">
        <h2>Todas as corridas</h2>
        <p class="res-sub">Cada corrida disputada aqui, com a temporada, o ano e o ano de referência. Clique nos cabeçalhos para ordenar.</p>

        @foreach([['lista' => $dados['corridas'], 'id' => 'tabelaCorridasPista', 'titulo' => 'Corridas', 'icone' => 'bi-flag-fill', 'sprint' => false],
                  ['lista' => $dados['sprints'], 'id' => 'tabelaSprintsPista', 'titulo' => 'Sprints', 'icone' => 'bi-lightning-charge-fill', 'sprint' => true]] as $bloco)
            @if(count($bloco['lista']))
            <div class="res-card {{ $bloco['sprint'] ? 'his-sprint' : '' }}" style="margin-bottom: 16px;">
                <div class="his-tabela-topo">
                    <h3><i class="bi {{ $bloco['icone'] }}"></i> {{ $bloco['titulo'] }} <span class="res-muted" data-contador="{{ $bloco['id'] }}">({{ count($bloco['lista']) }})</span></h3>
                    <span class="his-filtro-ativo" data-filtro-tag>Referência <b data-filtro-ano></b> <button type="button" data-limpar-filtro aria-label="Limpar filtro">&times;</button></span>
                </div>
                <div class="res-tabela-wrap">
                    <table class="res-tabela his-ordenavel his-corridas" id="{{ $bloco['id'] }}">
                        <thead><tr>
                            <th>#</th>
                            <th class="esq ordenavel" data-tipo="num">Temporada</th>
                            <th class="ordenavel" data-tipo="num">Ano</th>
                            <th class="ordenavel" data-tipo="num">Referência</th>
                            <th class="ordenavel" data-tipo="num">Etapa</th>
                            <th class="esq ordenavel" data-tipo="texto">Evento</th>
                            <th class="esq ordenavel" data-tipo="texto">Pole</th>
                            <th class="esq ordenavel" data-tipo="texto">Vencedor</th>
                            <th class="esq ordenavel" data-tipo="texto">2º</th>
                            <th class="esq ordenavel" data-tipo="texto">3º</th>
                            @unless($bloco['sprint'])<th class="esq ordenavel" data-tipo="texto" title="Volta mais rápida">VR</th>@endunless
                            <th class="ordenavel" data-tipo="num" title="Largada do vencedor">Larg. venc.</th>
                            <th class="ordenavel" data-tipo="num" title="Abandonos">Aband.</th>
                            <th class="ordenavel" data-tipo="num" title="Safety car">SC</th>
                            <th>Ações</th>
                        </tr></thead>
                        <tbody>
                            @foreach($bloco['lista'] as $i => $c)
                                <tr data-ref="{{ $c['referencia'] }}" class="{{ $c['tem_resultado'] ? '' : 'sem-resultado' }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td class="esq" data-valor="{{ $c['temporada_id'] * 1000 + $c['ordem'] }}">{{ $c['temporada'] }}</td>
                                    <td data-valor="{{ $c['ano'] ?? 0 }}">{{ $c['ano'] ?? '-' }}</td>
                                    <td data-valor="{{ $c['referencia'] ?? 0 }}">@if($c['referencia'])<span class="his-ref">{{ $c['referencia'] }}</span>@else<span class="res-muted">-</span>@endif</td>
                                    <td data-valor="{{ $c['ordem'] }}">{{ $c['sprint'] ? 'Sprint' : $c['ordem'] . 'ª' }}</td>
                                    <td class="esq">
                                        {{ $c['evento'] ?? '-' }}
                                        @if($c['clima_icone'])<i class="{{ $c['clima_icone'] }}" title="{{ $c['clima'] }}"></i>@endif
                                        @if($c['safety_car'] > 0)<i class="bi bi-car-front-fill" title="Safety car"></i>@endif
                                    </td>
                                    <td class="esq" data-valor="{{ $c['pole']['p']['nome'] ?? '' }}">{!! $cel($c['pole']) !!}</td>
                                    <td class="esq forte" data-valor="{{ $c['p1']['p']['nome'] ?? '' }}">{!! $cel($c['p1']) !!}</td>
                                    <td class="esq" data-valor="{{ $c['p2']['p']['nome'] ?? '' }}">{!! $cel($c['p2']) !!}</td>
                                    <td class="esq" data-valor="{{ $c['p3']['p']['nome'] ?? '' }}">{!! $cel($c['p3']) !!}</td>
                                    @unless($bloco['sprint'])<td class="esq" data-valor="{{ $c['vr']['p']['nome'] ?? '' }}">{!! $cel($c['vr']) !!}</td>@endunless
                                    <td data-valor="{{ $c['p1']['largada'] ?? 0 }}">{{ isset($c['p1']['largada']) && $c['p1']['largada'] > 0 ? $c['p1']['largada'] . 'º' : '-' }}</td>
                                    <td data-valor="{{ $c['abandonos'] }}" class="{{ $c['abandonos'] ? '' : 'zero' }}">{{ $c['tem_resultado'] ? $c['abandonos'] : '-' }}</td>
                                    <td data-valor="{{ $c['safety_car'] }}" class="{{ $c['safety_car'] ? '' : 'zero' }}">{{ $c['safety_car'] }}</td>
                                    <td class="text-nowrap">
                                        <div class="d-inline-flex gap-1">
                                            <a class="btn btn-sm btn-outline-secondary" data-toggle="tooltip" data-placement="top" title="Visualizar resultados" href="{{ route('resultados.show', [$c['id']]) }}"><i class="bi bi-eye"></i></a>
                                            <a class="btn btn-sm btn-outline-dark" data-toggle="tooltip" data-placement="top" title="Resumo e estatísticas da corrida" href="{{ route('corridas.resumo', [$c['id']]) }}"><i class="bi bi-newspaper"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="his-vazio-linha" data-sem-linhas hidden><td colspan="15">Nenhuma {{ $bloco['sprint'] ? 'sprint' : 'corrida' }} com essa referência.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach
    </section>
    @endif

    <div style="margin-top: 20px; display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('pistas.index') }}" class="btn btn-secondary">Voltar</a>
    </div>
</div>

@php
    $refsJs = [
        'recebeu' => collect($refs['recebeu'])->keyBy('ano'),
        'nunca' => collect($refs['nunca'])->keyBy('ano'),
        'pista' => $p['nome'],
    ];
@endphp
<script>
(function () {
    const refs = @json($refsJs);
    const sel = document.getElementById('filtroRefHistorico');
    const resposta = document.getElementById('respostaRef');
    const respostaInicial = resposta.innerHTML;
    const esc = t => String(t ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const tabelasCorridas = document.querySelectorAll('table.his-corridas');
    const plural = (n, s, p) => n + ' ' + (n == 1 ? s : p);

    function aplicar(ano) {
        sel.value = ano;
        document.querySelectorAll('.his-chip').forEach(c => c.classList.toggle('ativo', c.dataset.ref === ano));

        // resposta: já recebeu / nunca recebeu
        resposta.className = 'his-resposta' + (ano ? (refs.recebeu[ano] ? ' sim' : ' nao') : '');
        if (!ano) {
            resposta.innerHTML = respostaInicial;
        } else if (refs.recebeu[ano]) {
            const r = refs.recebeu[ano];
            resposta.innerHTML = '<b class="grande"><i class="bi bi-check-circle-fill"></i> Sim, já recebeu ' + esc(ano) + '.</b><br>'
                + plural(r.corridas, 'corrida', 'corridas') + (r.sprints ? ' e ' + plural(r.sprints, 'sprint', 'sprints') : '')
                + ' em: ' + esc(r.temporadas.join(', ')) + '.'
                + (r.vencedores.length ? '<br><span class="res-muted">Vencedores: ' + esc(r.vencedores.join(' / ')) + '</span>' : '');
        } else {
            const n = refs.nunca[ano];
            resposta.innerHTML = '<b class="grande"><i class="bi bi-x-circle-fill"></i> Não, nunca recebeu ' + esc(ano) + '.</b>'
                + (n ? '<br>Existem ' + plural(n.temporadas.length, 'temporada', 'temporadas') + ' com essa referência (' + esc(n.temporadas.join(', ')) + ') e a pista não esteve em nenhuma.' : '');
        }

        // filtra as tabelas de corridas e sprints
        tabelasCorridas.forEach(t => {
            let visiveis = 0;
            t.querySelectorAll('tbody tr:not([data-sem-linhas])').forEach(tr => {
                const ok = !ano || tr.dataset.ref === ano;
                tr.style.display = ok ? '' : 'none';
                if (ok) visiveis++;
            });
            t.querySelector('[data-sem-linhas]').hidden = visiveis > 0;
            const card = t.closest('.res-card');
            card.querySelector('[data-contador]').textContent = '(' + visiveis + ')';
            card.querySelector('[data-filtro-tag]').classList.toggle('on', !!ano);
            card.querySelector('[data-filtro-ano]').textContent = ano;
        });
    }

    sel.addEventListener('change', () => aplicar(sel.value));
    document.querySelectorAll('.his-chip').forEach(c => c.addEventListener('click', () => {
        aplicar(c.classList.contains('ativo') ? '' : c.dataset.ref);
        resposta.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }));
    document.querySelectorAll('[data-limpar-filtro]').forEach(b => b.addEventListener('click', () => aplicar('')));

    // ordenação por coluna (data-valor quando existir)
    const norm = t => (t || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toUpperCase();
    document.querySelectorAll('table.his-ordenavel').forEach(tabela => {
        const tbody = tabela.tBodies[0];
        tabela.querySelectorAll('th.ordenavel').forEach(th => {
            th.addEventListener('click', () => {
                const col = th.cellIndex;
                const num = th.dataset.tipo === 'num';
                const asc = !th.classList.contains('asc');
                tabela.querySelectorAll('th.ordenavel').forEach(o => o.classList.remove('asc', 'desc'));
                th.classList.add(asc ? 'asc' : 'desc');
                const linhas = Array.from(tbody.rows).filter(tr => !tr.hasAttribute('data-sem-linhas'));
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
                const vazio = tbody.querySelector('[data-sem-linhas]');
                linhas.forEach((tr, i) => { tr.cells[0].textContent = i + 1; tbody.insertBefore(tr, vazio); });
            });
        });
    });

    // "ver todos" das tabelas recolhidas
    document.querySelectorAll('.res-expandir[data-alvo]').forEach(b => b.addEventListener('click', () => {
        document.getElementById(b.dataset.alvo).classList.remove('recolhida');
        b.remove();
    }));
})();
</script>
@endsection
