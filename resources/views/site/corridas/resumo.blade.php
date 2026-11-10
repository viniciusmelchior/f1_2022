{{-- MELHORIA 20261110 (resumo da corrida): dados em App\Services\CorridaResumoService --}}
@extends('layouts.main')

@section('section')
@php
    $c = $dados['corrida'];
    $nav = $dados['navegacao'];
    $num = fn ($v) => $v === null ? '-' : (floor($v) == $v ? number_format($v, 0, ',', '.') : number_format($v, 1, ',', '.'));
    $tipoNome = $c['sprint'] ? 'sprint' : 'corrida';
@endphp

@include('site.partials.resumo-estilos')
<style>
    /* complementos do resumo da corrida */
    .res-hero-sub { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px; color: #c3c2b7; font-size: .9rem; margin-top: 4px; }
    .res-hero-sub .res-bandeira { width: 28px; height: 19px; }
    .res-pill.sprint { background: #ffc107; color: #15151e; }
    .res-pill.fase { background: #00d2be; color: #06332f; }
    .res-pill.etapa { background: rgba(255,255,255,.12); color: #fff; }
    .res-tag-status { display: inline-block; font-size: .66rem; font-weight: 800; letter-spacing: .5px; padding: 1px 6px; border-radius: 4px; margin-left: 4px; text-transform: uppercase; }
    .res-tag-status.vr { background: #6250d6; color: #fff; }
    .res-tag-status.ab { background: #fde2e1; color: #b42318; }
    .res-tag-status.est { background: #e0f2fe; color: #075985; }
    .res-tag-status.pole { background: var(--f1-darker); color: #fff; }
    .res-tabela tr.abandono td { color: #8a8985; }
    .res-tabela tr.podio-1 td:first-child { box-shadow: inset 4px 0 0 var(--ouro); }
    .res-tabela tr.podio-2 td:first-child { box-shadow: inset 4px 0 0 var(--prata); }
    .res-tabela tr.podio-3 td:first-child { box-shadow: inset 4px 0 0 var(--bronze); }
    .res-delta-camp { font-size: .72rem; font-weight: 800; margin-left: 4px; }
    .res-delta-camp.sobe { color: #006300; }
    .res-delta-camp.desce { color: #b42318; }
    .res-delta-camp.igual { color: #b9bcc2; }
    .res-mini-lista { list-style: none; margin: 0; padding: 0; }
    .res-mini-lista li { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-top: 1px solid #f2f2f2; }
    .res-mini-lista li:first-child { border-top: 0; }
    .res-mini-lista .res-foto { width: 34px; height: 34px; }
    .res-mini-lista .nome { flex: 1; min-width: 0; font-weight: 800; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--ink-1); }
    .res-mini-lista .nome small { display: block; font-weight: 600; color: var(--ink-3); font-size: .76rem; }
    .res-mini-lista .valor { font-weight: 900; font-size: 1.05rem; white-space: nowrap; }
    .res-mini-lista .valor small { font-weight: 600; color: var(--ink-3); font-size: .72rem; }
    .res-mini-lista li.hoje .nome { color: var(--f1-red); }
    .res-card h3.res-card-titulo { font-size: .9rem; font-weight: 800; text-transform: uppercase; margin: 0 0 10px; display: flex; align-items: center; gap: 8px; }
    .res-card h3.res-card-titulo i { color: var(--f1-red); }
    .res-destaques { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 12px; }
    @media (max-width: 700px) { .res-destaques { grid-template-columns: 1fr; } }
    .res-destaque { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--linha); background: #fafafa; min-width: 0; }
    .res-destaque i { font-size: 1.3rem; color: var(--f1-red); }
    .res-destaque small { display: block; font-size: .66rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--ink-3); }
    .res-destaque b { font-weight: 800; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>

<div class="res-page">
    {{-- ================= HERO ================= --}}
    <header class="res-hero">
        <div>
            <small class="res-kicker">Resumo da {{ $tipoNome }} · {{ $c['temporada'] }}</small>
            <h1>{{ $c['evento'] }}</h1>
            <div class="res-hero-sub">
                @if($c['bandeira'])<img class="res-bandeira" src="{{ $c['bandeira'] }}" alt="">@endif
                <span>{{ $c['pista'] }}@if($c['pais']) · {{ $c['pais'] }}@endif</span>
                @if(!$dados['vazia'])<span class="res-pill etapa">{{ $c['sprint'] ? 'Sprint' : 'Etapa' }} {{ $dados['contagem']['n'] }} de {{ $dados['contagem']['total'] }}</span>@endif
                @if($c['sprint'])<span class="res-pill sprint">Sprint · não soma no campeonato</span>@endif
                @if($c['fase'])<span class="res-pill fase">Playoff · {{ $c['fase']['nome'] }}@if($c['fase']['n']) {{ $c['fase']['n'] }}/{{ $c['fase']['total'] }}@endif</span>@endif
                @if($c['clima'])<span><i class="bi bi-cloud-sun"></i> {{ $c['clima'] }}</span>@endif
            </div>
        </div>
        <div class="res-hero-acoes">
            <div class="res-navtemp">
                @if($nav['anterior'])
                    <a href="{{ $nav['anterior']['url'] }}" title="Anterior: {{ $nav['anterior']['nome'] }}"><i class="bi bi-chevron-left"></i></a>
                @else
                    <span class="off"><i class="bi bi-chevron-left"></i></span>
                @endif
                {{-- id "temporada" fica fora do select2 global do layout --}}
                <select id="temporada" aria-label="Escolher corrida da temporada">
                    @foreach($nav['lista'] as $item)
                        <option value="{{ $item['url'] }}" @if($item['id'] === $corrida->id) selected @endif>{{ $item['nome'] }}</option>
                    @endforeach
                </select>
                @if($nav['proxima'])
                    <a href="{{ $nav['proxima']['url'] }}" title="Próxima: {{ $nav['proxima']['nome'] }}"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="off"><i class="bi bi-chevron-right"></i></span>
                @endif
            </div>
            <div class="res-links">
                <a href="{{ route('resultados.show', [$corrida->id]) }}"><i class="bi bi-eye"></i> Resultado</a>
                <a href="{{ route('temporadas.classificacao', [$c['temporada_id']]) }}"><i class="bi bi-trophy"></i> Classificação</a>
                <a href="{{ route('temporadas.resumo', [$c['temporada_id']]) }}"><i class="bi bi-newspaper"></i> Resumo da temporada</a>
                <a href="{{ route('corridas.index', [$c['temporada_id']]) }}"><i class="bi bi-arrow-left"></i> Corridas</a>
            </div>
        </div>
    </header>

    @if($dados['vazia'])
        <div class="res-card res-vazio" style="margin-top: 20px;">Esta {{ $tipoNome }} ainda não tem resultado cadastrado.
            <div style="margin-top: 10px;"><a class="btn btn-sm btn-outline-success" href="{{ route('resultados.edit', [$corrida->id]) }}"><i class="bi bi-clipboard-plus"></i> Inserir resultados</a></div>
        </div>
    @else
    @php
        $camp = $dados['campeonato'];
        $podio = $dados['podio'];
        $pole = collect($dados['linhas'])->firstWhere('largada', 1);
        $vr = collect($dados['linhas'])->firstWhere('vr', true);
    @endphp

    <div class="res-kpis">
        @foreach($dados['kpis'] as $k)
            <div class="res-kpi"><span><i class="bi {{ $k['icone'] }}"></i> {{ $k['rotulo'] }}</span><strong>{{ $k['valor'] }}</strong><small>{{ $k['detalhe'] }}</small></div>
        @endforeach
    </div>

    <nav class="res-nav" aria-label="Seções">
        <a href="#noticias">Manchetes</a>
        <a href="#podio">Pódio</a>
        <a href="#resultado">Resultado</a>
        <a href="#campeonato">Campeonato</a>
        <a href="#jejuns">Sem vencer</a>
        <a href="#aconteceu">Aconteceu</a>
        <a href="#nao-aconteceu">Não aconteceu</a>
        <a href="#pista">A pista</a>
    </nav>

    {{-- ================= MANCHETES ================= --}}
    <section class="res-secao" id="noticias">
        <div class="res-jornal">
            <div class="res-jornal-cab">
                <b>Gazeta do Paddock</b>
                <span>{{ $c['sprint'] ? 'Sprint' : $c['ordem'] . 'ª etapa' }} · {{ $c['evento'] }} · {{ $c['temporada'] }}</span>
            </div>
            <div class="res-noticias">
                @foreach($dados['manchetes'] as $i => $m)
                    <article class="res-noticia {{ $i === 0 ? 'principal' : '' }}" @if(!empty($m['cor'])) style="--cor-noticia: {{ $m['cor'] }}" @endif>
                        @if($i === 0 && !empty($m['foto']))<img class="res-noticia-foto" src="{{ $m['foto'] }}" alt="" loading="lazy">@endif
                        <div>
                            <span class="res-tag">{{ $m['tag'] }}</span>
                            <h3>{{ $m['titulo'] }}</h3>
                            <p>{{ $m['texto'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= PÓDIO ================= --}}
    <section class="res-secao" id="podio">
        <h2>Pódio</h2>
        <p class="res-sub">Os três primeiros, a pole e a volta mais rápida.</p>
        <div class="res-card res-card-pad">
            <div class="res-podio" style="max-width: 720px; margin: 0 auto;">
                @foreach($podio as $i => $l)
                    <a class="res-degrau p{{ $i + 1 }}" href="{{ $l['p']['url'] }}" style="color: inherit;">
                        <span class="pos">{{ $i + 1 }}º</span><br>
                        <img class="res-foto" src="{{ $l['p']['img'] }}" alt="">
                        <b title="{{ $l['p']['nome'] }}">{{ $l['p']['nome'] }}</b>
                        <small><img class="res-logo mini" src="{{ $l['eq']['img'] }}" alt="" style="background: {{ $l['eq']['fundo'] }}"> {{ $l['eq']['nome'] }}</small>
                        <strong>{{ $num($l['pontos']) }} pts</strong>
                        <small>{{ $l['largada'] > 0 ? 'largou em ' . $l['largada'] . 'º' : '' }}</small>
                    </a>
                @endforeach
            </div>
            <div class="res-destaques">
                @if($pole)
                    <div class="res-destaque"><i class="bi bi-bullseye"></i><div style="min-width: 0;"><small>Pole position</small><b>{{ $pole['p']['nome'] }}</b><span class="res-muted">terminou em {{ $pole['chegada'] }}º{{ $pole['abandono'] ? ' (abandono)' : '' }}</span></div></div>
                @endif
                @if($vr)
                    <div class="res-destaque"><i class="bi bi-stopwatch"></i><div style="min-width: 0;"><small>Volta mais rápida</small><b>{{ $vr['p']['nome'] }}</b><span class="res-muted">{{ $vr['chegada'] }}º na chegada</span></div></div>
                @endif
                @if($camp['lider'])
                    <div class="res-destaque"><i class="bi bi-trophy"></i><div style="min-width: 0;"><small>Líder {{ $c['sprint'] ? 'das sprints' : 'do campeonato' }}</small><b>{{ $camp['lider']['nome'] }}</b><span class="res-muted">{{ $camp['novoLider'] ? 'novo líder' : ($camp['liderAntes'] ? 'segue na ponta' : 'primeiro líder') }}@if($camp['vantagem'] !== null) · {{ $camp['vantagem'] == 0 ? 'empatado em pontos' : '+' . $num($camp['vantagem']) . ' pts' }} @endif</span></div></div>
                @endif
            </div>
        </div>
    </section>

    {{-- ================= RESULTADO ================= --}}
    <section class="res-secao" id="resultado">
        <h2>Resultado completo</h2>
        <p class="res-sub">+/- = posições ganhas da largada à chegada. "Camp." = posição na classificação {{ $c['sprint'] ? 'das sprints' : 'geral' }} depois desta {{ $tipoNome }} e quanto mudou.</p>
        <div class="res-card res-tabela-wrap">
            <table class="res-tabela {{ count($dados['linhas']) > 15 ? 'recolhida' : '' }}" id="tabelaResultado">
                <thead><tr><th>Pos</th><th class="esq">Piloto</th><th class="esq">Equipe</th><th>Largada</th><th>+/-</th><th>Pts</th><th class="esq">Status</th><th>Camp.</th></tr></thead>
                <tbody>
                    @foreach($dados['linhas'] as $l)
                        @php
                            $posCamp = $camp['posDepois'][$l['piloto']] ?? null;
                            $antesCamp = $camp['posAntes'][$l['piloto']] ?? null;
                            $dCamp = $posCamp && $antesCamp ? $antesCamp - $posCamp : null;
                        @endphp
                        <tr class="{{ $l['abandono'] ? 'abandono' : '' }} {{ $l['chegada'] <= 3 ? 'podio-' . $l['chegada'] : '' }}">
                            <td class="forte">{{ $l['chegada'] }}</td>
                            <td class="esq"><span class="res-piloto-cel">@if($l['p']['bandeira'])<img class="res-bandeira" src="{{ $l['p']['bandeira'] }}" alt="">@endif<a class="forte" href="{{ $l['p']['url'] }}">{{ $l['p']['nome'] }}</a></span></td>
                            <td class="esq"><span class="res-piloto-cel"><img class="res-logo mini" src="{{ $l['eq']['img'] }}" alt="" style="background: {{ $l['eq']['fundo'] }}"><a href="{{ $l['eq']['url'] }}">{{ $l['eq']['nome'] }}</a></span></td>
                            <td>{{ $l['largada'] > 0 ? $l['largada'] : '-' }}</td>
                            <td class="{{ $l['ganho'] > 0 ? 'res-positivo' : ($l['ganho'] < 0 ? 'res-negativo' : 'zero') }}">{{ $l['ganho'] === null ? '-' : ($l['ganho'] > 0 ? '+' . $l['ganho'] : $l['ganho']) }}</td>
                            <td class="{{ $l['pontos'] > 0 ? 'forte' : 'zero' }}">{{ $num($l['pontos']) }}</td>
                            <td class="esq">
                                @if($l['largada'] === 1)<span class="res-tag-status pole">Pole</span>@endif
                                @if($l['vr'])<span class="res-tag-status vr">Volta rápida</span>@endif
                                @if($l['abandono'])<span class="res-tag-status ab">Abandono</span>@endif
                                @if($l['estreia'])<span class="res-tag-status est">Estreia</span>@endif
                            </td>
                            <td>
                                @if($posCamp){{ $posCamp }}º
                                    @if($dCamp > 0)<span class="res-delta-camp sobe">▲{{ $dCamp }}</span>@elseif($dCamp < 0)<span class="res-delta-camp desce">▼{{ -$dCamp }}</span>@elseif($dCamp === 0)<span class="res-delta-camp igual">=</span>@endif
                                @else - @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($dados['linhas']) > 15)<button type="button" class="res-expandir" data-tabela="tabelaResultado">Mostrar todos os {{ count($dados['linhas']) }} pilotos</button>@endif
        </div>
    </section>

    {{-- ================= CAMPEONATO ================= --}}
    <section class="res-secao" id="campeonato">
        <h2>{{ $c['sprint'] ? 'Classificação das sprints' : 'Campeonato' }} após a {{ $tipoNome }}</h2>
        <p class="res-sub">Pontos somados nesta {{ $tipoNome }} e quantas posições cada piloto ganhou ou perdeu.</p>
        <div class="res-card res-tabela-wrap">
            <table class="res-tabela {{ count($camp['tabela']) > 15 ? 'recolhida' : '' }}" id="tabelaCampeonato">
                <thead><tr><th>Pos</th><th></th><th class="esq">Piloto</th><th class="esq">Equipe</th><th>Pts</th><th>Nesta {{ $tipoNome }}</th><th>Dif. p/ líder</th></tr></thead>
                <tbody>
                    @foreach($camp['tabela'] as $l)
                        <tr class="{{ $l['pos'] === 1 ? 'campeao' : '' }}">
                            <td class="forte">{{ $l['pos'] }}</td>
                            <td>
                                @if($l['delta'] === null)<span class="res-delta-camp igual">novo</span>
                                @elseif($l['delta'] > 0)<span class="res-delta-camp sobe">▲{{ $l['delta'] }}</span>
                                @elseif($l['delta'] < 0)<span class="res-delta-camp desce">▼{{ -$l['delta'] }}</span>
                                @else<span class="res-delta-camp igual">=</span>@endif
                            </td>
                            <td class="esq"><a class="forte" href="{{ $l['p']['url'] }}">{{ $l['p']['nome'] }}</a></td>
                            <td class="esq res-muted">{{ $l['equipe'] }}</td>
                            <td class="forte">{{ $num($l['pontos']) }}</td>
                            <td class="{{ $l['ganhou'] > 0 ? 'res-positivo' : 'zero' }}">{{ $l['ganhou'] > 0 ? '+' . $num($l['ganhou']) : '0' }}</td>
                            <td class="res-muted">{{ $l['pos'] === 1 ? '-' : ($camp['tabela'][0]['pontos'] == $l['pontos'] ? 'empate' : '-' . $num($camp['tabela'][0]['pontos'] - $l['pontos'])) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($camp['tabela']) > 15)<button type="button" class="res-expandir" data-tabela="tabelaCampeonato">Mostrar todos os {{ count($camp['tabela']) }} pilotos</button>@endif
        </div>
    </section>

    {{-- ================= JEJUNS ================= --}}
    <section class="res-secao" id="jejuns">
        <h2>Seguem sem vencer</h2>
        <p class="res-sub">Pilotos do top 10 desta {{ $tipoNome }} que não venceram: há quantas {{ $tipoNome }}s estão sem vitória (contando todas as temporadas).</p>
        @if(count($dados['jejuns']))
            <div class="res-card res-card-pad">
                <ul class="res-mini-lista">
                    @foreach($dados['jejuns'] as $j)
                        <li>
                            <img class="res-foto" src="{{ $j['p']['img'] }}" alt="">
                            <a class="nome" href="{{ $j['p']['url'] }}">{{ $j['p']['nome'] }}<small>{{ $j['chegada'] }}º hoje · {{ $j['eq']['nome'] }} · {{ $j['vitorias'] }} {{ $j['vitorias'] == 1 ? 'vitória' : 'vitórias' }} na carreira{{ $j['semVencerTemporada'] ? ' · ainda sem vitória na temporada' : '' }}</small></a>
                            <span class="valor">{{ $j['corridas'] }} <small>{{ $j['nuncaVenceu'] ? $tipoNome . 's sem nunca vencer' : $tipoNome . 's sem vencer' }}</small></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="res-card res-vazio">Ninguém do top 10 está em jejum.</div>
        @endif
    </section>

    {{-- ================= ACONTECEU / NÃO ================= --}}
    <section class="res-secao" id="aconteceu">
        <h2>Aconteceu</h2>
        <p class="res-sub">Marcas, primeiras vezes, sequências e curiosidades desta {{ $tipoNome }}.</p>
        <div class="res-fatos">
            @foreach($dados['aconteceu'] as $f)
                <div class="res-fato"><i class="bi {{ $f['icone'] }}"></i><div><b>{{ $f['titulo'] }}</b><p>{{ $f['texto'] }}</p></div></div>
            @endforeach
        </div>
    </section>

    <section class="res-secao" id="nao-aconteceu">
        <h2>Não aconteceu</h2>
        <p class="res-sub">O que ficou faltando e quem passou perto.</p>
        @if(count($dados['naoAconteceu']))
            <div class="res-fatos">
                @foreach($dados['naoAconteceu'] as $f)
                    <div class="res-fato nao"><i class="bi {{ $f['icone'] }}"></i><div><b>{{ $f['titulo'] }}</b><p>{{ $f['texto'] }}</p></div></div>
                @endforeach
            </div>
        @else
            <div class="res-card res-vazio">Nada ficou faltando nesta {{ $tipoNome }}.</div>
        @endif
    </section>

    {{-- ================= PISTA ================= --}}
    @php $ph = $dados['pistaHistorico']; @endphp
    <section class="res-secao" id="pista">
        <h2>{{ $c['pista'] }} na história</h2>
        <p class="res-sub">{{ $ph['edicoes'] ? 'Esta foi a ' . ($ph['edicoes'] + 1) . 'ª ' . $tipoNome . ' disputada aqui.' : 'Primeira ' . $tipoNome . ' disputada nesta pista.' }}</p>
        <div class="res-grid-2">
            <div class="res-card res-card-pad">
                <h3 class="res-card-titulo"><i class="bi bi-trophy-fill"></i> Maiores vencedores da pista</h3>
                <ul class="res-mini-lista">
                    @foreach($ph['maiores'] as $x)
                        <li class="{{ $x['hoje'] ? 'hoje' : '' }}"><img class="res-foto" src="{{ $x['p']['img'] }}" alt=""><a class="nome" href="{{ $x['p']['url'] }}">{{ $x['p']['nome'] }}@if($x['hoje'])<small>venceu hoje</small>@endif</a><span class="valor">{{ $x['vitorias'] }} <small>{{ $x['vitorias'] == 1 ? 'vitória' : 'vitórias' }}</small></span></li>
                    @endforeach
                </ul>
            </div>
            <div class="res-card res-card-pad">
                <h3 class="res-card-titulo"><i class="bi bi-clock-history"></i> Edição anterior</h3>
                @if($ph['anterior'])
                    <ul class="res-mini-lista">
                        <li><img class="res-foto" src="{{ $ph['anterior']['vencedor']['img'] }}" alt=""><span class="nome">{{ $ph['anterior']['vencedor']['nome'] }}<small>vencedor · {{ $ph['anterior']['temporada'] }}</small></span></li>
                        @if($ph['anterior']['pole'])<li><img class="res-foto" src="{{ $ph['anterior']['pole']['img'] }}" alt=""><span class="nome">{{ $ph['anterior']['pole']['nome'] }}<small>pole position</small></span></li>@endif
                    </ul>
                    <a class="res-mais" href="{{ $ph['anterior']['url'] }}" style="display: inline-block; margin-top: 8px; font-weight: 700; color: var(--f1-red);">Ver resumo da edição anterior →</a>
                @else
                    <p class="res-muted" style="margin: 0;">Não há edição anterior desta pista.</p>
                @endif
                @if($c['observacoes'])
                    <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--linha);">
                        <small class="res-muted" style="font-weight: 800; text-transform: uppercase; letter-spacing: 1px; font-size: .68rem;">Observações da {{ $tipoNome }}</small>
                        <div class="res-obs">{{ $c['observacoes'] }}</div>
                    </div>
                @endif
            </div>
        </div>
    </section>
    @endif
</div>

<script>
(function () {
    const seletor = document.getElementById('temporada');
    if (seletor) seletor.addEventListener('change', () => { window.location.href = seletor.value; });
    document.querySelectorAll('.res-expandir').forEach(botao => botao.addEventListener('click', () => {
        const tabela = document.getElementById(botao.dataset.tabela);
        const recolhida = tabela.classList.toggle('recolhida');
        botao.dataset.texto = botao.dataset.texto || botao.textContent;
        botao.textContent = recolhida ? botao.dataset.texto : 'Mostrar só os 15 primeiros';
    }));
})();
</script>
@endsection
