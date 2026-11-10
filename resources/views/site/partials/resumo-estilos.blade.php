{{-- MELHORIA 20261110: estilos compartilhados pelos resumos de temporada e de corrida --}}
<style>
    .res-page {
        --f1-red: #e10600; --f1-dark: #15151e; --f1-darker: #11101d; --f1-gray: #38383f;
        --ink-1: #15151e; --ink-2: #52514e; --ink-3: #8a8985; --linha: #ececec; --fundo: #f3f4f6;
        --ouro: #f5c518; --prata: #c9ccd1; --bronze: #d4925b; --verde-po: #00d2be;
        font-family: 'Titillium Web', sans-serif; color: var(--ink-1); background: var(--fundo);
        padding: 20px 24px 80px; min-height: 100vh; max-width: 100%; overflow-wrap: break-word;
    }
    .res-page a { text-decoration: none; }
    .res-page *, .res-page *::before { box-sizing: border-box; }

    /* ---------- HERO ---------- */
    .res-hero { background: radial-gradient(circle at 85% 20%, rgba(225,6,0,.35), transparent 45%), linear-gradient(120deg, var(--f1-darker), var(--f1-dark) 60%, #24242f);
        border-radius: 14px; padding: 24px 28px; color: #fff; border-bottom: 4px solid var(--f1-red); box-shadow: 0 10px 30px rgba(0,0,0,.15);
        display: flex; justify-content: space-between; align-items: flex-end; gap: 18px; flex-wrap: wrap; }
    .res-hero small.res-kicker { display: block; color: #c3c2b7; font-size: .78rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; }
    .res-hero h1 { font-weight: 900; text-transform: uppercase; letter-spacing: -1px; font-size: 2.1rem; margin: 2px 0 6px; line-height: 1; }
    .res-hero h1 span { color: var(--f1-red); }
    .res-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .72rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
    .res-pill.fim { background: rgba(255,255,255,.12); color: #fff; }
    .res-pill.andamento { background: var(--f1-red); color: #fff; }
    .res-hero-acoes { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; margin-left: auto; }
    .res-navtemp { display: flex; align-items: center; gap: 6px; }
    .res-navtemp a, .res-navtemp span.off { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,.4); color: #fff; display: inline-flex; align-items: center; justify-content: center; }
    .res-navtemp a:hover { border-color: var(--f1-red); background: var(--f1-red); }
    .res-navtemp span.off { opacity: .25; }
    .res-navtemp select { background: #24242f; color: #fff; border: 1px solid #3a3a45; border-radius: 8px; padding: 6px 10px; font-weight: 700; max-width: 260px; }
    .res-links { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
    .res-links a { color: #fff; border: 1px solid rgba(255,255,255,.25); border-radius: 999px; padding: 4px 12px; font-size: .78rem; font-weight: 700; }
    .res-links a:hover { border-color: #fff; background: rgba(255,255,255,.08); }

    /* ---------- KPIs ---------- */
    .res-kpis { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 14px; margin: 20px 0; }
    @media (max-width: 1100px) { .res-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .res-kpi { background: #fff; border-radius: 10px; padding: 14px 16px; border-left: 4px solid var(--f1-red); box-shadow: 0 4px 15px rgba(0,0,0,.04); }
    .res-kpi span { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--ink-2); text-transform: uppercase; font-weight: 700; letter-spacing: .5px; }
    .res-kpi span i { color: var(--f1-red); }
    .res-kpi strong { display: block; font-size: 1.8rem; font-weight: 900; line-height: 1.1; margin-top: 4px; }
    .res-kpi small { color: var(--ink-3); font-size: 12px; }

    /* ---------- NAV ---------- */
    .res-nav { position: sticky; top: 0; z-index: 50; background: rgba(243,244,246,.92); backdrop-filter: blur(6px); padding: 10px 0; margin: 0 0 6px; display: flex; gap: 8px; overflow-x: auto; }
    .res-nav a { flex: 0 0 auto; color: var(--ink-1); background: #fff; border: 1px solid var(--linha); border-radius: 20px; padding: 6px 14px; font-size: 13px; font-weight: 700; text-transform: uppercase; }
    .res-nav a:hover { border-color: var(--f1-red); color: var(--f1-red); }

    /* ---------- SEÇÕES / CARDS ---------- */
    .res-secao { scroll-margin-top: 70px; margin-top: 28px; }
    .res-secao > h2 { font-size: 1.35rem; font-weight: 900; text-transform: uppercase; display: flex; align-items: center; gap: 10px; margin: 0 0 4px; }
    .res-secao > h2::before { content: ''; width: 6px; height: 26px; background: var(--f1-red); border-radius: 3px; }
    .res-secao > p.res-sub { color: var(--ink-2); margin: 0 0 14px 16px; font-size: .9rem; }
    .res-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,.04); border: 1px solid #eee; min-width: 0; }
    .res-card-pad { padding: 16px 18px; }
    .res-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .res-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    @media (max-width: 1000px) { .res-grid-2, .res-grid-3 { grid-template-columns: 1fr; } }
    .res-foto { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; object-position: top; background: #e5e7eb; flex-shrink: 0; }
    .res-logo { width: 28px; height: 28px; border-radius: 6px; padding: 3px; object-fit: contain; flex-shrink: 0; vertical-align: middle; }
    .res-logo.mini { width: 22px; height: 22px; padding: 2px; border-radius: 50%; }
    .res-bandeira { width: 22px; height: 15px; object-fit: cover; border-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,.25); vertical-align: middle; }
    .res-muted { color: var(--ink-3); }

    /* ---------- JORNAL ---------- */
    .res-jornal { background: #fffdf7; border: 1px solid #e8e2d0; border-radius: 12px; padding: 18px 22px 22px; box-shadow: 0 4px 15px rgba(0,0,0,.04); }
    .res-jornal-cab { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; flex-wrap: wrap; border-bottom: 3px double #2b2b2b; padding-bottom: 8px; margin-bottom: 16px; }
    .res-jornal-cab b { font-family: Georgia, 'Times New Roman', serif; font-size: 1.9rem; font-weight: 900; letter-spacing: -.5px; font-style: italic; }
    .res-jornal-cab span { font-size: .78rem; color: var(--ink-2); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; }
    .res-noticias { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px 24px; }
    @media (max-width: 1000px) { .res-noticias { grid-template-columns: 1fr; } }
    .res-noticia { min-width: 0; }
    .res-noticia + .res-noticia { border-top: 1px solid #e8e2d0; padding-top: 14px; }
    @media (min-width: 1001px) { .res-noticia + .res-noticia { border-top: 0; padding-top: 0; } .res-noticia:not(.principal) { border-left: 1px solid #e8e2d0; padding-left: 20px; } .res-noticia.principal + .res-noticia { border-left: 0; padding-left: 0; } }
    .res-noticia.principal { grid-column: 1 / -1; display: grid; grid-template-columns: 120px minmax(0, 1fr); gap: 18px; align-items: start; border-bottom: 1px solid #e8e2d0; padding-bottom: 16px; }
    @media (max-width: 700px) { .res-noticia.principal { grid-template-columns: 1fr; } }
    .res-noticia.principal .res-noticia-foto { width: 120px; height: 120px; border-radius: 10px; object-fit: cover; object-position: top; background: #e5e7eb; border-bottom: 5px solid var(--cor-noticia, var(--f1-red)); }
    .res-tag { display: inline-block; font-size: .68rem; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; color: var(--f1-red); margin-bottom: 2px; }
    .res-noticia h3 { font-family: Georgia, 'Times New Roman', serif; font-weight: 800; font-size: 1.2rem; line-height: 1.2; margin: 0 0 6px; color: #111; }
    .res-noticia.principal h3 { font-size: 1.9rem; }
    .res-noticia p { margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: .95rem; line-height: 1.5; color: #333; }
    .res-noticia p::first-letter { font-weight: 700; }
    .res-noticia.principal p::first-letter { float: left; font-size: 3rem; line-height: .9; padding: 4px 6px 0 0; font-weight: 900; color: var(--f1-red); }
    .res-noticia a.res-mais { font-size: .8rem; font-weight: 700; color: var(--f1-red); display: inline-block; margin-top: 6px; font-family: 'Titillium Web', sans-serif; }

    /* ---------- PÓDIOS ---------- */
    .res-podio { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; align-items: end; }
    .res-degrau { text-align: center; padding: 12px 8px 10px; border-radius: 10px 10px 4px 4px; background: #fafafa; border: 1px solid var(--linha); min-width: 0; }
    .res-degrau.p1 { order: 2; border-top: 5px solid var(--ouro); padding-top: 18px; }
    .res-degrau.p2 { order: 1; border-top: 5px solid var(--prata); }
    .res-degrau.p3 { order: 3; border-top: 5px solid var(--bronze); }
    .res-degrau .res-foto { width: 64px; height: 64px; }
    .res-degrau.p1 .res-foto { width: 78px; height: 78px; box-shadow: 0 0 0 3px var(--ouro); }
    .res-degrau b { display: block; font-size: .95rem; font-weight: 800; margin-top: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .res-degrau small { display: block; color: var(--ink-3); font-size: .78rem; }
    .res-degrau strong { display: block; font-size: 1.3rem; font-weight: 900; }
    .res-degrau .pos { font-size: .75rem; font-weight: 800; color: var(--ink-3); letter-spacing: 1px; }
    .res-degrau .res-logo-grande { width: 64px; height: 64px; border-radius: 12px; padding: 8px; object-fit: contain; }
    .res-extras { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
    .res-extra { flex: 1 1 220px; display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--linha); background: #fafafa; }
    .res-extra i { font-size: 1.4rem; }
    .res-extra small { display: block; font-size: .68rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--ink-3); }
    .res-extra b { font-weight: 800; }

    /* ---------- EVOLUÇÃO ---------- */
    .res-grafico { position: relative; height: 340px; }
    .res-lideres { display: flex; gap: 3px; flex-wrap: wrap; margin-top: 14px; }
    .res-lider { min-width: 58px; flex: 1 1 58px; text-align: center; border-radius: 6px; padding: 4px 3px; font-size: .7rem; font-weight: 700; line-height: 1.15; border: 1px solid rgba(0,0,0,.12); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .res-lider small { display: block; font-size: .62rem; opacity: .8; }
    .res-legenda-lider { font-size: .8rem; color: var(--ink-2); margin-top: 8px; }

    /* ---------- TABELAS ---------- */
    .res-tabela-wrap { overflow-x: auto; }
    .res-tabela { width: 100%; border-collapse: collapse; font-size: .88rem; }
    .res-tabela th { background: var(--f1-darker); color: #fff; font-size: .72rem; text-transform: uppercase; letter-spacing: .5px; padding: 9px 8px; text-align: center; white-space: nowrap; position: sticky; top: 0; }
    .res-tabela th.ordenavel { cursor: pointer; user-select: none; }
    .res-tabela th.ordenavel::after { content: ' ⇅'; opacity: .45; }
    .res-tabela th.esq, .res-tabela td.esq { text-align: left !important; }
    .res-tabela td { padding: 7px 8px; border-bottom: 1px solid #f0f0f0; text-align: center; white-space: nowrap; vertical-align: middle; }
    .res-tabela tbody tr:hover { background: #fafafa; }
    .res-tabela tr.campeao td { background: rgba(245,197,24,.13); }
    .res-tabela tr.campeao td:first-child { box-shadow: inset 4px 0 0 var(--ouro); }
    .res-tabela.recolhida tbody tr:nth-child(n+16) { display: none; }
    .res-expandir { display: block; width: 100%; border: 0; border-top: 1px solid var(--linha); background: #fafafa; padding: 9px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--ink-2); border-radius: 0 0 12px 12px; }
    .res-expandir:hover { background: var(--f1-darker); color: #fff; }
    .res-tabela .forte { font-weight: 800; }
    .res-tabela .zero { color: #c9c9c9; }
    .res-tabela a { color: inherit; }
    .res-tabela a:hover { color: var(--f1-red); }
    .res-piloto-cel { display: inline-flex; align-items: center; gap: 6px; }
    .res-largou { font-size: .7rem; color: var(--ink-3); }
    .res-novo-lider { display: inline-block; font-size: .62rem; font-weight: 800; background: var(--f1-red); color: #fff; border-radius: 4px; padding: 0 5px; margin-left: 4px; letter-spacing: .5px; }
    .res-positivo { color: #006300; font-weight: 700; }
    .res-negativo { color: #b42318; font-weight: 700; }

    /* ---------- DUELOS ---------- */
    #gradeDuelos:not(.todos) .res-duelo-extra { display: none; }
    .res-duelo-cab { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 800; text-transform: uppercase; font-size: .85rem; }
    .res-duelo-nomes { display: flex; justify-content: space-between; font-weight: 800; font-size: .9rem; }
    .res-barra { display: flex; height: 10px; border-radius: 5px; overflow: hidden; background: #eee; margin: 3px 0 2px; gap: 2px; }
    .res-barra span { display: block; height: 100%; }
    .res-duelo-linha { display: grid; grid-template-columns: 28px minmax(0, 1fr) 28px; gap: 6px; align-items: center; font-size: .8rem; margin-top: 6px; }
    .res-duelo-linha b { text-align: center; }
    .res-duelo-linha small { grid-column: 1 / -1; text-align: center; color: var(--ink-3); font-size: .68rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: -4px; }

    /* ---------- FATOS ---------- */
    .res-fatos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    @media (max-width: 1100px) { .res-fatos { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 700px) { .res-fatos { grid-template-columns: 1fr; } }
    .res-fato { display: grid; grid-template-columns: 38px minmax(0, 1fr); gap: 10px; padding: 14px; background: #fff; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,.03); }
    .res-fato i { width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; background: rgba(225,6,0,.08); color: var(--f1-red); }
    .res-fato b { display: block; font-weight: 800; font-size: .95rem; line-height: 1.2; }
    .res-fato p { margin: 3px 0 0; font-size: .85rem; color: var(--ink-2); line-height: 1.35; }
    .res-fato.nao i { background: #eef0f3; color: #6c757d; position: relative; }
    .res-fato.nao i::after { content: ''; position: absolute; width: 26px; height: 2px; background: #6c757d; transform: rotate(-45deg); border-radius: 2px; }
    .res-fato.nao b { color: #3d3d3d; }

    /* ---------- E SE ---------- */
    .res-cenario { padding: 14px 16px; }
    .res-cenario-cab { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
    .res-cenario-cab b { font-weight: 800; font-size: .95rem; }
    .res-cenario-cab small { display: block; color: var(--ink-3); font-size: .76rem; }
    .res-selo { flex-shrink: 0; font-size: .66rem; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; padding: 3px 8px; border-radius: 999px; }
    .res-selo.igual { background: #eef0f3; color: #52514e; }
    .res-selo.mudou { background: var(--f1-red); color: #fff; }
    .res-cenario ol { list-style: none; margin: 10px 0 0; padding: 0; }
    .res-cenario li { display: flex; align-items: center; gap: 8px; padding: 5px 0; border-top: 1px solid #f4f4f4; font-size: .88rem; }
    .res-cenario li .pos { width: 18px; font-weight: 900; color: var(--ink-3); }
    .res-cenario li .res-foto { width: 28px; height: 28px; }
    .res-cenario li .nome { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 700; color: var(--ink-1); }
    .res-cenario li .nome:hover { color: var(--f1-red); }
    .res-cenario li.lider .nome { font-weight: 900; }
    .res-cenario-lista { max-height: 340px; overflow-y: auto; padding-right: 4px !important; scrollbar-width: thin; }
    /* classificacao.css (global) define .pos com 24px itálico: aqui o número fica numa linha só */
    .res-cenario li .pos { width: 28px; flex-shrink: 0; text-align: right; white-space: nowrap; font-size: .85rem; font-style: normal; line-height: 1; font-variant-numeric: tabular-nums; }
    .res-delta { width: 34px; flex-shrink: 0; text-align: center; font-size: .72rem; font-weight: 800; }
    .res-delta.sobe { color: #006300; }
    .res-delta.desce { color: #b42318; }
    .res-delta.igual { color: #b9bcc2; }
    .res-cenario-rodape { display: block; margin-top: 6px; color: var(--ink-3); font-size: .72rem; text-align: right; }
    .res-cenario li .valor { color: var(--ink-2); font-weight: 700; font-size: .82rem; }

    @media (max-width: 700px) {
        .res-page { padding: 12px 12px 60px; }
        .res-hero { padding: 18px 16px; }
        .res-hero h1 { font-size: 1.6rem; }
        .res-hero-acoes { align-items: stretch; margin-left: 0; width: 100%; }
        .res-navtemp select { flex: 1; min-width: 0; max-width: none; }
        .res-links { justify-content: flex-start; }
        .res-kpi strong { font-size: 1.5rem; }
        .res-jornal { padding: 14px; }
        .res-jornal-cab b { font-size: 1.5rem; }
        .res-noticia.principal h3 { font-size: 1.4rem; }
        .res-podio { gap: 6px; }
        .res-degrau .res-foto, .res-degrau .res-logo-grande { width: 48px; height: 48px; }
        .res-degrau.p1 .res-foto { width: 56px; height: 56px; }
    }
    .res-obs { white-space: pre-line; font-size: .92rem; color: var(--ink-2); }
    .res-vazio { padding: 40px; text-align: center; color: var(--ink-3); font-size: 1rem; }
</style>
