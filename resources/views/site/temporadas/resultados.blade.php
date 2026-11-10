@php
    use App\Models\Site\Piloto;

    // MELHORIA 20261110 (layout): redesenho da tela. Regras mantidas:
    //  - modo padrão mostra a CHEGADA; a rota .../porPontuacao mostra a LARGADA (mesmo comportamento de PilotoEquipe::getResultadoPilotoEquipe)
    //  - ?tipo=sprint mostra só as sprints; sem ele, só as corridas principais (pontos sem sprint)
    //  - abandono = célula vermelha
    $modoLargada = (bool) $porPontuacao;
    $ehSprint = $tipo === 'sprint';
    $rotaChegada = route('temporadas.resultados', [$temporada->id]) . ($ehSprint ? '?tipo=sprint' : '');
    $rotaLargada = route('temporadas.resultados', [$temporada->id, $porPontuacao ?: 'porPontuacao']) . ($ehSprint ? '?tipo=sprint' : '');
    $parametrosRota = $porPontuacao ? [$temporada->id, $porPontuacao] : [$temporada->id];
    $rotaCorridas = route('temporadas.resultados', $parametrosRota);
    $rotaSprints = route('temporadas.resultados', $parametrosRota) . '?tipo=sprint';

    $classeCelula = function ($r, $corrida) use ($modoLargada) {
        if (!$r) return 'vazia';
        if ($r->flg_abandono == 'S') return 'dnf';
        $pos = (int) ($modoLargada ? $r->largada : $r->chegada);
        // MELHORIA 20261110 (cores): só vencedor (ou pole, no modo largada), abandono e volta rápida têm destaque
        if ($pos === 1) return 'p1';
        return 'normal';
    };
@endphp

@extends('layouts.main')

@section('section')

<style>
    /* tudo escopado em .res-f1: o layout global (classificacao.css) zera margin/padding e redefine .row */
    .res-f1 {
        --res-fundo: #15151e;
        --res-painel: #1c1c26;
        --res-linha: #2b2b35;
        --res-texto-fraco: #8a8a94;
        --res-vermelho: #e10600;
        --res-sprint: #ffc107;
        --res-acento: var(--res-vermelho);
        --w-pos: 46px;
        --w-piloto: 220px;
        --w-pts: 64px;
        padding: 16px 12px 40px;
        max-width: none; /* MELHORIA 20261110 (hover): grade usa toda a largura */
        margin: 0 auto;
        font-family: 'Roboto Condensed', -apple-system, 'Segoe UI', sans-serif;
    }
    .res-f1.modo-sprint { --res-acento: var(--res-sprint); }

    .res-f1 .breadcrumb { margin-bottom: 14px; }
    .res-f1 .breadcrumb-item { display: flex; align-items: center; }
    .res-f1 .breadcrumb-item .breadcrumb-icon { margin-right: 5px; }

    .res-card {
        background-color: var(--res-fundo);
        color: #fff;
        border-radius: 10px;
        border-top: 4px solid var(--res-acento);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }

    /* ---------- cabeçalho ---------- */
    .res-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px 16px;
        border-bottom: 1px solid var(--res-linha);
    }
    .res-eyebrow {
        display: block;
        color: var(--res-texto-fraco);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    .res-titulo {
        margin: 4px 0 6px;
        font-size: 1.8rem;
        font-weight: 800;
        font-style: italic;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1.1;
    }
    .res-titulo .destaque { color: var(--res-acento); }
    .res-resumo { color: var(--res-texto-fraco); font-size: 0.9rem; }
    .res-resumo strong { color: #fff; }

    .res-controles { display: flex; flex-wrap: wrap; gap: 14px; }
    .res-grupo { display: flex; flex-direction: column; gap: 5px; }
    .res-grupo-rotulo {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--res-texto-fraco);
        padding-left: 4px;
    }
    .res-segmentado {
        display: inline-flex;
        background-color: #0b0b10;
        border: 1px solid var(--res-linha);
        border-radius: 999px;
        padding: 3px;
    }
    .res-segmentado a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        border-radius: 999px;
        color: #8a8a94;
        font-size: 0.82rem;
        font-weight: 700;
        font-style: italic;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-decoration: none;
        transition: color 0.2s, background-color 0.2s;
        white-space: nowrap;
    }
    .res-segmentado a:hover { color: #fff; }
    .res-segmentado a.ativo { background-color: var(--res-vermelho); color: #fff; box-shadow: 0 0 12px rgba(225, 6, 0, 0.45); pointer-events: none; }
    .res-segmentado a.ativo.sprint { background-color: var(--res-sprint); color: #15151e; box-shadow: 0 0 12px rgba(255, 193, 7, 0.4); }
    .res-segmentado a.ativo.neutro { background-color: #fff; color: #15151e; box-shadow: none; }
    .res-segmentado svg { width: 14px; height: 14px; fill: currentColor; }

    /* ---------- legenda ---------- */
    .res-legenda {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 16px;
        padding: 10px 22px;
        background-color: var(--res-painel);
        border-bottom: 1px solid var(--res-linha);
        font-size: 0.8rem;
        color: #c8c8d0;
    }
    .res-legenda span { display: inline-flex; align-items: center; gap: 6px; }
    .res-legenda i {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 3px;
    }

    /* ---------- grade ---------- */
    .res-grade-wrap { overflow-x: auto; }
    .res-grade {
        border-collapse: separate;
        border-spacing: 0;
        width: auto; /* com poucas etapas a grade não estica as colunas */
        font-size: 0.92rem;
    }
    .res-grade th, .res-grade td {
        padding: 0;
        border-bottom: 1px solid var(--res-linha);
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
    .res-grade thead th {
        background-color: var(--res-painel);
        color: var(--res-texto-fraco);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 10px 4px 8px;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .res-grade .col-etapa { min-width: 56px; cursor: default; }
    .res-grade.preenche { width: 100%; } /* com várias etapas a grade ocupa a largura toda */
    .res-grade .col-etapa img {
        display: block;
        width: 30px;
        height: 20px;
        margin: 0 auto 4px;
        object-fit: cover;
        border-radius: 2px;
        border: 1px solid #333338;
    }
    .res-grade .col-etapa .rotulo-etapa { color: #c8c8d0; }

    /* colunas fixas à esquerda: posição, piloto e pontos ficam visíveis ao rolar as etapas */
    .res-grade .fixa { position: sticky; z-index: 1; background-color: var(--res-fundo); }
    .res-grade thead .fixa { z-index: 3; background-color: var(--res-painel); }
    .res-grade .fixa-pos { left: 0; width: var(--w-pos); min-width: var(--w-pos); }
    .res-grade .fixa-piloto { left: var(--w-pos); width: var(--w-piloto); min-width: var(--w-piloto); max-width: var(--w-piloto); text-align: left; }
    .res-grade .fixa-pts { left: calc(var(--w-pos) + var(--w-piloto)); width: var(--w-pts); min-width: var(--w-pts); border-right: 2px solid var(--res-linha); }

    .res-grade tbody tr:hover td { background-color: #22222d; }
    /* MELHORIA 20261110 (hover): coluna da corrida sob o mouse */
    .res-grade td.col-ativa { background-color: #262633 !important; }
    .res-grade th.col-ativa { background-color: #2b2b38; color: #fff; box-shadow: inset 0 -3px 0 var(--res-acento); }
    .res-grade th.col-ativa .rotulo-etapa { color: #fff; }

    .res-legenda { align-items: center; min-height: 41px; }
    .res-legenda .itens-legenda { display: flex; flex-wrap: wrap; gap: 6px 16px; }
    .res-info-corrida { display: none; gap: 14px; flex-wrap: wrap; align-items: center; color: #fff; font-size: 0.82rem; }
    .res-info-corrida.visivel { display: inline-flex; }
    .res-info-corrida .titulo { font-weight: 800; font-style: italic; text-transform: uppercase; letter-spacing: 0.5px; }
    .res-info-corrida .titulo small { color: var(--res-texto-fraco); font-style: normal; font-weight: 400; text-transform: none; letter-spacing: 0; }
    .res-info-corrida .dado { color: var(--res-texto-fraco); }
    .res-info-corrida .dado b { color: #fff; font-weight: 700; }
    .res-info-corrida .marca { display: inline-block; width: 9px; height: 9px; border-radius: 2px; margin-right: 4px; vertical-align: middle; }
    .res-grade tbody td { height: 44px; }

    .celula-pos { font-weight: 800; font-style: italic; color: #fff; }
    .celula-pos.top1 { color: #f5c542; }

    .celula-piloto {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 10px 0 0;
        height: 44px;
        overflow: hidden;
    }
    .celula-piloto .barra-equipe { width: 4px; align-self: stretch; margin: 8px 0; border-radius: 2px; flex-shrink: 0; }
    .celula-piloto img { width: 26px; height: 26px; object-fit: contain; flex-shrink: 0; }
    .celula-piloto .nome { overflow: hidden; text-overflow: ellipsis; color: #fff; }
    .celula-piloto .nome .primeiro { font-weight: 300; opacity: 0.8; }
    .celula-piloto .nome .sobrenome { font-weight: 800; text-transform: uppercase; }

    .celula-pts { font-weight: 800; font-size: 1rem; color: var(--res-acento); }

    /* células de posição */
    .res-grade td.etapa { padding: 4px 3px; }
    .chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 30px;
        padding: 0 4px;
        border-radius: 5px;
        font-weight: 700;
        font-size: 0.88rem;
        position: relative;
    }
    .chip.p1 { background-color: #f5c542; color: #1a1400; }
    .chip.p2 { background-color: #cfd3d8; color: #15151e; }
    .chip.p3 { background-color: #c97a3d; color: #fff; }
    .chip.pontua { background-color: #1e5b3a; color: #d9f7e5; }
    .chip.normal { background-color: transparent; color: #c8c8d0; }
    .chip.dnf { background-color: #b81414; color: #fff; }
    .chip.vazia { color: #4a4a55; }
    .chip.volta-rapida { box-shadow: 0 0 0 2px #a855f7; }
    .chip.volta-rapida::after {
        content: '';
        position: absolute;
        top: -3px;
        right: -3px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #a855f7;
        border: 1px solid var(--res-fundo);
    }

    .res-vazio { padding: 40px 20px; text-align: center; color: var(--res-texto-fraco); }

    .res-acoes { margin-top: 18px; }
    .res-acoes a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 22px;
        border-radius: 999px;
        background-color: #15151e;
        color: #fff;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .res-acoes a:hover { background-color: var(--res-vermelho); color: #fff; }

    @media (max-width: 768px) {
        .res-f1 { --w-piloto: 140px; --w-pos: 38px; --w-pts: 54px; padding: 10px 6px 30px; }
        .res-header { padding: 16px 14px 12px; }
        .res-titulo { font-size: 1.4rem; }
        .res-legenda { padding: 8px 14px; }
        .celula-piloto .nome .primeiro, .celula-piloto img { display: none; }
    }
</style>

<div class="res-f1 {{ $ehSprint ? 'modo-sprint' : '' }}">
    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">
                    <span class="breadcrumb-icon"><i class="fas fa-home"></i></span> Home
                </a>
            </li>
            <li class="breadcrumb-item active">Temporadas</li>
            <li class="breadcrumb-item active" aria-current="page">
                <span class="breadcrumb-icon"><i class="fas fa-calendar"></i></span> Resultados - {{$temporada->ano->ano}}
            </li>
        </ol>
    </nav>

    <div class="res-card">
        <header class="res-header">
            <div>
                <span class="res-eyebrow">{{ $temporada->des_temporada }}@if($temporada->referencia) • {{ $temporada->referencia }}@endif</span>
                <h1 class="res-titulo">Resultados @if($ehSprint)<span class="destaque">· Sprints</span>@endif</h1>
                <div class="res-resumo">
                    <strong>{{ count($corridas) }}</strong> {{ $ehSprint ? (count($corridas) == 1 ? 'sprint' : 'sprints') : (count($corridas) == 1 ? 'corrida' : 'corridas') }} ·
                    <strong>{{ count($resultadosPilotos) }}</strong> {{ count($resultadosPilotos) == 1 ? 'piloto' : 'pilotos' }} ·
                    exibindo posição de <strong>{{ $modoLargada ? 'largada' : 'chegada' }}</strong>
                </div>
            </div>

            <div class="res-controles">
                @if($temSprint)
                    <div class="res-grupo">
                        <span class="res-grupo-rotulo">Tipo</span>
                        <div class="res-segmentado">
                            <a href="{{ $rotaCorridas }}" class="{{ $ehSprint ? '' : 'ativo' }}" @if(!$ehSprint) aria-current="page" @endif>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg> Corridas
                            </a>
                            <a href="{{ $rotaSprints }}" class="sprint {{ $ehSprint ? 'ativo' : '' }}" @if($ehSprint) aria-current="page" @endif>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg> Sprints
                            </a>
                        </div>
                    </div>
                @endif
                <div class="res-grupo">
                    <span class="res-grupo-rotulo">Posição</span>
                    <div class="res-segmentado">
                        <a href="{{ $rotaChegada }}" class="neutro {{ $modoLargada ? '' : 'ativo' }}">Chegada</a>
                        <a href="{{ $rotaLargada }}" class="neutro {{ $modoLargada ? 'ativo' : '' }}">Largada</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="res-legenda">
            <div class="itens-legenda" id="itensLegenda">
            <span><i style="background:#f5c542"></i> {{ $modoLargada ? 'Pole position' : 'Vencedor' }}</span>
            <span><i style="background:transparent; box-shadow: 0 0 0 2px #a855f7"></i> Volta mais rápida</span>
            <span><i style="background:#b81414"></i> Abandono</span>
            </div>
            {{-- MELHORIA 20261110 (hover): resumo da corrida sob o mouse --}}
            <div class="res-info-corrida" id="infoCorrida" aria-live="polite"></div>
        </div>

        @if(count($resultadosPilotos) > 0 && count($corridas) > 0)
            <div class="res-grade-wrap">
                <table class="res-grade {{ count($corridas) >= 8 ? 'preenche' : '' }}" id="gradeResultados">
                    <thead>
                        <tr>
                            <th class="fixa fixa-pos">Pos</th>
                            <th class="fixa fixa-piloto" style="padding-left: 14px;">Piloto</th>
                            <th class="fixa fixa-pts">Pts</th>
                            @foreach ($corridas as $indiceCorrida => $corrida)
                                <th class="col-etapa" data-col="{{ $indiceCorrida }}">
                                    <img src="{{ asset('images/' . $corrida->pista->pais->imagem) }}" alt="">
                                    <span class="rotulo-etapa">{{ $corrida->flg_sprint == 'S' ? 'S' : 'R' }}{{ $corrida->ordem }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultadosPilotos as $key => $piloto)
                            @php
                                // MELHORIA 20261105: logo/cor da equipe da última corrida do piloto (quem trocou de equipe no ano)
                                $logoEquipe = $piloto->equipe_ultima_imagem ?? optional(Piloto::equipeAtual($temporada->ano->id, $piloto->piloto_id))->imagem;
                                $corEquipe = $piloto->equipe_ultima_cor ?? $piloto->cor ?? '#555';
                                $posicaoGeral = $key + 1;
                            @endphp
                            <tr>
                                <td class="fixa fixa-pos celula-pos {{ $posicaoGeral <= 3 ? 'top' . $posicaoGeral : '' }}">{{ $posicaoGeral }}</td>
                                <td class="fixa fixa-piloto">
                                    <div class="celula-piloto" title="{{ $piloto->nome }} {{ $piloto->sobrenome }} · {{ $piloto->equipe_ultima ?? '' }}">
                                        <span class="barra-equipe" style="background-color: {{ $corEquipe }};"></span>
                                        <img src="{{ asset('images/' . $logoEquipe) }}" alt="">
                                        <span class="nome"><span class="primeiro">{{ $piloto->nome }}</span> <span class="sobrenome">{{ $piloto->sobrenome }}</span></span>
                                    </div>
                                </td>
                                <td class="fixa fixa-pts celula-pts">{{ $piloto->total }}</td>

                                @foreach ($corridas as $indiceCorrida => $corrida)
                                    @php
                                        $r = $mapaResultados[$corrida->id][$piloto->piloto_id] ?? null;
                                        $valor = $r ? ($modoLargada ? $r->largada : $r->chegada) : null;
                                        $classe = $valor ? $classeCelula($r, $corrida) : 'vazia';
                                        $voltaRapida = $r && $corrida->volta_rapida && $corrida->volta_rapida == $r->pilotoEquipe_id;
                                        $dica = $r ? ('Largada ' . ($r->largada ?: '-') . ' · Chegada ' . ($r->chegada ?: '-') . ' · ' . (float) $r->pontuacao . ' pts'
                                                    . ($r->flg_abandono == 'S' ? ' · Abandono' : '') . ($voltaRapida ? ' · Volta mais rápida' : '')) : '';
                                    @endphp
                                    <td class="etapa" data-col="{{ $indiceCorrida }}">
                                        <span class="chip {{ $classe }} {{ $voltaRapida && $valor ? 'volta-rapida' : '' }}" @if($dica) title="{{ $dica }}" @endif>{{ $valor ?: '-' }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="res-vazio">Sem {{ $ehSprint ? 'sprints' : 'corridas' }} com resultados registrados nesta temporada.</div>
        @endif
    </div>

    <div class="res-acoes">
        <a href="{{route('temporadas.index')}}">
            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/></svg>
            Voltar
        </a>
    </div>
</div>

@php
    // MELHORIA 20261110 (hover): resumo de cada corrida exibida (mesmos dados da grade, sem consultas extras)
    $nomesPilotos = collect($resultadosPilotos)->mapWithKeys(fn ($p) => [$p->piloto_id => trim($p->nome . ' ' . $p->sobrenome)]);
    $resumoCorridas = $corridas->map(function ($corrida) use ($mapaResultados, $nomesPilotos) {
        $rs = collect($mapaResultados[$corrida->id] ?? []);
        $nome = fn ($r) => $r ? ($nomesPilotos[$r->piloto_id] ?? '-') : null;
        return [
            'etapa' => ($corrida->flg_sprint == 'S' ? 'S' : 'R') . $corrida->ordem,
            'evento' => optional($corrida->evento)->des_nome,
            'pista' => optional($corrida->pista)->nome,
            'sprint' => $corrida->flg_sprint == 'S',
            'vencedor' => $nome($rs->first(fn ($r) => (int) $r->chegada === 1)),
            'pole' => $nome($rs->first(fn ($r) => (int) $r->largada === 1)),
            'voltaRapida' => $corrida->volta_rapida ? $nome($rs->first(fn ($r) => $r->pilotoEquipe_id == $corrida->volta_rapida)) : null,
            'abandonos' => $rs->where('flg_abandono', 'S')->count(),
        ];
    })->values();
@endphp
<script>
(function () {
    const grade = document.getElementById('gradeResultados');
    const info = document.getElementById('infoCorrida');
    const legenda = document.getElementById('itensLegenda');
    if (!grade || !info) return;
    const corridas = @json($resumoCorridas);
    const esc = t => String(t ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    let colunaAtiva = null;

    function ativar(col) {
        if (col === colunaAtiva) return;
        limpar();
        colunaAtiva = col;
        grade.querySelectorAll('[data-col="' + col + '"]').forEach(el => el.classList.add('col-ativa'));
        const c = corridas[col];
        if (!c) return;
        const dados = [
            c.vencedor ? '<span class="dado"><span class="marca" style="background:#f5c542"></span>Vencedor <b>' + esc(c.vencedor) + '</b></span>' : '',
            c.pole ? '<span class="dado">Pole <b>' + esc(c.pole) + '</b></span>' : '',
            c.voltaRapida ? '<span class="dado"><span class="marca" style="box-shadow:0 0 0 2px #a855f7"></span>VMR <b>' + esc(c.voltaRapida) + '</b></span>' : '',
            '<span class="dado"><span class="marca" style="background:#b81414"></span>Abandonos <b>' + c.abandonos + '</b></span>',
        ].join('');
        info.innerHTML = '<span class="titulo">' + esc(c.etapa) + ' · ' + esc(c.evento || c.pista) +
            (c.evento && c.pista ? ' <small>' + esc(c.pista) + '</small>' : '') + '</span>' + dados;
        info.classList.add('visivel');
        if (legenda) legenda.style.display = 'none';
    }

    function limpar() {
        if (colunaAtiva === null) return;
        grade.querySelectorAll('.col-ativa').forEach(el => el.classList.remove('col-ativa'));
        colunaAtiva = null;
        info.classList.remove('visivel');
        if (legenda) legenda.style.display = '';
    }

    grade.addEventListener('mouseover', e => {
        const cel = e.target.closest('[data-col]');
        if (cel) ativar(cel.dataset.col); else limpar();
    });
    grade.addEventListener('mouseleave', limpar);
})();
</script>

@endsection
