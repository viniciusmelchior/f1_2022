{{-- 20261105 - Conteúdo da tela de curiosidades. É renderizado uma vez e guardado em cache pelo CuriosidadesController --}}
@php
    $j = $dados['jejuns'];
    $s = $dados['sequencias'];
    $casa = $dados['casa'];
    $cont = $dados['continentes'];
    $ab = $dados['abandonos'];
    $car = $dados['carreira'];
    $cor = $dados['corridas'];
    $temp = $dados['temporadas'];
    $num = fn ($v) => number_format($v, 0, ',', '.');
    $dec = fn ($v) => number_format($v, 1, ',', '.');
    $graficosJs = [
        'sede' => array_map(fn ($c) => ['n' => $c['nome'], 'v' => $c['corridas'], 'd' => $c['vencedores']], $cont['porSede']),
        'origem' => array_map(fn ($c) => ['n' => $c['nome'], 'v' => $c['vitorias'], 'p' => $c['pilotos']], $cont['porOrigem']),
        'abTemp' => array_map(fn ($t) => ['r' => $t['rotulo'], 'n' => $t['nome'], 'm' => $t['media'], 't' => $t['total'], 'c' => $t['corridas']], $ab['porTemporada']),
        'distAb' => $ab['distribuicao'],
        'grid' => $cor['gridVencedor'],
    ];
@endphp

<style>
    .cur-page {
        --f1-red: #e10600; --f1-dark: #15151e; --f1-darker: #11101d; --f1-gray: #38383f;
        --ink-1: #15151e; --ink-2: #52514e; --ink-3: #8a8985; --linha: #ececec; --fundo: #f3f4f6;
        --ouro: #f5c518; --prata: #c9ccd1; --bronze: #d4925b;
        font-family: 'Titillium Web', sans-serif; color: var(--ink-1); background: var(--fundo);
        padding: 20px 24px 80px; min-height: 100vh; max-width: 100%; overflow-wrap: break-word;
    }
    .cur-page a { text-decoration: none; }
    .cur-page .oculto { display: none !important; }

    /* ---------- HERO ---------- */
    .cur-hero { background: radial-gradient(circle at 85% 20%, rgba(225,6,0,.35), transparent 45%), linear-gradient(120deg, var(--f1-darker), var(--f1-dark) 60%, #24242f);
        border-radius: 14px; padding: 28px 30px; color: #fff; border-bottom: 4px solid var(--f1-red); box-shadow: 0 10px 30px rgba(0,0,0,.15);
        display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; flex-wrap: wrap; }
    .cur-hero h1 { font-weight: 900; text-transform: uppercase; letter-spacing: -1px; font-size: 2.2rem; margin: 0 0 4px; line-height: 1; }
    .cur-hero h1 span { color: var(--f1-red); }
    .cur-hero p { margin: 0; color: #c3c2b7; font-size: .95rem; }
    .cur-hero-acoes { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; margin-left: auto; text-align: right; }
    .cur-switch { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; font-size: .85rem; font-weight: 700; text-transform: uppercase; user-select: none; }
    .cur-switch input { appearance: none; width: 40px; height: 22px; border-radius: 22px; background: var(--f1-gray); position: relative; cursor: pointer; transition: .2s; }
    .cur-switch input::after { content: ''; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: .2s; }
    .cur-switch input:checked { background: var(--f1-red); }
    .cur-switch input:checked::after { left: 21px; }
    .cur-hero small { color: #8a8985; font-size: .75rem; }
    .cur-hero small a { color: #c3c2b7; text-decoration: underline; }

    /* ---------- KPIs ---------- */
    .cur-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 20px 0; }
    @media (max-width: 1100px) { .cur-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .cur-kpi { background: #fff; border-radius: 10px; padding: 16px 18px; border-left: 4px solid var(--f1-red); box-shadow: 0 4px 15px rgba(0,0,0,.04); }
    .cur-kpi span { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--ink-2); text-transform: uppercase; font-weight: 700; letter-spacing: .5px; }
    .cur-kpi span i { color: var(--f1-red); }
    .cur-kpi strong { display: block; font-size: 1.9rem; font-weight: 900; line-height: 1.1; margin-top: 4px; }
    .cur-kpi small { color: var(--ink-3); font-size: 12px; }

    /* ---------- NAV ---------- */
    .cur-nav { position: sticky; top: 0; z-index: 50; background: rgba(243,244,246,.92); backdrop-filter: blur(6px);
        padding: 10px 0; margin: 0 0 10px; display: flex; gap: 8px; overflow-x: auto; scrollbar-width: thin; }
    .cur-nav a { flex: 0 0 auto; color: var(--ink-1); background: #fff; border: 1px solid var(--linha); border-radius: 20px; padding: 6px 14px;
        font-size: 13px; font-weight: 700; text-transform: uppercase; transition: .2s; }
    .cur-nav a:hover { border-color: var(--f1-red); color: var(--f1-red); }
    .cur-nav a.ativo { background: var(--f1-darker); color: #fff; border-color: var(--f1-darker); }

    /* ---------- SEÇÕES ---------- */
    .cur-secao { scroll-margin-top: 70px; margin-top: 26px; }
    .cur-secao > h2 { font-size: 1.35rem; font-weight: 900; text-transform: uppercase; display: flex; align-items: center; gap: 10px; margin: 0 0 4px; }
    .cur-secao > h2::before { content: ''; width: 6px; height: 26px; background: var(--f1-red); border-radius: 3px; }
    .cur-secao > p.cur-sub { color: var(--ink-2); margin: 0 0 14px 16px; font-size: .9rem; }
    .cur-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: start; }
    .cur-largo { grid-column: span 2; }
    .cur-total { grid-column: 1 / -1; }
    .cur-dupla { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: start; }
    .cur-temporada-linha { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 10px; }
    .cur-equipe-mini { display: inline-flex; align-items: center; gap: 5px; }
    .cur-equipe-mini .cur-foto.mini { border-radius: 50%; padding: 0; }
    .cur-de { font-size: .8rem; color: var(--ink-3); font-weight: 700; }
    .cur-temporada-box { width: 40px; height: 40px; border-radius: 8px; background: var(--f1-darker); color: #fff !important; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; }
    .cur-temporada-box b { font-size: .85rem; font-weight: 900; }
    .cur-temporada-box small { font-size: 9px; color: #c3c2b7; margin-top: 2px; }
    .cur-temporada-box:hover { background: var(--f1-red); }

    /* ---------- CARD ---------- */
    .cur-card { min-width: 0; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,.04); border: 1px solid #eee; overflow: hidden; display: flex; flex-direction: column; }
    .cur-card-header { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; flex-wrap: wrap; padding: 14px 16px 10px; border-bottom: 1px solid var(--linha); }
    .cur-card-header > div { min-width: 0; flex: 1 1 180px; }
    .cur-card-header h3 { font-size: .95rem; font-weight: 800; text-transform: uppercase; margin: 0; display: flex; gap: 8px; align-items: center; }
    .cur-card-header h3 i { color: var(--f1-red); }
    .cur-card-header p { margin: 3px 0 0; font-size: 12px; color: var(--ink-3); line-height: 1.3; }
    .cur-chips { display: flex; background: var(--fundo); border-radius: 20px; padding: 2px; flex: 0 0 auto; }
    .cur-chips button { border: 0; background: transparent; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 4px 10px; border-radius: 20px; color: var(--ink-2); }
    .cur-chips button.ativo { background: var(--f1-darker); color: #fff; }
    .cur-vazio { padding: 20px; text-align: center; color: var(--ink-3); font-style: italic; margin: 0; }
    .cur-mais { border: 0; border-top: 1px solid var(--linha); background: #fafafa; padding: 9px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--ink-2); }
    .cur-mais:hover { background: var(--f1-darker); color: #fff; }

    /* ---------- LISTA DE RANKING ---------- */
    .cur-list { list-style: none; margin: 0; padding: 4px 0; counter-reset: pos; }
    .cur-item { display: grid; grid-template-columns: 26px 40px minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 8px 16px; counter-increment: pos; }
    .cur-item + .cur-item { border-top: 1px solid #f4f4f4; }
    .cur-item:hover { background: #fafafa; }
    .cur-pos::before { content: counter(pos); font-weight: 900; color: var(--ink-3); font-size: .95rem; }
    .cur-item.lider { background: linear-gradient(90deg, rgba(245,197,24,.14), transparent 70%); }
    .cur-item.lider .cur-pos::before { content: '★'; color: #c99a00; }
    .cur-item.lider .cur-foto, .cur-item.lider .cur-logo { box-shadow: 0 0 0 2px var(--ouro); }
    .cur-foto { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; object-position: top; background: #e5e7eb; }
    .cur-logo { width: 40px; height: 40px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; padding: 5px; flex: 0 0 auto; }
    .cur-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .cur-foto.mini, .cur-logo.mini { width: 24px; height: 24px; padding: 3px; border-radius: 5px; box-shadow: none !important; }
    .cur-info { min-width: 0; }
    .cur-nome-linha { display: flex; align-items: center; gap: 6px; flex-wrap: nowrap; min-width: 0; }
    .cur-nome-linha > .cur-nome { flex: 0 1 auto; }
    .cur-nome-linha > .cur-bandeira, .cur-nome-linha > .cur-tag { flex: 0 0 auto; }
    .cur-nome { display: block; min-width: 0; max-width: 100%; color: var(--ink-1); font-weight: 800; font-size: .95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cur-nome:hover { color: var(--f1-red); }
    .cur-bandeira { width: 20px; height: 14px; object-fit: cover; border-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,.25); }
    .cur-detalhe { display: block; color: var(--ink-2); font-size: 12px; line-height: 1.3; }
    .cur-detalhe a { color: var(--ink-2); }
    .cur-detalhe a:hover { color: var(--f1-red); text-decoration: underline; }
    .cur-extra { color: var(--ink-3); }
    .cur-tag { font-size: 10px; font-weight: 800; text-transform: uppercase; background: var(--f1-red); color: #fff; border-radius: 3px; padding: 1px 6px; }
    .cur-tag-cinza { background: #e5e7eb; color: var(--ink-2); }
    .cur-logos { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 5px; }
    .cur-logos a { display: inline-flex; align-items: center; gap: 2px; font-size: 11px; color: var(--ink-2); }
    .cur-barra { display: block; height: 3px; background: #f1f1f1; border-radius: 3px; margin-top: 5px; overflow: hidden; }
    .cur-barra span { display: block; height: 100%; background: var(--f1-red); border-radius: 3px; }
    .cur-valor { text-align: right; line-height: 1; }
    .cur-valor strong { display: block; font-size: 1.35rem; font-weight: 900; }
    .cur-valor small { font-size: 10px; text-transform: uppercase; color: var(--ink-3); font-weight: 700; }


    /* ---------- LISTAS SEM TABELA (MELHORIA 20261105) ---------- */
    .cur-list.sem-pos .cur-item { grid-template-columns: 40px minmax(0, 1fr) auto; }
    .cur-bandeira-box { width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: var(--fundo); display: flex; align-items: center; justify-content: center; color: var(--ink-3); box-shadow: inset 0 0 0 1px rgba(0,0,0,.06); }
    .cur-bandeira-box img { width: 100%; height: 100%; object-fit: cover; }
    .cur-temporada-box i { font-size: 1.1rem; }
    .cur-rolagem { max-height: 470px; overflow-y: auto; overflow-x: hidden; }
    .cur-chips-linha { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px; }
    .cur-chip { min-width: 0; display: inline-flex; align-items: center; gap: 5px; background: var(--fundo); border-radius: 20px; padding: 2px 9px 2px 3px; font-size: 12px; color: var(--ink-1); font-weight: 700; max-width: 100%; }
    .cur-chip b { color: var(--f1-red); }
    a.cur-chip:hover { background: var(--f1-darker); color: #fff; }
    .cur-chip .cur-bandeira { margin-left: 4px; }
    .cur-cont { display: inline-flex; gap: 4px; align-items: center; background: #f6f6f6; border-radius: 4px; padding: 2px 7px; font-size: 11px; color: var(--ink-1); }
    .cur-cont.zero { color: #b5b5b5; }
    .cur-equipe-mini { color: inherit; }
    .cur-seta { color: #198754; } .cur-seta.desce { color: #dc3545; }

    /* ---------- GRÁFICOS ---------- */
    .cur-grafico { position: relative; height: 290px; padding: 12px 14px; min-width: 0; width: 100%; overflow: hidden; }
    .cur-grafico canvas { display: block; max-width: 100%; }
    .cur-grafico.baixo { height: 240px; }
    .cur-legenda-simples { font-size: 12px; color: var(--ink-3); padding: 0 16px 12px; margin: 0; }

    /* ---------- DESTAQUE GRANDE (número herói dentro de card) ---------- */
    .cur-hero-num { display: flex; align-items: center; gap: 16px; padding: 16px; border-bottom: 1px solid var(--linha); }
    .cur-hero-num strong { font-size: 2.6rem; font-weight: 900; line-height: 1; color: var(--f1-red); }
    .cur-hero-num span { color: var(--ink-2); font-size: 13px; line-height: 1.3; }

    /* chips (uma vitória só) */
    .cur-nuvem { display: flex; flex-wrap: wrap; gap: 8px; padding: 14px 16px; max-height: 360px; overflow-y: auto; }
    .cur-nuvem a { display: inline-flex; align-items: center; gap: 8px; background: var(--fundo); border-radius: 30px; padding: 4px 12px 4px 4px; color: var(--ink-1); font-size: 12px; font-weight: 700; }
    .cur-nuvem a:hover { background: var(--f1-darker); color: #fff; }
    .cur-nuvem a .cur-foto { width: 28px; height: 28px; }
    .cur-nuvem a small { display: block; font-weight: 400; color: var(--ink-3); font-size: 10px; }

    /* trios */
    .cur-trio { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-top: 1px solid #f4f4f4; }
    .cur-trio:first-child { border-top: 0; }
    .cur-trio .fotos { display: flex; }
    .cur-trio .fotos img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; object-position: top; border: 2px solid #fff; margin-left: -10px; background: #e5e7eb; }
    .cur-trio .fotos img:first-child { margin-left: 0; }
    .cur-trio .nomes { flex: 1; font-size: 12px; color: var(--ink-2); line-height: 1.3; }
    .cur-trio .nomes b { color: var(--ink-1); font-size: 13px; }
    .cur-trio .num { font-size: 1.3rem; font-weight: 900; }

    .cur-rodape { margin-top: 30px; text-align: center; font-size: 12px; color: var(--ink-3); }

    /* ---------- RESPONSIVO (fica no fim para vencer as regras base) ---------- */
    @media (max-width: 900px) {
        .cur-largo { grid-column: auto; }
        .cur-page { padding: 12px; }
        .cur-grid, .cur-dupla { grid-template-columns: minmax(0, 1fr); }
        .cur-nav { position: static; flex-wrap: wrap; overflow: visible; background: transparent; backdrop-filter: none; }
    }
    @media (max-width: 480px) {
        .cur-item { grid-template-columns: 16px 32px minmax(0, 1fr) auto; gap: 7px; padding: 8px; }
        .cur-list.sem-pos .cur-item { grid-template-columns: 32px minmax(0, 1fr) auto; }
        .cur-nome { white-space: normal; line-height: 1.15; font-size: .9rem; }
        .cur-nome-linha { align-items: flex-start; }
        .cur-nome-linha > .cur-bandeira { margin-top: 2px; }
        .cur-pos::before { font-size: .8rem; }
        .cur-foto, .cur-logo, .cur-temporada-box, .cur-bandeira-box { width: 32px; height: 32px; }
        .cur-valor strong { font-size: 1.1rem; }
        .cur-hero { padding: 20px; }
        .cur-hero h1 { font-size: 1.6rem; }
        .cur-hero-acoes { align-items: flex-start; text-align: left; margin-left: 0; }
        .cur-kpi strong { font-size: 1.4rem; }
        .cur-kpis { gap: 8px; }
        .cur-kpi { padding: 12px; }
        .cur-kpi span { font-size: 10px; }
        .cur-page { padding: 10px 6px 60px; }
        .cur-card-header { padding: 12px 10px 8px; }
        .cur-hero-num strong { font-size: 2rem; }
    }
    @media (max-width: 480px) { .cur-grafico { height: 240px; padding: 8px 6px; } }
</style>

<div class="cur-page">

    {{-- ===================== HERO ===================== --}}
    <div class="cur-hero">
        <div>
            <h1>Curiosidades <span>&amp;</span> Recordes</h1>
            <p>{{ $num($dados['totalCorridas']) }} Grandes Prêmios analisados · sprints e super corridas não entram nas contas</p>
        </div>
        <div class="cur-hero-acoes">
            @if($meuPiloto)
                <label class="cur-switch" title="Remove {{ $meuPiloto->nome }} {{ $meuPiloto->sobrenome }} dos rankings">
                    <input type="checkbox" id="curOcultarMeu"> Ocultar meu piloto
                </label>
            @endif
            <small>Calculado em {{ $dados['gerado_em'] }} ({{ $dados['tempo_ms'] }} ms) · atualiza sozinho quando os resultados mudam · <a href="{{ route('curiosidades.index', ['recalcular' => 1]) }}">recalcular agora</a></small>
        </div>
    </div>

    {{-- ===================== KPIs ===================== --}}
    <div class="cur-kpis">
        @foreach($dados['kpis'] as $k)
            <div class="cur-kpi">
                <span><i class="bi {{ $k['icone'] }}"></i> {{ $k['rotulo'] }}</span>
                <strong>{{ is_numeric($k['valor']) ? $num($k['valor']) : $k['valor'] }}</strong>
                <small>{{ $k['nota'] }}</small>
            </div>
        @endforeach
    </div>

    {{-- ===================== NAV ===================== --}}
    <nav class="cur-nav" id="curNav">
        <a href="#jejuns">Jejuns</a>
        <a href="#sequencias">Sequências</a>
        <a href="#casa">Em casa</a>
        <a href="#continentes">Continentes</a>
        <a href="#abandonos">Abandonos</a>
        <a href="#carreira">Carreira &amp; Equipes</a>
        <a href="#corridas">Corridas</a>
        <a href="#temporadas">Temporadas</a>
    </nav>

    {{-- ===================== JEJUNS ===================== --}}
    <section class="cur-secao" id="jejuns">
        <h2>Jejuns</h2>
        <p class="cur-sub">Contados em GPs disputados pelo próprio piloto/equipe desde o último resultado.</p>
        <div class="cur-grid">
            @include('site.curiosidades._ranking', ['titulo' => 'Há mais tempo sem vencer', 'icone' => 'bi-hourglass-split', 'itens' => $j['semVencer'], 'unidade' => 'GPs', 'filtroAtivo' => true, 'padraoAtivo' => true, 'descricao' => 'Já venceram, mas a última vitória está longe'])
            @include('site.curiosidades._ranking', ['titulo' => 'Há mais tempo sem pole', 'icone' => 'bi-stopwatch', 'itens' => $j['semPole'], 'unidade' => 'GPs', 'filtroAtivo' => true, 'padraoAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Há mais tempo sem pódio', 'icone' => 'bi-award', 'itens' => $j['semPodio'], 'unidade' => 'GPs', 'filtroAtivo' => true, 'padraoAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Equipes há mais tempo sem vencer', 'icone' => 'bi-car-front-fill', 'itens' => $j['equipesSemVencer'], 'unidade' => 'GPs', 'filtroAtivo' => true, 'padraoAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Maior jejum entre duas vitórias', 'icone' => 'bi-arrow-left-right', 'itens' => $j['maiorJejum'], 'unidade' => 'GPs', 'descricao' => 'Recorde histórico de GPs entre uma vitória e a seguinte'])
            @include('site.curiosidades._ranking', ['titulo' => 'A primeira vitória que mais demorou', 'icone' => 'bi-hourglass-bottom', 'itens' => $j['ateVitoria'], 'unidade' => 'º GP', 'descricao' => 'Em qual GP da carreira veio a primeira vitória'])
            @include('site.curiosidades._ranking', ['titulo' => 'Mais GPs sem nunca vencer', 'icone' => 'bi-emoji-frown', 'itens' => $j['nuncaVenceu'], 'unidade' => 'GPs', 'filtroAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Mais pódios sem nunca vencer', 'icone' => 'bi-bar-chart-steps', 'itens' => $j['podiosSemVitoria'], 'unidade' => 'pódios', 'filtroAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Mais GPs sem nunca fazer pole', 'icone' => 'bi-speedometer', 'itens' => $j['nuncaPole'], 'unidade' => 'GPs', 'filtroAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Mais GPs sem nunca subir ao pódio', 'icone' => 'bi-ladder', 'itens' => $j['nuncaPodio'], 'unidade' => 'GPs', 'filtroAtivo' => true])
        </div>
    </section>

    {{-- ===================== SEQUÊNCIAS ===================== --}}
    <section class="cur-secao" id="sequencias">
        <h2>Sequências</h2>
        <p class="cur-sub">GPs consecutivos disputados pelo piloto. "Em andamento" = a sequência ainda está viva.</p>
        <div class="cur-grid">
            @include('site.curiosidades._ranking', ['titulo' => 'Vitórias consecutivas', 'icone' => 'bi-trophy-fill', 'itens' => $s['vit'], 'unidade' => 'seguidas'])
            @include('site.curiosidades._ranking', ['titulo' => 'Poles consecutivas', 'icone' => 'bi-stopwatch-fill', 'itens' => $s['pole'], 'unidade' => 'seguidas'])
            @include('site.curiosidades._ranking', ['titulo' => 'Pódios consecutivos', 'icone' => 'bi-award-fill', 'itens' => $s['podio'], 'unidade' => 'seguidos'])
            @include('site.curiosidades._ranking', ['titulo' => 'Top 10 consecutivos', 'icone' => 'bi-list-ol', 'itens' => $s['top10'], 'unidade' => 'seguidos'])
            @include('site.curiosidades._ranking', ['titulo' => 'GPs seguidos sem abandonar', 'icone' => 'bi-shield-fill-check', 'itens' => $s['semAband'], 'unidade' => 'GPs'])
            @include('site.curiosidades._ranking', ['titulo' => 'Equipes: vitórias consecutivas', 'icone' => 'bi-lightning-charge-fill', 'itens' => $s['equipes'], 'unidade' => 'GPs', 'descricao' => 'GPs seguidos do calendário vencidos pela mesma equipe'])
        </div>
    </section>

    {{-- ===================== EM CASA ===================== --}}
    <section class="cur-secao" id="casa">
        <h2>Em casa</h2>
        <p class="cur-sub">"Em casa" = GP disputado em uma pista do país do piloto (ou da equipe).</p>
        <div class="cur-grid">
            @include('site.curiosidades._ranking', ['titulo' => 'Mais vitórias em casa', 'icone' => 'bi-house-heart-fill', 'itens' => $casa['vitorias'], 'unidade' => 'vitórias'])
            @include('site.curiosidades._ranking', ['titulo' => 'Mais pódios em casa', 'icone' => 'bi-house-fill', 'itens' => $casa['podios'], 'unidade' => 'pódios'])
            @include('site.curiosidades._ranking', ['titulo' => 'Equipes que mais venceram em casa', 'icone' => 'bi-building', 'itens' => $casa['equipes'], 'unidade' => 'vitórias'])
            @include('site.curiosidades._ranking', ['titulo' => 'Fator casa', 'icone' => 'bi-graph-up-arrow', 'itens' => $casa['fator'], 'unidade' => 'posições', 'descricao' => 'Quanto a média de chegada melhora em casa (mín. 3 GPs em casa, sem contar abandonos)'])
            @include('site.curiosidades._ranking', ['titulo' => 'Sentem a pressão da torcida', 'icone' => 'bi-graph-down-arrow', 'itens' => $casa['pressao'], 'unidade' => 'posições', 'descricao' => 'Quanto a média de chegada piora em casa'])
            @include('site.curiosidades._ranking', ['titulo' => 'Nunca correram em casa', 'icone' => 'bi-house-door-fill', 'itens' => $casa['nunca'], 'unidade' => 'GPs', 'filtroAtivo' => true, 'descricao' => $casa['nuncaTotal'].' pilotos nunca disputaram um GP no próprio país'])

            <article class="cur-card">
                <header class="cur-card-header"><div><h3><i class="bi bi-geo-alt-fill"></i> Países-sede e a torcida local</h3><p>GPs recebidos por país e quantos foram vencidos por um piloto da casa</p></div></header>
                <ol class="cur-list" data-cur-lista data-limite="10">
                    @foreach($casa['paises'] as $p)
                        <li class="cur-item {{ $loop->index >= 10 ? 'oculto' : '' }}" data-item data-ativo="1" data-piloto="">
                            <span class="cur-pos"></span>
                            <span class="cur-bandeira-box">@if($p['pais']['bandeira'])<img src="{{ $p['pais']['bandeira'] }}" alt="" loading="lazy">@else<i class="bi bi-flag-fill"></i>@endif</span>
                            <div class="cur-info">
                                <div class="cur-nome-linha"><span class="cur-nome">{{ $p['pais']['nome'] }}</span></div>
                                <small class="cur-detalhe">
                                    @if(count($p['herois']))
                                        <span data-cur-primeiro>
                                            @foreach($p['herois'] as $h)
                                                <a class="cur-equipe-mini {{ $loop->first ? '' : 'oculto' }}" data-item data-piloto="{{ $h['id'] }}" href="{{ $h['url'] }}">@include('site.curiosidades._avatar', ['q' => $h, 'classe' => 'mini']) Herói local: {{ $h['nome'] }} ({{ $h['vitorias'] }})</a>
                                            @endforeach
                                        </span>
                                    @else
                                        Nenhum piloto da casa venceu aqui
                                    @endif
                                </small>
                                <span class="cur-barra" aria-hidden="true"><span style="width: {{ max(3, $p['pct']) }}%"></span></span>
                            </div>
                            <div class="cur-valor">
                                <strong>{{ $p['locais'] }}<span class="cur-de">/{{ $p['corridas'] }}</span></strong>
                                <small>vitórias locais<br>{{ $dec($p['pct']) }}%</small>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <p class="cur-vazio" hidden>Nenhum registro.</p>
                <button type="button" class="cur-mais">Ver mais</button>
            </article>
        </div>
    </section>

    {{-- ===================== CONTINENTES ===================== --}}
    <section class="cur-secao" id="continentes">
        <h2>Continentes</h2>
        <p class="cur-sub">Cada GP tem um vencedor: vitórias por continente da pista e pelo continente de origem dos pilotos.</p>
        <div class="cur-grid">
            <article class="cur-card">
                <header class="cur-card-header"><div><h3><i class="bi bi-globe2"></i> Vitórias por continente (sede)</h3><p>GPs vencidos em cada continente</p></div></header>
                <div class="cur-grafico"><canvas id="grafContinentes" aria-label="Vitórias por continente da pista"></canvas></div>
            </article>
            <article class="cur-card">
                <header class="cur-card-header"><div><h3><i class="bi bi-people-fill"></i> Vitórias por continente de origem</h3><p>Somando as vitórias dos pilotos de cada continente</p></div></header>
                <div class="cur-grafico"><canvas id="grafOrigem" aria-label="Vitórias por continente de origem dos pilotos"></canvas></div>
            </article>

            <article class="cur-card">
                <header class="cur-card-header"><div><h3><i class="bi bi-trophy"></i> Os reis de cada continente</h3><p>Quem mais venceu em cada continente</p></div></header>
                <ol class="cur-list sem-pos">
                    @foreach($cont['porSede'] as $c)
                        <li class="cur-item">
                            <span class="cur-temporada-box"><i class="bi bi-globe2"></i></span>
                            <div class="cur-info">
                                <div class="cur-nome-linha"><span class="cur-nome">{{ $c['nome'] }}</span></div>
                                <small class="cur-detalhe">{{ $c['vencedores'] }} vencedores diferentes</small>
                                <div class="cur-chips-linha">
                                    <span data-cur-primeiro>
                                        @foreach($c['topPilotos'] as $tp)
                                            <a class="cur-chip {{ $loop->first ? '' : 'oculto' }}" data-item data-piloto="{{ $tp['id'] }}" href="{{ $tp['url'] }}" title="Piloto com mais vitórias">@include('site.curiosidades._avatar', ['q' => $tp, 'classe' => 'mini']) {{ $tp['nome'] }} <b>{{ $tp['vitorias'] }}</b></a>
                                        @endforeach
                                    </span>
                                    @if($c['topEquipe'])<a class="cur-chip" href="{{ $c['topEquipe']['url'] }}" title="Equipe com mais vitórias">@include('site.curiosidades._avatar', ['q' => $c['topEquipe'], 'classe' => 'mini']) {{ $c['topEquipe']['nome'] }} <b>{{ $c['topEquipe']['vitorias'] }}</b></a>@endif
                                    @if($c['topPais'])<span class="cur-chip" title="País com mais vitórias">@if($c['topPais']['bandeira'])<img class="cur-bandeira" src="{{ $c['topPais']['bandeira'] }}" alt="" loading="lazy">@endif {{ $c['topPais']['nome'] }} <b>{{ $c['topPais']['vitorias'] }}</b></span>@endif
                                </div>
                            </div>
                            <div class="cur-valor"><strong>{{ $c['corridas'] }}</strong><small>GPs</small></div>
                        </li>
                    @endforeach
                </ol>
            </article>

            @include('site.curiosidades._ranking', ['titulo' => 'Venceram em mais continentes', 'icone' => 'bi-airplane-fill', 'itens' => $cont['globais'], 'unidade' => 'continentes'])

            <article class="cur-card cur-largo">
                <header class="cur-card-header"><div><h3><i class="bi bi-grid-3x3-gap-fill"></i> Maiores vencedores × continentes</h3><p>Vitórias de cada um dos 15 maiores vencedores em cada continente (quanto mais forte a cor, mais vitórias)</p></div></header>
                <ol class="cur-list">
                    @foreach($cont['matriz'] as $linha)
                        <li class="cur-item" data-cur-ocultavel data-piloto="{{ $linha['quem']['id'] }}">
                            <span class="cur-pos"></span>
                            @include('site.curiosidades._avatar', ['q' => $linha['quem']])
                            <div class="cur-info">
                                <div class="cur-nome-linha">
                                    @if(!empty($linha['quem']['bandeira']))<img class="cur-bandeira" src="{{ $linha['quem']['bandeira'] }}" alt="" loading="lazy">@endif
                                    <a href="{{ $linha['quem']['url'] }}" class="cur-nome">{{ $linha['quem']['nome'] }}</a>
                                </div>
                                <div class="cur-chips-linha">
                                    @foreach($linha['celulas'] as $k => $n)
                                        @php $alfa = $n > 0 ? round(0.12 + 0.78 * $n / max(1, $cont['maxCelula']), 2) : 0; @endphp
                                        <span class="cur-cont {{ $n ? '' : 'zero' }}" style="{{ $n ? 'background: rgba(225,6,0,'.$alfa.');'.($alfa > 0.5 ? 'color:#fff;' : '') : '' }}">{{ $cont['colunas'][$k]['nome'] }} <b>{{ $n }}</b></span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="cur-valor"><strong>{{ $linha['total'] }}</strong><small>vitórias</small></div>
                        </li>
                    @endforeach
                </ol>
            </article>
        </div>
    </section>

    {{-- ===================== ABANDONOS ===================== --}}
    <section class="cur-secao" id="abandonos">
        <h2>Abandonos</h2>
        <p class="cur-sub">Corridas caóticas, corridas limpas e quem mais (e menos) quebra.</p>
        <div class="cur-grid">
            <article class="cur-card">
                <header class="cur-card-header"><div><h3><i class="bi bi-fire"></i> Recorde de abandonos em um GP</h3><p>As corridas com mais carros fora</p></div></header>
                <ol class="cur-list" data-cur-lista data-limite="10">
                    @foreach($ab['recordes'] as $r)
                        <li class="cur-item {{ $loop->index >= 10 ? 'oculto' : '' }}" data-item data-ativo="1" data-piloto="">
                            <span class="cur-pos"></span>
                            <span class="cur-bandeira-box">@if($r['corrida']['bandeira'])<img src="{{ $r['corrida']['bandeira'] }}" alt="" loading="lazy">@else<i class="bi bi-flag-fill"></i>@endif</span>
                            <div class="cur-info">
                                <div class="cur-nome-linha"><a href="{{ $r['corrida']['url'] }}" class="cur-nome">{{ $r['corrida']['evento'] }}</a></div>
                                <small class="cur-detalhe">{{ $r['corrida']['pista'] }} · {{ $r['corrida']['temporada'] }}</small>
                                @if($r['vencedor'])<small class="cur-detalhe"><a class="cur-equipe-mini" href="{{ $r['vencedor']['url'] }}">@include('site.curiosidades._avatar', ['q' => $r['vencedor'], 'classe' => 'mini']) Vencedor: {{ $r['vencedor']['nome'] }}</a></small>@endif
                                <span class="cur-barra" aria-hidden="true"><span style="width: {{ $r['pct'] }}%"></span></span>
                            </div>
                            <div class="cur-valor">
                                <strong>{{ $r['valor'] }}<span class="cur-de">/{{ $r['carros'] }}</span></strong>
                                <small>{{ $dec($r['pct']) }}% do grid</small>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <p class="cur-vazio" hidden>Nenhum registro.</p>
                <button type="button" class="cur-mais">Ver mais</button>
            </article>

            <article class="cur-card">
                <header class="cur-card-header"><div><h3><i class="bi bi-shield-check"></i> GPs sem nenhum abandono</h3><p>Todos os carros receberam a bandeirada (mais recentes primeiro)</p></div></header>
                <div class="cur-hero-num"><strong>{{ count($ab['limpas']) }}</strong><span>corridas sem abandonos<br>{{ $dec($ab['limpasPct']) }}% de todos os GPs</span></div>
                <div class="cur-rolagem">
                    <ol class="cur-list sem-pos">
                        @foreach($ab['limpas'] as $l)
                            <li class="cur-item">
                                <span class="cur-bandeira-box">@if($l['corrida']['bandeira'])<img src="{{ $l['corrida']['bandeira'] }}" alt="" loading="lazy">@else<i class="bi bi-flag-fill"></i>@endif</span>
                                <div class="cur-info">
                                    <div class="cur-nome-linha"><a href="{{ $l['corrida']['url'] }}" class="cur-nome">{{ $l['corrida']['evento'] }}</a></div>
                                    <small class="cur-detalhe">{{ $l['corrida']['temporada'] }}</small>
                                    @if($l['vencedor'])<small class="cur-detalhe"><a class="cur-equipe-mini" href="{{ $l['vencedor']['url'] }}">@include('site.curiosidades._avatar', ['q' => $l['vencedor'], 'classe' => 'mini']) {{ $l['vencedor']['nome'] }}</a></small>@endif
                                </div>
                                <div class="cur-valor"><strong>{{ $l['carros'] }}</strong><small>carros</small></div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </article>

            <article class="cur-card cur-largo">
                <header class="cur-card-header"><div><h3><i class="bi bi-graph-up"></i> Média de abandonos por GP em cada temporada</h3></div></header>
                <div class="cur-grafico"><canvas id="grafAbandTemporada" aria-label="Média de abandonos por GP em cada temporada"></canvas></div>
            </article>

            <article class="cur-card">
                <header class="cur-card-header"><div><h3><i class="bi bi-bar-chart-fill"></i> Quantos abandonos um GP costuma ter</h3><p>Número de GPs por quantidade de abandonos</p></div></header>
                <div class="cur-grafico"><canvas id="grafDistAband" aria-label="Distribuição de abandonos por GP"></canvas></div>
            </article>

            @include('site.curiosidades._ranking', ['titulo' => 'Mais abandonos na carreira', 'icone' => 'bi-wrench-adjustable', 'itens' => $ab['pilotosTotal'], 'unidade' => 'abandonos', 'filtroAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Maior taxa de abandono', 'icone' => 'bi-exclamation-triangle-fill', 'itens' => $ab['pilotosTaxa'], 'unidade' => 'dos GPs', 'filtroAtivo' => true, 'descricao' => 'Mínimo de 20 GPs'])
            @include('site.curiosidades._ranking', ['titulo' => 'Os mais confiáveis', 'icone' => 'bi-check2-circle', 'itens' => $ab['confiaveis'], 'unidade' => 'terminados', 'filtroAtivo' => true, 'descricao' => 'Percentual de GPs completados (mínimo de 20 GPs)'])

            @foreach([['Pistas mais destruidoras', 'bi-cone-striped', $ab['pistasPerigosas']], ['Pistas mais tranquilas', 'bi-flower1', $ab['pistasSeguras']]] as [$tituloPista, $iconePista, $listaPistas])
                @php $maxMedia = collect($ab['pistasPerigosas'])->max('media') ?: 1; @endphp
                <article class="cur-card">
                    <header class="cur-card-header"><div><h3><i class="bi {{ $iconePista }}"></i> {{ $tituloPista }}</h3><p>Média de abandonos por GP (mínimo de 3 GPs na pista)</p></div></header>
                    <ol class="cur-list" data-cur-lista data-limite="8">
                        @foreach($listaPistas as $p)
                            <li class="cur-item {{ $loop->index >= 8 ? 'oculto' : '' }}" data-item data-ativo="1" data-piloto="">
                                <span class="cur-pos"></span>
                                <span class="cur-bandeira-box">@if($p['bandeira'])<img src="{{ $p['bandeira'] }}" alt="" loading="lazy">@else<i class="bi bi-flag-fill"></i>@endif</span>
                                <div class="cur-info">
                                    <div class="cur-nome-linha"><a href="{{ $p['url'] }}" class="cur-nome">{{ $p['pista'] }}</a></div>
                                    <small class="cur-detalhe">{{ $p['total'] }} abandonos em {{ $p['corridas'] }} GPs</small>
                                    <span class="cur-barra" aria-hidden="true"><span style="width: {{ max(3, round($p['media'] / $maxMedia * 100)) }}%"></span></span>
                                </div>
                                <div class="cur-valor"><strong>{{ $dec($p['media']) }}</strong><small>por GP</small></div>
                            </li>
                        @endforeach
                    </ol>
                    <p class="cur-vazio" hidden>Nenhum registro.</p>
                    @if(count($listaPistas) > 8)<button type="button" class="cur-mais">Ver mais</button>@endif
                </article>
            @endforeach
        </div>
    </section>

    {{-- ===================== CARREIRA & EQUIPES ===================== --}}
    <section class="cur-secao" id="carreira">
        <h2>Carreira &amp; Equipes</h2>
        <p class="cur-sub">Andarilhos do grid, longevidade e as equipes que mais dominaram.</p>
        <div class="cur-grid">
            @include('site.curiosidades._ranking', ['titulo' => 'Correram por mais equipes', 'icone' => 'bi-shuffle', 'itens' => $car['maisEquipes'], 'unidade' => 'equipes', 'filtroAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Venceram por mais equipes', 'icone' => 'bi-trophy', 'itens' => $car['venceuPor'], 'unidade' => 'equipes'])
            @include('site.curiosidades._ranking', ['titulo' => 'Equipes com mais pilotos diferentes', 'icone' => 'bi-people', 'itens' => $car['equipesPilotos'], 'unidade' => 'pilotos', 'filtroAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Mais temporadas disputadas', 'icone' => 'bi-calendar-range-fill', 'itens' => $car['temporadas'], 'unidade' => 'temporadas', 'filtroAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Mais GPs disputados', 'icone' => 'bi-flag', 'itens' => $car['corridas'], 'unidade' => 'GPs', 'filtroAtivo' => true])
            @include('site.curiosidades._ranking', ['titulo' => 'Hat-tricks', 'icone' => 'bi-stars', 'itens' => $car['hatTricks'], 'unidade' => 'hat-tricks', 'descricao' => 'Pole position + vitória + volta mais rápida no mesmo GP'])
            @include('site.curiosidades._ranking', ['titulo' => 'Dobradinhas', 'icone' => 'bi-1-circle-fill', 'itens' => $car['dobradinhas'], 'unidade' => '1-2', 'descricao' => 'GPs com 1º e 2º lugar da mesma equipe'])

            <article class="cur-card cur-largo">
                <header class="cur-card-header"><div><h3><i class="bi bi-1-square-fill"></i> Venceram uma única vez</h3><p>{{ count($car['umaVitoria']) }} pilotos têm exatamente uma vitória — ordenados por GPs disputados</p></div></header>
                <div class="cur-nuvem">
                    @foreach($car['umaVitoria'] as $u)
                        <a href="{{ $u['corrida']['url'] }}" data-cur-ocultavel data-piloto="{{ $u['id'] }}" title="Ver o resultado do {{ $u['corrida']['evento'] }}">
                            @include('site.curiosidades._avatar', ['q' => $u])
                            <span>{{ $u['nome'] }}<small>{{ $u['corrida']['evento'] }} · {{ $u['corrida']['temporada'] }} · {{ $u['corridas'] }} GPs</small></span>
                        </a>
                    @endforeach
                </div>
            </article>
        </div>
    </section>

    {{-- ===================== CORRIDAS ===================== --}}
    <section class="cur-secao" id="corridas">
        <h2>Corridas</h2>
        <p class="cur-sub">Largadas, escaladas e os resultados mais improváveis.</p>
        <div class="cur-grid">
            <article class="cur-card cur-total">
                <header class="cur-card-header"><div><h3><i class="bi bi-signpost-split-fill"></i> De onde largam os vencedores</h3><p>Vitórias por posição de largada</p></div></header>
                <div class="cur-grafico baixo"><canvas id="grafGrid" aria-label="Vitórias por posição de largada"></canvas></div>
            </article>

            @foreach([
                ['Vitórias largando mais atrás', 'bi-chevron-double-up', $cor['vitoriasDeTras'], 'largou', 'Venceram saindo do fundo do grid'],
                ['Maiores escaladas', 'bi-arrow-up-right-circle-fill', $cor['escaladas'], 'ganhou', 'Posições ganhas entre largada e chegada (sem abandonos)'],
                ['Maiores quedas', 'bi-arrow-down-right-circle-fill', $cor['quedas'], 'perdeu', 'Posições perdidas entre largada e chegada (sem abandonos)'],
            ] as [$tituloR, $iconeR, $listaR, $tipoR, $descR])
                <article class="cur-card">
                    <header class="cur-card-header"><div><h3><i class="bi {{ $iconeR }}"></i> {{ $tituloR }}</h3><p>{{ $descR }}</p></div></header>
                    <ol class="cur-list" data-cur-lista data-limite="8">
                        @foreach($listaR as $r)
                            <li class="cur-item {{ $loop->index >= 8 ? 'oculto' : '' }}" data-item data-ativo="1" data-piloto="{{ $r['quem']['id'] }}">
                                <span class="cur-pos"></span>
                                @include('site.curiosidades._avatar', ['q' => $r['quem']])
                                <div class="cur-info">
                                    <div class="cur-nome-linha">
                                        @if(!empty($r['quem']['bandeira']))<img class="cur-bandeira" src="{{ $r['quem']['bandeira'] }}" alt="" loading="lazy">@endif
                                        <a href="{{ $r['quem']['url'] }}" class="cur-nome">{{ $r['quem']['nome'] }}</a>
                                    </div>
                                    <small class="cur-detalhe"><a href="{{ $r['corrida']['url'] }}">{{ $r['corrida']['evento'] }} · {{ $r['corrida']['temporada'] }}</a></small>
                                    <small class="cur-detalhe cur-extra"><span class="cur-equipe-mini">@include('site.curiosidades._avatar', ['q' => $r['equipe'], 'classe' => 'mini']) {{ $r['equipe']['nome'] }}</span></small>
                                </div>
                                <div class="cur-valor">
                                    @if($tipoR === 'largou')
                                        <strong>P{{ $r['largada'] }}</strong><small>largou e venceu</small>
                                    @else
                                        <strong class="cur-seta {{ $tipoR === 'perdeu' ? 'desce' : '' }}">{{ $tipoR === 'perdeu' ? '▼' : '▲' }}{{ $r['valor'] }}</strong><small>P{{ $r['largada'] }} → P{{ $r['chegada'] }}</small>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                    <p class="cur-vazio" hidden>Nenhum registro.</p>
                    @if(count($listaR) > 8)<button type="button" class="cur-mais">Ver mais</button>@endif
                </article>
            @endforeach

            <article class="cur-card">
                <header class="cur-card-header"><div><h3><i class="bi bi-pin-map-fill"></i> Donos da pista</h3><p>Mais vitórias de um piloto na mesma pista</p></div></header>
                <ol class="cur-list" data-cur-lista data-limite="8">
                    @foreach($cor['donos'] as $d)
                        <li class="cur-item {{ $loop->index >= 8 ? 'oculto' : '' }}" data-item data-ativo="1" data-piloto="{{ $d['quem']['id'] }}">
                            <span class="cur-pos"></span>
                            @include('site.curiosidades._avatar', ['q' => $d['quem']])
                            <div class="cur-info">
                                <div class="cur-nome-linha"><a href="{{ $d['quem']['url'] }}" class="cur-nome">{{ $d['quem']['nome'] }}</a></div>
                                <small class="cur-detalhe"><a class="cur-equipe-mini" href="{{ $d['url'] }}">@if($d['bandeira'])<img class="cur-bandeira" src="{{ $d['bandeira'] }}" alt="" loading="lazy">@endif {{ $d['pista'] }}</a></small>
                            </div>
                            <div class="cur-valor"><strong>{{ $d['valor'] }}</strong><small>vitórias</small></div>
                        </li>
                    @endforeach
                </ol>
                <p class="cur-vazio" hidden>Nenhum registro.</p>
                @if(count($cor['donos']) > 8)<button type="button" class="cur-mais">Ver mais</button>@endif
            </article>

            <article class="cur-card">
                <header class="cur-card-header"><div><h3><i class="bi bi-people-fill"></i> Pódios mais repetidos</h3><p>Os mesmos três pilotos no pódio, em qualquer ordem</p></div></header>
                <div>
                    @foreach($cor['trios'] as $t)
                        <div class="cur-trio" data-cur-ocultavel data-piloto="{{ implode(',', array_column($t['pilotos'], 'id')) }}">
                            <div class="fotos">@foreach($t['pilotos'] as $tp)<img src="{{ $tp['img'] }}" alt="{{ $tp['nome'] }}" title="{{ $tp['nome'] }}" loading="lazy">@endforeach</div>
                            <div class="nomes"><b>{{ implode(' · ', array_column($t['pilotos'], 'nome')) }}</b><br>último: {{ $t['ultima']['evento'] }} · {{ $t['ultima']['temporada'] }}</div>
                            <div class="num">{{ $t['valor'] }}×</div>
                        </div>
                    @endforeach
                </div>
            </article>
        </div>
    </section>

    {{-- ===================== TEMPORADAS ===================== --}}
    <section class="cur-secao" id="temporadas">
        <h2>Temporadas</h2>
        <p class="cur-sub">Campeonatos equilibrados, campeões improváveis e temporadas de domínio absoluto.</p>
        <div class="cur-grid">
            {{-- MELHORIA 20261105: listas de temporadas, 2 por linha no desktop e empilhadas no celular --}}
            <div class="cur-total cur-dupla">
                @foreach([['Domínio absoluto', 'bi-lightning-fill', $temp['dominio'], 'Maior percentual de vitórias de um piloto numa temporada (mín. 5 GPs)'], ['Campeões com menos vitórias', 'bi-patch-question-fill', $temp['campeoesMenosVitorias'], 'Títulos conquistados na regularidade']] as [$tituloT, $iconeT, $listaT, $descT])
                    <article class="cur-card">
                        <header class="cur-card-header"><div><h3><i class="bi {{ $iconeT }}"></i> {{ $tituloT }}</h3><p>{{ $descT }}</p></div></header>
                        <ol class="cur-list">
                            @foreach($listaT as $d)
                                <li class="cur-item" data-cur-ocultavel data-piloto="{{ $d['quem']['id'] }}">
                                    <span class="cur-pos"></span>
                                    @include('site.curiosidades._avatar', ['q' => $d['quem']])
                                    <div class="cur-info">
                                        <div class="cur-nome-linha">
                                            @if(!empty($d['quem']['bandeira']))<img class="cur-bandeira" src="{{ $d['quem']['bandeira'] }}" alt="{{ $d['quem']['pais'] }}" loading="lazy">@endif
                                            <a href="{{ $d['quem']['url'] }}" class="cur-nome">{{ $d['quem']['nome'] }}</a>
                                        </div>
                                        <small class="cur-detalhe cur-temporada-linha">
                                            <a href="{{ $d['url'] }}">{{ $d['temporada'] }}</a>
                                            <span class="cur-equipe-mini">@include('site.curiosidades._avatar', ['q' => $d['equipe'], 'classe' => 'mini']) {{ $d['equipe']['nome'] }}</span>
                                        </small>
                                        <span class="cur-barra" aria-hidden="true"><span style="width: {{ max(3, $d['pct']) }}%"></span></span>
                                    </div>
                                    <div class="cur-valor">
                                        <strong>{{ $d['valor'] }}<span class="cur-de">/{{ $d['corridas'] }}</span></strong>
                                        <small>{{ $dec($d['pct']) }}% dos GPs</small>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </article>
                @endforeach

                @foreach([['Temporadas mais disputadas', 'bi-shuffle', $temp['maisDisputadas']], ['Temporadas mais previsíveis', 'bi-lock-fill', $temp['menosDisputadas']]] as [$tituloD, $iconeD, $listaD])
                    <article class="cur-card">
                        <header class="cur-card-header"><div><h3><i class="bi {{ $iconeD }}"></i> {{ $tituloD }}</h3><p>Vencedores diferentes em relação ao número de GPs (mín. 5 GPs)</p></div></header>
                        <ol class="cur-list">
                            @foreach($listaD as $d)
                                <li class="cur-item">
                                    <span class="cur-pos"></span>
                                    <a class="cur-temporada-box" href="{{ $d['url'] }}" title="{{ $d['nome'] }}">
                                        <b>{{ $d['ordinal'] }}</b>@if($d['referencia'])<small>{{ $d['referencia'] }}</small>@endif
                                    </a>
                                    <div class="cur-info">
                                        <div class="cur-nome-linha"><a href="{{ $d['url'] }}" class="cur-nome">{{ $d['nome'] }}</a></div>
                                        @if($d['lider'])
                                            <small class="cur-detalhe cur-temporada-linha">
                                                <span class="cur-equipe-mini">@include('site.curiosidades._avatar', ['q' => $d['lider'], 'classe' => 'mini']) Mais vitórias: <a href="{{ $d['lider']['url'] }}">{{ $d['lider']['nome'] }}</a> ({{ $d['lider']['vitorias'] }})</span>
                                            </small>
                                        @endif
                                        <span class="cur-barra" aria-hidden="true"><span style="width: {{ max(3, round($d['vencedores'] / max(1, $d['corridas']) * 100)) }}%"></span></span>
                                    </div>
                                    <div class="cur-valor">
                                        <strong>{{ $d['vencedores'] }}</strong>
                                        <small>vencedores<br>em {{ $d['corridas'] }} GPs</small>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <p class="cur-rodape">Somente Grandes Prêmios (sprints e super corridas fora). Contagens de jejum e sequência consideram os GPs que o próprio piloto/equipe disputou.</p>
</div>

<script>
(function () {
    const MEU_PILOTO = @json($meuPiloto->id ?? null);
    const CHAVE = 'curiosidades.ocultarMeuPiloto';
    const pagina = document.querySelector('.cur-page');
    let ocultarMeu = MEU_PILOTO && localStorage.getItem(CHAVE) === '1';

    const ehMeu = el => MEU_PILOTO && (el.dataset.piloto || '').split(',').includes(String(MEU_PILOTO));

    // ---------- filtros das listas ----------
    function aplicar() {
        pagina.querySelectorAll('[data-cur-lista]').forEach(lista => {
            const card = lista.closest('.cur-card');
            const soAtivos = card.dataset.soAtivos === '1';
            const expandido = card.classList.contains('expandido');
            const base = parseInt(lista.dataset.limite || '8', 10);
            const limite = expandido ? Infinity : base;
            let visiveis = 0, elegiveis = 0;
            lista.querySelectorAll(':scope > [data-item]').forEach(item => {
                const ok = !(ocultarMeu && ehMeu(item)) && !(soAtivos && item.dataset.ativo !== '1');
                if (ok) elegiveis++;
                const mostra = ok && visiveis < limite;
                item.classList.toggle('oculto', !mostra);
                item.classList.toggle('lider', mostra && visiveis === 0);
                if (mostra) visiveis++;
            });
            const mais = card.querySelector('.cur-mais');
            if (mais) {
                mais.hidden = elegiveis <= base;
                mais.textContent = expandido ? 'Ver menos' : 'Ver mais (' + elegiveis + ')';
            }
            const vazio = lista.nextElementSibling;
            if (vazio && vazio.classList.contains('cur-vazio')) vazio.hidden = visiveis > 0;
        });
        pagina.querySelectorAll('[data-cur-primeiro]').forEach(caixa => {
            let achou = false;
            caixa.querySelectorAll(':scope > [data-item]').forEach(item => {
                const mostra = !achou && !(ocultarMeu && ehMeu(item));
                item.classList.toggle('oculto', !mostra);
                if (mostra) achou = true;
            });
        });
        pagina.querySelectorAll('[data-cur-ocultavel]').forEach(el => el.classList.toggle('oculto', !!(ocultarMeu && ehMeu(el))));
    }

    pagina.addEventListener('click', e => {
        const chip = e.target.closest('.cur-chips button');
        if (chip) {
            const card = chip.closest('.cur-card');
            card.dataset.soAtivos = chip.dataset.filtro;
            chip.parentElement.querySelectorAll('button').forEach(b => b.classList.toggle('ativo', b === chip));
            aplicar();
            return;
        }
        const mais = e.target.closest('.cur-mais');
        if (mais) {
            mais.closest('.cur-card').classList.toggle('expandido');
            aplicar();
        }
    });

    const chave = document.getElementById('curOcultarMeu');
    if (chave) {
        chave.checked = !!ocultarMeu;
        chave.addEventListener('change', () => {
            ocultarMeu = chave.checked;
            localStorage.setItem(CHAVE, ocultarMeu ? '1' : '0');
            aplicar();
        });
    }
    aplicar();

    // ---------- navegação: destaca a seção visível ----------
    const links = [...document.querySelectorAll('#curNav a')];
    const observador = new IntersectionObserver(entradas => {
        entradas.forEach(en => {
            if (en.isIntersecting) links.forEach(a => a.classList.toggle('ativo', a.getAttribute('href') === '#' + en.target.id));
        });
    }, { rootMargin: '-30% 0px -60% 0px' });
    document.querySelectorAll('.cur-secao').forEach(s => observador.observe(s));
    links.forEach(a => a.addEventListener('click', e => {
        e.preventDefault();
        document.querySelector(a.getAttribute('href')).scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));

    // ---------- gráficos (criados só quando aparecem na tela) ----------
    if (typeof Chart === 'undefined') return;
    const VERMELHO = '#e10600', TINTA = '#52514e', GRADE = '#ececec';
    Chart.defaults.font.family = "'Titillium Web', sans-serif";
    Chart.defaults.color = TINTA;
    Chart.defaults.plugins.legend.display = false;
    Chart.defaults.plugins.tooltip.backgroundColor = '#15151e';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.maintainAspectRatio = false;
    const fmt = v => Number(v).toLocaleString('pt-BR', { maximumFractionDigits: 2 });

    const eixos = (horizontal = false, extraY = {}) => {
        const valor = { beginAtZero: true, grid: { color: GRADE, drawBorder: false }, ticks: { precision: 0 }, ...extraY };
        const categoria = { grid: { display: false, drawBorder: false } };
        return horizontal ? { x: valor, y: categoria } : { x: categoria, y: valor };
    };
    const barras = (rotulos, valores, opcoes = {}) => ({
        type: 'bar',
        data: { labels: rotulos, datasets: [{ data: valores, backgroundColor: VERMELHO, hoverBackgroundColor: '#15151e', borderRadius: 4, borderSkipped: 'start', maxBarThickness: opcoes.grossura || 34 }] },
        options: { indexAxis: opcoes.horizontal ? 'y' : 'x', scales: eixos(opcoes.horizontal), plugins: { tooltip: { callbacks: opcoes.tooltip || {} } }, interaction: { mode: 'index', intersect: false } },
    });
    const linha = (rotulos, valores, opcoes = {}) => ({
        type: 'line',
        data: { labels: rotulos, datasets: [{ data: valores, borderColor: VERMELHO, backgroundColor: 'rgba(225,6,0,.08)', fill: true, borderWidth: 2, tension: .3, pointRadius: 0, pointHoverRadius: 5, pointHoverBackgroundColor: VERMELHO }] },
        options: { scales: eixos(false, opcoes.y || {}), plugins: { tooltip: { callbacks: opcoes.tooltip || {} } }, interaction: { mode: 'index', intersect: false } },
    });

    const { sede, origem, abTemp, distAb, grid } = @json($graficosJs);

    const graficos = {
        grafContinentes: () => barras(sede.map(c => c.n), sede.map(c => c.v), { horizontal: true, grossura: 26, tooltip: { label: i => ' ' + fmt(i.raw) + ' vitórias · ' + sede[i.dataIndex].d + ' vencedores diferentes' } }),
        grafOrigem: () => barras(origem.map(c => c.n), origem.map(c => c.v), { horizontal: true, grossura: 26, tooltip: { label: i => ' ' + fmt(i.raw) + ' vitórias de ' + origem[i.dataIndex].p + ' pilotos' } }),
        grafAbandTemporada: () => linha(abTemp.map(t => t.r), abTemp.map(t => t.m), { tooltip: { title: i => abTemp[i[0].dataIndex].n, label: i => ' ' + fmt(i.raw) + ' abandonos por GP (' + abTemp[i.dataIndex].t + ' em ' + abTemp[i.dataIndex].c + ' GPs)' } }),
        grafDistAband: () => barras(distAb.map(d => d.rotulo), distAb.map(d => d.valor), { tooltip: { title: i => i[0].label + ' abandono(s)', label: i => ' ' + i.raw + ' GPs' } }),
        grafGrid: () => barras(grid.map(d => d.rotulo), grid.map(d => d.valor), { tooltip: { title: i => 'Largando de ' + i[0].label, label: i => ' ' + i.raw + ' vitórias' } }),
    };

    const observadorGraficos = new IntersectionObserver(entradas => {
        entradas.forEach(en => {
            if (!en.isIntersecting) return;
            observadorGraficos.unobserve(en.target);
            new Chart(en.target, graficos[en.target.id]());
        });
    }, { rootMargin: '200px 0px' });
    Object.keys(graficos).forEach(id => { const el = document.getElementById(id); if (el) observadorGraficos.observe(el); });
})();
</script>
