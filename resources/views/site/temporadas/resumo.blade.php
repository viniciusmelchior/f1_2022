{{-- MELHORIA 20261110 (resumo): visão completa da temporada. Dados em App\Services\TemporadaResumoService --}}
@extends('layouts.main')

@section('section')
@php
    $t = $dados['temporada'];
    $num = fn ($v) => $v === null ? '-' : (floor($v) == $v ? number_format($v, 0, ',', '.') : number_format($v, 1, ',', '.'));
    // texto legível sobre a cor da equipe
    $tinta = function ($hex) {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) !== 6) return '#fff';
        [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        return (0.299 * $r + 0.587 * $g + 0.114 * $b) > 160 ? '#15151e' : '#fff';
    };
    $luminancia = function ($hex) {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) !== 6) return 0;
        return 0.299 * hexdec(substr($hex, 0, 2)) + 0.587 * hexdec(substr($hex, 2, 2)) + 0.114 * hexdec(substr($hex, 4, 2));
    };
    $tituloTemporada = $t['nome'] . ($t['referencia'] ? ' · ' . $t['referencia'] : '');
@endphp

@include('site.partials.resumo-estilos')

<div class="res-page">
    {{-- ================= HERO ================= --}}
    <header class="res-hero">
        <div>
            <small class="res-kicker">Resumo da temporada @if($t['ano']) · Ano {{ $t['ano'] }} @endif</small>
            <h1>{{ $t['nome'] }} @if($t['referencia'])<span>{{ $t['referencia'] }}</span>@endif</h1>
            <span class="res-pill {{ $t['finalizada'] ? 'fim' : 'andamento' }}">{{ $t['finalizada'] ? 'Temporada finalizada' : 'Em andamento' }}</span>
        </div>
        <div class="res-hero-acoes">
            <div class="res-navtemp">
                @if($navegacao['anterior'])
                    <a href="{{ route('temporadas.resumo', [$navegacao['anterior']->id]) }}" title="Temporada anterior: {{ $navegacao['anterior']->des_temporada }}"><i class="bi bi-chevron-left"></i></a>
                @else
                    <span class="off"><i class="bi bi-chevron-left"></i></span>
                @endif
                {{-- id "temporada" fica fora do select2 global do layout --}}
                <select id="temporada" aria-label="Escolher temporada">
                    @foreach($lista as $item)
                        <option value="{{ route('temporadas.resumo', [$item->id]) }}" @if($item->id === $temporada->id) selected @endif>{{ $item->des_temporada }}{{ $item->referencia ? ' · ' . $item->referencia : '' }}</option>
                    @endforeach
                </select>
                @if($navegacao['proxima'])
                    <a href="{{ route('temporadas.resumo', [$navegacao['proxima']->id]) }}" title="Próxima temporada: {{ $navegacao['proxima']->des_temporada }}"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="off"><i class="bi bi-chevron-right"></i></span>
                @endif
            </div>
            <div class="res-links">
                <a href="{{ route('temporadas.classificacao', [$temporada->id]) }}"><i class="bi bi-trophy"></i> Classificação</a>
                <a href="{{ route('temporadas.resultados', [$temporada->id]) }}"><i class="bi bi-list-ol"></i> Resultados</a>
                <a href="{{ route('corridas.index', [$temporada->id]) }}"><i class="bi bi-calendar3"></i> Corridas</a>
                <a href="{{ route('temporadas.index') }}"><i class="bi bi-arrow-left"></i> Temporadas</a>
            </div>
        </div>
    </header>

    @if($dados['vazia'])
        <div class="res-card res-vazio" style="margin-top: 20px;">Nenhuma corrida com resultado nesta temporada ainda.</div>
    @else
    @php
        $tit = $dados['titulos'];
        $ev = $dados['evolucao'];
    @endphp

    {{-- ================= KPIs ================= --}}
    <div class="res-kpis">
        @foreach($dados['kpis'] as $k)
            <div class="res-kpi">
                <span><i class="bi {{ $k['icone'] }}"></i> {{ $k['rotulo'] }}</span>
                <strong>{{ $k['valor'] }}</strong>
                <small>{{ $k['detalhe'] }}</small>
            </div>
        @endforeach
    </div>

    <nav class="res-nav" aria-label="Seções">
        <a href="#noticias">Manchetes</a>
        <a href="#campeonato">Campeonato</a>
        <a href="#evolucao">Evolução</a>
        <a href="#corridas">Corrida a corrida</a>
        <a href="#estatisticas">Estatísticas</a>
        @if(count($dados['duelos']))<a href="#duelos">Duelos</a>@endif
        <a href="#aconteceu">Aconteceu</a>
        <a href="#nao-aconteceu">Não aconteceu</a>
        <a href="#e-se">E se…?</a>
    </nav>

    {{-- ================= MANCHETES ================= --}}
    <section class="res-secao" id="noticias">
        <div class="res-jornal">
            <div class="res-jornal-cab">
                <b>Gazeta do Paddock</b>
                <span>Edição especial · {{ $tituloTemporada }} · {{ $t['finalizada'] ? 'balanço final' : 'até a ' . end($dados['corridas'])['ordem'] . 'ª etapa' }}</span>
            </div>
            <div class="res-noticias">
                @foreach($dados['manchetes'] as $i => $m)
                    <article class="res-noticia {{ $i === 0 ? 'principal' : '' }}" @if(!empty($m['cor'])) style="--cor-noticia: {{ $m['cor'] }}" @endif>
                        @if($i === 0 && !empty($m['foto']))
                            <img class="res-noticia-foto" src="{{ $m['foto'] }}" alt="" loading="lazy">
                        @endif
                        <div>
                            <span class="res-tag">{{ $m['tag'] }}</span>
                            <h3>
                                @if(!empty($m['bandeira']))<img class="res-bandeira" src="{{ $m['bandeira'] }}" alt=""> @endif
                                @if(!empty($m['logo']))<img class="res-logo mini" src="{{ $m['logo'] }}" alt="" style="background: {{ $m['fundo'] }}"> @endif
                                {{ $m['titulo'] }}
                            </h3>
                            <p>{{ $m['texto'] }}</p>
                            @if(!empty($m['link']))<a class="res-mais" href="{{ $m['link'] }}">Ver resultado da corrida →</a>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= CAMPEONATO ================= --}}
    <section class="res-secao" id="campeonato">
        <h2>Campeonato</h2>
        <p class="res-sub">Classificação oficial (só corridas principais; sprints e playoff têm títulos próprios).</p>
        <div class="res-grid-2">
            <div class="res-card res-card-pad">
                <h3 style="font-size: .9rem; font-weight: 800; text-transform: uppercase; margin: 0 0 12px;">Pilotos</h3>
                <div class="res-podio">
                    @foreach([$tit['piloto'], $tit['vice'], $tit['terceiro']] as $i => $p)
                        @if($p)
                            <a class="res-degrau p{{ $i + 1 }}" href="{{ $p['url'] }}" style="color: inherit;">
                                <span class="pos">{{ $i + 1 }}º</span><br>
                                <img class="res-foto" src="{{ $p['img'] }}" alt="">
                                <b title="{{ $p['nome'] }}">{{ $p['nome'] }}</b>
                                <small>@if($p['equipeInfo'])<img class="res-logo mini" src="{{ $p['equipeInfo']['img'] }}" alt="" style="background: {{ $p['equipeInfo']['fundo'] }}"> {{ $p['equipeInfo']['nome'] }}@endif</small>
                                <strong>{{ $num($p['pontos']) }}</strong>
                                <small>{{ $p['vitorias'] }} vit · {{ $p['podios'] }} pód</small>
                            </a>
                        @endif
                    @endforeach
                </div>
                @if($tit['sprint'] || $tit['playoff'])
                    <div class="res-extras">
                        @if($tit['sprint'])
                            <div class="res-extra"><i class="bi bi-lightning-charge-fill" style="color: #d39e00;"></i><div><small>{{ $t['finalizada'] ? 'Campeão' : 'Líder' }} das sprints · {{ $tit['qtdSprints'] }} {{ $tit['qtdSprints'] == 1 ? 'sprint' : 'sprints' }}</small><b>{{ $tit['sprint']['nome'] }}</b> <span class="res-muted">{{ $num($tit['sprint']['pontos']) }} pts</span></div></div>
                        @endif
                        @if($tit['playoff'])
                            <a class="res-extra" href="{{ $tit['playoff']['link'] }}" style="color: inherit;"><i class="bi bi-diagram-3" style="color: #00a896;"></i><div><small>Playoff · {{ $tit['playoff']['fase'] }}</small>
                                @if($tit['playoff']['campeao'])<b>{{ $tit['playoff']['campeao']['nome'] }}</b> @if($tit['playoff']['vice'])<span class="res-muted">venceu {{ $tit['playoff']['vice']['nome'] }} na final</span>@endif
                                @else<b>Em disputa</b> <span class="res-muted">ver chaveamento</span>@endif
                            </div></a>
                        @endif
                    </div>
                @endif
            </div>
            <div class="res-card res-card-pad">
                <h3 style="font-size: .9rem; font-weight: 800; text-transform: uppercase; margin: 0 0 12px;">Construtores</h3>
                <div class="res-podio">
                    @foreach([$tit['equipe'], $tit['equipeVice'], $tit['equipeTerceira']] as $i => $e)
                        @if($e)
                            <a class="res-degrau p{{ $i + 1 }}" href="{{ $e['url'] }}" style="color: inherit;">
                                <span class="pos">{{ $i + 1 }}º</span><br>
                                <img class="res-logo-grande" src="{{ $e['img'] }}" alt="" style="background: {{ $e['fundo'] }}">
                                <b title="{{ $e['nome'] }}">{{ $e['nome'] }}</b>
                                <small>{{ implode(' · ', array_slice($e['pilotos'], 0, 3)) }}</small>
                                <strong>{{ $num($e['pontos']) }}</strong>
                                <small>{{ $e['vitorias'] }} vit · {{ $e['dobradinhas'] }} dobr.</small>
                            </a>
                        @endif
                    @endforeach
                </div>
                @if($t['observacoes'])
                    <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--linha);">
                        <small class="res-muted" style="font-weight: 800; text-transform: uppercase; letter-spacing: 1px; font-size: .68rem;">Observações da temporada</small>
                        <div class="res-obs">{{ $t['observacoes'] }}</div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ================= EVOLUÇÃO ================= --}}
    <section class="res-secao" id="evolucao">
        <h2>Evolução do campeonato</h2>
        <p class="res-sub">Pontos acumulados dos 6 primeiros colocados, etapa a etapa, e o líder após cada corrida.</p>
        <div class="res-card res-card-pad">
            <div class="res-grafico"><canvas id="graficoEvolucao" aria-label="Pontos acumulados por etapa dos 6 primeiros" role="img"></canvas></div>
            <div class="res-lideres" aria-label="Líder após cada etapa">
                @foreach($ev['lideres'] as $l)
                    <span class="res-lider" style="background: {{ $l['cor'] }}; color: {{ $tinta($l['cor']) }};" title="Após a {{ $l['etapa'] }}ª etapa ({{ $l['evento'] }}): {{ $l['piloto']['nome'] }} com {{ $num($l['pontos']) }} pts (+{{ $num($l['vantagem']) }})">
                        <small>{{ $l['etapa'] }}ª</small>{{ $l['piloto']['sobrenome'] }}
                    </span>
                @endforeach
            </div>
            <div class="res-legenda-lider">
                Faixa colorida: líder do campeonato após cada etapa (cor da equipe).
                {{ $ev['qtdLideres'] }} {{ $ev['qtdLideres'] == 1 ? 'líder' : 'líderes diferentes' }} · {{ $ev['trocas'] }} {{ $ev['trocas'] == 1 ? 'troca' : 'trocas' }} de liderança.
                @if($ev['garantido'] && $ev['garantido']['restantes'] > 0) Título garantido com {{ $ev['garantido']['restantes'] }} {{ $ev['garantido']['restantes'] == 1 ? 'corrida' : 'corridas' }} de antecedência. @endif
            </div>
        </div>
    </section>

    {{-- ================= CORRIDA A CORRIDA ================= --}}
    <section class="res-secao" id="corridas">
        <h2>Corrida a corrida</h2>
        <p class="res-sub">Pole, pódio, volta mais rápida, abandonos e quem saiu de cada etapa liderando o campeonato.</p>
        <div class="res-card res-tabela-wrap">
            <table class="res-tabela">
                <thead>
                    <tr>
                        <th>#</th><th class="esq">Grande Prêmio</th><th>Clima</th><th class="esq">Pole</th><th class="esq">Vencedor</th><th class="esq">2º</th><th class="esq">3º</th><th class="esq">Volta rápida</th><th>Aband.</th><th class="esq">Líder após</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados['corridas'] as $c)
                        <tr>
                            <td class="forte">{{ $c['ordem'] }}</td>
                            <td class="esq"><a href="{{ $c['url'] }}">@if($c['bandeira'])<img class="res-bandeira" src="{{ $c['bandeira'] }}" alt=""> @endif<b>{{ $c['evento'] }}</b></a> <span class="res-muted">{{ $c['pista'] }}</span></td>
                            <td class="res-muted">{{ $c['clima'] ?? '-' }}</td>
                            @foreach([$c['pole'], $c['podio'][0], $c['podio'][1], $c['podio'][2], $c['vrPiloto']] as $k => $r)
                                <td class="esq">
                                    @if($r)
                                        <span class="res-piloto-cel"><img class="res-logo mini" src="{{ $r['equipe']['img'] }}" alt="" style="background: {{ $r['equipe']['fundo'] }}"><a href="{{ $r['piloto']['url'] }}" class="{{ $k === 1 ? 'forte' : '' }}">{{ $r['piloto']['sobrenome'] }}</a>
                                        @if($k === 1 && $r['largada'] > 1)<span class="res-largou">(P{{ $r['largada'] }})</span>@endif</span>
                                    @else
                                        <span class="zero">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="{{ $c['abandonos'] ? '' : 'zero' }}">{{ $c['abandonos'] }}</td>
                            <td class="esq">{{ $c['lider']['piloto']['sobrenome'] }} <span class="res-muted">{{ $num($c['lider']['pontos']) }}</span>@if($c['trocaLider'])<span class="res-novo-lider">NOVO</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- ================= ESTATÍSTICAS ================= --}}
    <section class="res-secao" id="estatisticas">
        <h2>Estatísticas</h2>
        <p class="res-sub">Clique no cabeçalho para ordenar. Grid e chegada médios consideram as corridas disputadas; +/- = posições ganhas da largada à chegada (sem abandonos).</p>
        <div class="res-card res-tabela-wrap" style="margin-bottom: 16px;">
            <table class="res-tabela ordenavel {{ count($dados['pilotos']) > 15 ? 'recolhida' : '' }}" id="tabelaPilotos">
                <thead>
                    <tr>
                        <th class="ordenavel">Pos</th><th class="esq ordenavel">Piloto</th><th class="ordenavel">Pts</th><th class="ordenavel">Corr.</th><th class="ordenavel">Vit.</th><th class="ordenavel">Poles</th><th class="ordenavel">Pódios</th><th class="ordenavel">Top 10</th><th class="ordenavel">V. ráp.</th><th class="ordenavel">Aband.</th><th class="ordenavel">Grid méd.</th><th class="ordenavel">Cheg. méd.</th><th class="ordenavel">+/-</th><th class="ordenavel">Melhor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados['pilotos'] as $p)
                        <tr class="{{ $p['pos'] === 1 ? 'campeao' : '' }}">
                            <td class="forte" data-valor="{{ $p['pos'] }}">{{ $p['pos'] }}</td>
                            <td class="esq" data-valor="{{ $p['nome'] }}"><span class="res-piloto-cel">@if($p['equipeInfo'])<img class="res-logo mini" src="{{ $p['equipeInfo']['img'] }}" alt="" title="{{ $p['equipeInfo']['nome'] }}" style="background: {{ $p['equipeInfo']['fundo'] }}">@endif<a href="{{ $p['url'] }}" class="forte">{{ $p['nome'] }}</a></span></td>
                            <td class="forte" data-valor="{{ $p['pontos'] }}">{{ $num($p['pontos']) }}</td>
                            <td data-valor="{{ $p['corridas'] }}">{{ $p['corridas'] }}</td>
                            @foreach(['vitorias', 'poles', 'podios', 'top10', 'vr', 'abandonos'] as $campo)
                                <td data-valor="{{ $p[$campo] }}" class="{{ $p[$campo] ? '' : 'zero' }}">{{ $p[$campo] }}</td>
                            @endforeach
                            <td data-valor="{{ $p['mediaLargada'] ?? 99 }}">{{ $p['mediaLargada'] !== null ? number_format($p['mediaLargada'], 1, ',', '') : '-' }}</td>
                            <td data-valor="{{ $p['mediaChegada'] ?? 99 }}">{{ $p['mediaChegada'] !== null ? number_format($p['mediaChegada'], 1, ',', '') : '-' }}</td>
                            <td data-valor="{{ $p['ganhos'] }}" class="{{ $p['ganhos'] > 0 ? 'res-positivo' : ($p['ganhos'] < 0 ? 'res-negativo' : 'zero') }}">{{ $p['ganhos'] > 0 ? '+' : '' }}{{ $p['ganhos'] }}</td>
                            <td data-valor="{{ $p['melhor'] ?? 99 }}">{{ $p['melhor'] ? $p['melhor'] . 'º' : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($dados['pilotos']) > 15)<button type="button" class="res-expandir" data-tabela="tabelaPilotos">Mostrar todos os {{ count($dados['pilotos']) }} pilotos</button>@endif
        </div>
        <div class="res-card res-tabela-wrap">
            <table class="res-tabela ordenavel {{ count($dados['equipes']) > 15 ? 'recolhida' : '' }}" id="tabelaEquipes">
                <thead>
                    <tr><th class="ordenavel">Pos</th><th class="esq ordenavel">Equipe</th><th class="ordenavel">Pts</th><th class="ordenavel">Vit.</th><th class="ordenavel">Poles</th><th class="ordenavel">Pódios</th><th class="ordenavel">Dobradinhas</th><th class="ordenavel">Aband.</th><th class="esq">Pilotos</th></tr>
                </thead>
                <tbody>
                    @foreach($dados['equipes'] as $e)
                        <tr class="{{ $e['pos'] === 1 ? 'campeao' : '' }}">
                            <td class="forte" data-valor="{{ $e['pos'] }}">{{ $e['pos'] }}</td>
                            <td class="esq" data-valor="{{ $e['nome'] }}"><span class="res-piloto-cel"><img class="res-logo mini" src="{{ $e['img'] }}" alt="" style="background: {{ $e['fundo'] }}"><a href="{{ $e['url'] }}" class="forte">{{ $e['nome'] }}</a></span></td>
                            <td class="forte" data-valor="{{ $e['pontos'] }}">{{ $num($e['pontos']) }}</td>
                            @foreach(['vitorias', 'poles', 'podios', 'dobradinhas', 'abandonos'] as $campo)
                                <td data-valor="{{ $e[$campo] }}" class="{{ $e[$campo] ? '' : 'zero' }}">{{ $e[$campo] }}</td>
                            @endforeach
                            <td class="esq res-muted">{{ implode(', ', $e['pilotos']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($dados['equipes']) > 15)<button type="button" class="res-expandir" data-tabela="tabelaEquipes">Mostrar todas as {{ count($dados['equipes']) }} equipes</button>@endif
        </div>
    </section>

    {{-- ================= DUELOS ================= --}}
    @if(count($dados['duelos']))
    <section class="res-secao" id="duelos">
        <h2>Duelos de companheiros</h2>
        <p class="res-sub">Só as corridas em que os dois correram pela equipe. Largada = quem largou na frente; corrida = quem chegou na frente.</p>
        <div class="res-grid-3" id="gradeDuelos">
            @foreach($dados['duelos'] as $iDuelo => $d)
                @php
                    // cor da equipe na barra; cores muito claras (ex.: branco) somem no fundo e viram tinta escura
                    $cor = $luminancia($d['equipe']['cor']) > 225 ? '#15151e' : $d['equipe']['cor'];
                    $barra = function ($x, $y) use ($cor) {
                        $tot = max($x + $y, 1);
                        return '<div class="res-barra"><span style="width:' . round($x * 100 / $tot) . '%; background:' . e($cor) . '"></span><span style="width:' . round($y * 100 / $tot) . '%; background:#b9bcc2"></span></div>';
                    };
                @endphp
                <div class="res-card res-card-pad {{ $iDuelo >= 6 ? 'res-duelo-extra' : '' }}">
                    <div class="res-duelo-cab"><img class="res-logo" src="{{ $d['equipe']['img'] }}" alt="" style="background: {{ $d['equipe']['fundo'] }}">{{ $d['equipe']['nome'] }} <span class="res-muted" style="font-weight: 600; text-transform: none;">· {{ $d['juntos'] }} corridas juntos</span></div>
                    <div class="res-duelo-nomes"><a href="{{ $d['a']['url'] }}" style="color: inherit;">{{ $d['a']['sobrenome'] }}</a><a href="{{ $d['b']['url'] }}" style="color: inherit;">{{ $d['b']['sobrenome'] }}</a></div>
                    <div class="res-duelo-linha"><small>Corrida</small><b>{{ $d['corrida'][0] }}</b>{!! $barra($d['corrida'][0], $d['corrida'][1]) !!}<b>{{ $d['corrida'][1] }}</b></div>
                    <div class="res-duelo-linha"><small>Largada</small><b>{{ $d['quali'][0] }}</b>{!! $barra($d['quali'][0], $d['quali'][1]) !!}<b>{{ $d['quali'][1] }}</b></div>
                    <div class="res-duelo-linha"><small>Pontos</small><b>{{ $num($d['pontos'][0]) }}</b>{!! $barra($d['pontos'][0], $d['pontos'][1]) !!}<b>{{ $num($d['pontos'][1]) }}</b></div>
                </div>
            @endforeach
        </div>
        @if(count($dados['duelos']) > 6)
            <button type="button" class="res-expandir res-card" id="expandirDuelos" style="margin-top: 12px; border-radius: 12px;">Mostrar todos os {{ count($dados['duelos']) }} duelos</button>
        @endif
    </section>
    @endif

    {{-- ================= ACONTECEU ================= --}}
    <section class="res-secao" id="aconteceu">
        <h2>Aconteceu</h2>
        <p class="res-sub">Curiosidades, recordes e marcas da temporada.</p>
        <div class="res-fatos">
            @foreach($dados['aconteceu'] as $f)
                <div class="res-fato"><i class="bi {{ $f['icone'] }}"></i><div><b>{{ $f['titulo'] }}</b><p>{{ $f['texto'] }}</p></div></div>
            @endforeach
        </div>
    </section>

    {{-- ================= NÃO ACONTECEU ================= --}}
    <section class="res-secao" id="nao-aconteceu">
        <h2>Não aconteceu</h2>
        <p class="res-sub">O que ficou faltando, quem passou perto e as marcas que ninguém alcançou.</p>
        @if(count($dados['naoAconteceu']))
            <div class="res-fatos">
                @foreach($dados['naoAconteceu'] as $f)
                    <div class="res-fato nao"><i class="bi {{ $f['icone'] }}"></i><div><b>{{ $f['titulo'] }}</b><p>{{ $f['texto'] }}</p></div></div>
                @endforeach
            </div>
        @else
            <div class="res-card res-vazio">Nada ficou faltando nesta temporada.</div>
        @endif
    </section>

    {{-- ================= E SE ================= --}}
    <section class="res-secao" id="e-se">
        <h2>E se…?</h2>
        <p class="res-sub">Os mesmos resultados de chegada, contados de outro jeito, com a classificação completa de cada cenário. As setas comparam com a posição oficial. Nenhum desses cenários altera a classificação.</p>
        <div class="res-grid-3">
            @foreach($dados['cenarios'] as $c)
                <div class="res-card res-cenario">
                    <div class="res-cenario-cab">
                        <div><b>{{ $c['nome'] }}</b><small>{{ $c['regra'] }}</small></div>
                        <span class="res-selo {{ $c['mudou'] ? 'mudou' : 'igual' }}">{{ $c['mudou'] ? 'Outro campeão' : 'Mesmo campeão' }}</span>
                    </div>
                    {{-- MELHORIA 20261110 (e se): todos os pilotos, com rolagem dentro do card --}}
                    <ol class="res-cenario-lista">
                        @foreach($c['lista'] as $x)
                            <li class="{{ $x['pos'] === 1 ? 'lider' : '' }}">
                                <span class="pos">{{ $x['pos'] }}</span>
                                <img class="res-foto" src="{{ $x['piloto']['img'] }}" alt="" loading="lazy">
                                <a class="nome" href="{{ $x['piloto']['url'] }}" title="{{ $x['piloto']['nome'] }}{{ $x['oficial'] ? ' · posição oficial: ' . $x['oficial'] . 'º' : '' }}">{{ $x['piloto']['nome'] }}</a>
                                @if($x['delta'] === null)
                                    <span class="res-delta"></span>
                                @elseif($x['delta'] > 0)
                                    <span class="res-delta sobe" title="Subiria {{ $x['delta'] }} {{ $x['delta'] == 1 ? 'posição' : 'posições' }} (oficial: {{ $x['oficial'] }}º)">▲{{ $x['delta'] }}</span>
                                @elseif($x['delta'] < 0)
                                    <span class="res-delta desce" title="Cairia {{ -$x['delta'] }} {{ $x['delta'] == -1 ? 'posição' : 'posições' }} (oficial: {{ $x['oficial'] }}º)">▼{{ -$x['delta'] }}</span>
                                @else
                                    <span class="res-delta igual" title="Mesma posição da classificação oficial">=</span>
                                @endif
                                <span class="valor">{{ $x['valor'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                    <small class="res-cenario-rodape">{{ count($c['lista']) }} pilotos</small>
                </div>
            @endforeach
        </div>
    </section>
    @endif
</div>

<script>
(function () {
    const seletor = document.getElementById('temporada');
    if (seletor) seletor.addEventListener('change', () => { window.location.href = seletor.value; });

    // tabelas longas: mostra os 15 primeiros e expande sob demanda
    document.querySelectorAll('.res-expandir').forEach(botao => botao.addEventListener('click', () => {
        const tabela = document.getElementById(botao.dataset.tabela);
        const recolhida = tabela.classList.toggle('recolhida');
        botao.dataset.texto = botao.dataset.texto || botao.textContent;
        botao.textContent = recolhida ? botao.dataset.texto : 'Mostrar só os 15 primeiros';
    }));

    const botaoDuelos = document.getElementById('expandirDuelos');
    if (botaoDuelos) botaoDuelos.addEventListener('click', () => {
        const todos = document.getElementById('gradeDuelos').classList.toggle('todos');
        botaoDuelos.dataset.texto = botaoDuelos.dataset.texto || botaoDuelos.textContent;
        botaoDuelos.textContent = todos ? 'Mostrar só os 6 primeiros' : botaoDuelos.dataset.texto;
    });

    // ordenação simples das tabelas de estatísticas (data-valor numérico ou texto)
    document.querySelectorAll('table.res-tabela.ordenavel').forEach(tabela => {
        tabela.querySelectorAll('th.ordenavel').forEach((th, col) => {
            let asc = col <= 1; // posição e nome começam crescentes
            th.addEventListener('click', () => {
                const corpo = tabela.tBodies[0];
                const linhas = [...corpo.rows];
                linhas.sort((a, b) => {
                    const va = a.cells[col].dataset.valor, vb = b.cells[col].dataset.valor;
                    const na = parseFloat(va), nb = parseFloat(vb);
                    const r = (!isNaN(na) && !isNaN(nb)) ? na - nb : String(va).localeCompare(String(vb), 'pt-BR');
                    return asc ? r : -r;
                });
                asc = !asc;
                linhas.forEach(l => corpo.appendChild(l));
            });
        });
    });

    // gráfico de evolução dos pontos (Chart.js 3.9.1 do layout); paleta categórica fixa, 1 eixo
    const dados = @json($dados['vazia'] ? null : $dados['evolucao']['grafico']);
    const canvas = document.getElementById('graficoEvolucao');
    if (!dados || !canvas || typeof Chart === 'undefined') return;
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: dados.rotulos,
            datasets: dados.series.map(s => ({
                label: s.nome, data: s.dados, borderColor: s.cor, backgroundColor: s.cor,
                borderWidth: 2, tension: 0.25, pointRadius: 0, pointHoverRadius: 5, pointHoverBorderWidth: 2, pointHoverBorderColor: '#fcfcfb',
            })),
        },
        options: {
            responsive: true, maintainAspectRatio: false, animation: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'start', labels: { usePointStyle: true, pointStyle: 'line', boxWidth: 18, color: '#0b0b0b', font: { family: 'Titillium Web', weight: '700' } } },
                tooltip: {
                    backgroundColor: '#15151e', titleFont: { family: 'Titillium Web', weight: '700' }, bodyFont: { family: 'Titillium Web' }, padding: 10,
                    itemSort: (a, b) => b.parsed.y - a.parsed.y,
                    callbacks: {
                        title: itens => itens.length ? itens[0].label + ' etapa · ' + dados.eventos[itens[0].dataIndex] : '',
                        label: item => ' ' + item.dataset.label + ': ' + String(item.parsed.y).replace('.', ',') + ' pts',
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, border: { color: '#c3c2b7' }, ticks: { color: '#898781', font: { family: 'Titillium Web' } } },
                y: { beginAtZero: true, grid: { color: '#e1e0d9' }, border: { display: false }, ticks: { color: '#898781', font: { family: 'Titillium Web' } }, title: { display: true, text: 'Pontos acumulados', color: '#52514e' } },
            },
        },
    });
})();
</script>
@endsection
