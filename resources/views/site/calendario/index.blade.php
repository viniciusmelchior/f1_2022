{{-- MELHORIA 20261119: planejamento do calendário da temporada (substitui a planilha CalendarioF1).
     Formulário + grid que atualiza na hora + anotações. Lógica de ordem igual à planilha:
     ao mudar a posição de uma etapa as outras se reorganizam e a numeração é refeita. --}}
@extends('layouts.main')

@section('section')
@include('site.partials.resumo-estilos')
<style>
    .cal-page { font-size: 1.02rem; }
    /* topo enxuto */
    .cal-page .res-hero.cal-hero { padding: 14px 22px; align-items: center; }
    .cal-hero-titulo small.res-kicker { font-size: .7rem; }
    .cal-page .cal-hero h1 { font-size: 1.75rem; margin: 2px 0 0; letter-spacing: -.5px; }
    .cal-hero-acoes { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; margin-left: auto; }
    #calSelTemporada { background: #24242f; color: #fff; border: 1px solid #3a3a45; border-radius: 8px; height: 36px; padding: 0 10px; font-weight: 700; max-width: 240px; }
    /* botões por função: criar = vermelho F1 · copiar = azul · editar = âmbar · Excel = verde · excluir = vermelho */
    .cal-tbtn { --cor: #fff; display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 999px; font-size: .85rem; font-weight: 800; color: #fff !important;
        border: 1.5px solid var(--cor); background: rgba(255,255,255,.04); transition: background .15s, color .15s; }
    .cal-tbtn i { color: var(--cor); font-size: 1rem; }
    .cal-tbtn:hover { background: var(--cor); color: #15151e !important; }
    .cal-tbtn:hover i { color: #15151e; }
    .cal-tbtn.criar { --cor: #e10600; background: #e10600; }
    .cal-tbtn.criar i, .cal-tbtn.criar:hover i { color: #fff; }
    .cal-tbtn.criar:hover { background: #b80500; color: #fff !important; }
    .cal-tbtn.copiar { --cor: #60a5fa; }
    .cal-tbtn.editar { --cor: #f5c518; }
    .cal-tbtn.excel { --cor: #21a366; }
    .cal-tbtn.excel:hover, .cal-tbtn.excel:hover i { color: #fff !important; }
    .cal-tbtn.excluir { --cor: #ff6b6b; padding: 0 12px; }
    .cal-tbtn.excluir:hover, .cal-tbtn.excluir:hover i { color: #fff !important; }
    /* cards menores */
    .cal-page .res-kpis { gap: 10px; margin: 14px 0; }
    .cal-page .res-kpi { padding: 8px 14px; border-left-width: 3px; }
    .cal-page .res-kpi span { font-size: 10px; }
    .cal-page .res-kpi strong { font-size: 1.35rem; margin-top: 6px; } /* espaço entre o título e o número */
    .cal-page .res-kpi strong { display: inline-block; }
    .cal-page .res-kpi small { font-size: 11px; margin-left: 6px; } /* texto ao lado do número: cards da mesma altura */
    .cal-painel { display: none; padding: 16px 18px; margin-top: 14px; border-left: 4px solid var(--f1-red); }
    .cal-painel.on { display: block; }
    .cal-painel form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; margin: 0; }
    .cal-campo { display: flex; flex-direction: column; gap: 4px; min-width: 0; position: relative; }
    .cal-x { position: absolute; right: 6px; bottom: 8px; width: 24px; height: 24px; border: 0; border-radius: 50%; background: #eef0f3; color: #6b6b6b; font-size: 16px; line-height: 22px; padding: 0; cursor: pointer; display: none; }
    .cal-x:hover { background: #fde2e1; color: #b42318; }
    .cal-x.on { display: block; }
    .cal-campo input.tem-x { padding-right: 34px; }
    #tabelaCalendario td[data-campo] { cursor: text; }
    #tabelaCalendario td[data-campo]:hover { outline: 1px dashed #c8c8c8; outline-offset: -4px; }
    .cal-inline { width: 100%; min-width: 90px; min-height: 34px; border: 2px solid var(--f1-red); border-radius: 6px; padding: 4px 8px; font-size: .95rem; font-family: inherit; font-style: normal; background: #fff; color: var(--ink-1); }
    textarea.cal-inline { min-width: 220px; min-height: 60px; }
    .cal-campo label { font-size: .72rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--ink-2); margin: 0; }
    .cal-campo input, .cal-campo select, .cal-campo textarea { min-height: 40px; border: 1px solid #d6d6d6; border-radius: 8px; padding: 6px 10px; font-size: 1rem; background: #fff; color: var(--ink-1); width: 100%; font-family: inherit; }
    .cal-campo textarea { resize: vertical; min-height: 60px; }
    .cal-campo input:focus, .cal-campo select:focus, .cal-campo textarea:focus { outline: none; border-color: var(--f1-red); box-shadow: 0 0 0 3px rgba(225,6,0,.12); }
    .cal-campo input::placeholder { color: #a9a9a9; font-style: italic; }
    .cal-titulo-card { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 18px; border-bottom: 1px solid var(--linha); }
    .cal-titulo-card h2 { margin: 0; font-size: 1.15rem; font-weight: 900; text-transform: uppercase; display: flex; align-items: center; gap: 10px; }
    .cal-titulo-card h2::before { content: ''; width: 6px; height: 22px; background: var(--f1-red); border-radius: 3px; }
    .cal-form { padding: 16px 18px; display: grid; gap: 12px; grid-template-columns: 90px 1.1fr 1.6fr 1.1fr 1.5fr 120px 95px 95px; }
    .cal-form .cal-obs { grid-column: 1 / -1; }
    .cal-sprint-box { grid-column: 1 / -1; border: 1px dashed #e0b400; background: #fffbea; border-radius: 10px; padding: 12px 14px; display: none; gap: 12px; grid-template-columns: 1.1fr 1.6fr 1.1fr 1.5fr 120px 95px 95px; } /* mesmas larguras da corrida */
    .cal-sprint-box.on { display: grid; }
    .cal-sprint-box .cal-obs { grid-column: 1 / -1; }
    .cal-sprint-box h3 { grid-column: 1 / -1; margin: 0; font-size: .85rem; font-weight: 900; text-transform: uppercase; color: #8a6d00; letter-spacing: 1px; }
    .cal-acoes-form { grid-column: 1 / -1; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .cal-switch { display: inline-flex; align-items: center; gap: 8px; font-weight: 800; cursor: pointer; user-select: none; background: #fff6cc; border: 1px solid #f0d36b; color: #7a5c00; padding: 8px 14px; border-radius: 999px; }
    .cal-switch input { width: 18px; height: 18px; accent-color: #d4a000; }
    .cal-btn { border: 0; border-radius: 8px; padding: 9px 18px; font-weight: 800; font-size: .95rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .cal-btn.prim { background: var(--f1-red); color: #fff; }
    .cal-btn.prim:hover { background: #b80500; }
    .cal-btn.sec { background: #eef0f3; color: var(--ink-1); }
    .cal-btn.sec:hover { background: #dfe2e7; }
    .cal-btn.perigo { background: #fde2e1; color: #b42318; }
    .cal-editando { display: none; font-weight: 800; color: var(--f1-red); }
    .cal-editando.on { display: inline; }
    .cal-msg { display: none; padding: 10px 14px; border-radius: 8px; font-weight: 700; margin: 0 18px 14px; }
    .cal-msg.on { display: block; }
    #formEtapa .cal-msg { grid-column: 1 / -1; margin: 0; } /* mensagem ocupa a largura toda do formulário */
    .cal-msg.erro { background: #fde2e1; color: #b42318; }
    .cal-msg.ok { background: #dcfce7; color: #166534; }
    .cal-filtros { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; padding: 14px 18px; border-bottom: 1px solid var(--linha); background: #fafafa; }
    .cal-filtros .cal-campo input { width: 260px; }
    .cal-filtros .cal-campo select { width: 210px; }
    .cal-gerar { display: flex; gap: 8px; align-items: flex-end; padding-left: 12px; border-left: 2px solid var(--linha); }
    .cal-filtros .cal-gerar .cal-campo input { width: 90px; }
    .cal-contador { margin-left: auto; font-weight: 800; color: var(--ink-2); }
    #tabelaCalendario { font-size: 1rem; }
    #tabelaCalendario td { padding: 9px 10px; white-space: normal; }
    #tabelaCalendario td.nowrap { white-space: nowrap; }
    #tabelaCalendario tr.cal-sprint td { background: #fffbea; color: #6b5a1e; font-style: italic; border-bottom-style: dashed; }
    #tabelaCalendario tr.cal-sprint td:first-child { box-shadow: inset 4px 0 0 #e0b400; }
    #tabelaCalendario tr.cal-corrida td:first-child { box-shadow: inset 4px 0 0 var(--f1-red); }
    #tabelaCalendario tr.cal-sel td { background: #fff1f0 !important; }
    .cal-num { width: 64px; height: 36px; text-align: center; font-weight: 900; font-size: 1.1rem; border: 2px solid #e5e5e5; border-radius: 8px; }
    .cal-num:focus { outline: none; border-color: var(--f1-red); }
    .cal-num-sprint { display: inline-block; width: 64px; font-weight: 900; font-size: 1.05rem; color: #b08a00; }
    .cal-tipo { display: inline-block; font-size: .72rem; font-weight: 900; letter-spacing: .5px; text-transform: uppercase; padding: 3px 9px; border-radius: 999px; font-style: normal; }
    .cal-tipo.corrida { background: var(--f1-red); color: #fff; }
    .cal-tipo.sprint { background: #f5c518; color: #3d3000; }
    .cal-cont { display: inline-block; font-size: .78rem; font-weight: 800; padding: 3px 10px; border-radius: 6px; font-style: normal; white-space: nowrap; }
    .cal-nome { font-weight: 800; }
    .cal-band { width: 24px; height: 16px; object-fit: cover; border-radius: 2px; box-shadow: 0 0 0 1px rgba(0,0,0,.12); vertical-align: -3px; margin-right: 8px; font-style: normal; }
    .cal-obs-cel { max-width: 340px; font-size: .9rem; color: var(--ink-2); }
    .cal-acao { border: 0; background: transparent; font-size: 1.15rem; padding: 2px 6px; border-radius: 6px; cursor: pointer; color: #b7791f; }
    .cal-acao:hover { background: #fff6dc; color: #92600a; }
    .cal-acao.del { color: #d92d20; }
    .cal-acao.del:hover { background: #fde2e1; color: #b42318; }
    /* MELHORIA 20261126: cadastrar oficialmente */
    .cal-acao.oficial { color: #15803d; }
    .cal-acao.oficial:hover { background: #dcfce7; color: #166534; }
    #calOficial .cal-modal-caixa { max-width: 640px; position: relative; } /* relative: a lista do select2 acompanha o modal */
    #formOficial { display: flex; flex-direction: column; gap: 14px; padding: 18px; }
    #formOficial[hidden], #calOficial [hidden] { display: none !important; }
    #formOficial .cal-campo .select2-container { width: 100% !important; }
    #formOficial .cal-campo .select2-selection { min-height: 40px; display: flex; align-items: center; border-radius: 8px; border-color: #d6d6d6; }
    #formOficial .cal-campo .select2-selection__rendered { flex: 1; }
    #formOficial .cal-campo .select2-selection__arrow { height: 38px; }
    #formOficial .cal-campo.nao .select2-selection { border: 2px solid #d92d20; }
    #formOficial .cal-campo.atencao .select2-selection { border: 2px solid #e0a100; }
    #formOficial #of_ordem { width: 110px; }
    .cal-of-status { font-size: .85rem; font-weight: 700; }
    .cal-of-status:empty { display: none; }
    .cal-of-status.ok { color: #166534; }
    .cal-of-status.atencao { color: #92600a; }
    .cal-of-status.nao { color: #b42318; }
    #formOficial .cal-switch { align-self: flex-start; }
    #formOficial .cal-msg, #calOficial .cal-of-aviso { margin: 0; }
    .cal-of-aviso { background: #fff6cc; border: 1px solid #f0d36b; color: #7a5c00; border-radius: 8px; padding: 10px 14px; font-weight: 700; font-size: .95rem; }
    .cal-of-aviso ul { margin: 6px 0; padding-left: 20px; }
    .cal-of-carregando { padding: 30px; text-align: center; color: var(--ink-3); }
    .cal-of-pronto { padding: 26px 18px; text-align: center; }
    .cal-of-pronto i { font-size: 2.4rem; color: #15803d; }
    .cal-of-pronto p { font-weight: 800; margin: 10px 0 18px; }
    .cal-of-pronto .cal-acoes-form { justify-content: center; }
    .cal-vazio { padding: 30px; text-align: center; color: var(--ink-3); font-size: 1.05rem; }
    .cal-anot { padding: 16px 18px; }
    .cal-anot textarea { width: 100%; min-height: 320px; resize: vertical; border: 1px solid #d6d6d6; border-radius: 10px; padding: 14px 16px; font-size: 1.02rem; line-height: 1.55; font-family: inherit; background: #fffef8; }
    .cal-anot textarea:focus { outline: none; border-color: var(--f1-red); box-shadow: 0 0 0 3px rgba(225,6,0,.12); }
    .cal-calc { grid-column: 1 / -1; font-size: .85rem; color: var(--ink-2); margin-top: -4px; }
    .cal-calc b { color: var(--ink-1); }
    .cal-vazio-cel { color: #9a9a9a; font-style: normal; }
    /* modal de confirmação */
    .cal-conf { position: fixed; inset: 0; background: rgba(17,16,29,.55); z-index: 2500; display: none; align-items: center; justify-content: center; padding: 16px; }
    .cal-conf.on { display: flex; animation: calFade .12s ease-out; }
    .cal-conf-caixa { background: #fff; border-radius: 16px; width: 100%; max-width: 440px; padding: 26px 26px 20px; text-align: center; box-shadow: 0 24px 70px rgba(0,0,0,.35); border-top: 5px solid var(--f1-red); animation: calSobe .15s ease-out; }
    .cal-conf-icone { width: 62px; height: 62px; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 28px; background: #fde2e1; color: #b42318; }
    .cal-conf-icone.info { background: #e8eefc; color: #1e40af; }
    .cal-conf-caixa h3 { margin: 0 0 10px; font-size: 1.35rem; font-weight: 900; text-transform: uppercase; color: var(--ink-1); }
    .cal-conf-alvo { display: inline-flex; flex-direction: column; gap: 2px; background: #f6f7f9; border-radius: 10px; padding: 10px 16px; margin-bottom: 12px; font-size: 1rem; }
    .cal-conf-alvo:empty { display: none; }
    .cal-conf-alvo b { font-size: 1.05rem; }
    .cal-conf-alvo small { color: var(--ink-2); }
    .cal-conf-alvo .sprint { color: #8a6d00; font-weight: 700; }
    .cal-conf-caixa p { color: var(--ink-2); margin: 0 0 18px; font-size: .95rem; }
    .cal-conf-botoes { display: flex; gap: 10px; justify-content: center; }
    .cal-conf-botoes .cal-btn { min-width: 130px; justify-content: center; }
    .cal-btn.perigo-forte { background: #d92d20; color: #fff; }
    .cal-btn.perigo-forte:hover { background: #b42318; }
    .cal-btn.azul { background: var(--f1-dark); color: #fff; }
    @keyframes calFade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes calSobe { from { transform: translateY(12px); opacity: 0; } to { transform: none; opacity: 1; } }
    /* modal de edição */
    .cal-modal { position: fixed; inset: 0; background: rgba(17,16,29,.55); z-index: 2000; display: none; align-items: flex-start; justify-content: center; padding: 40px 16px; overflow-y: auto; }
    .cal-modal.on { display: flex; }
    .cal-modal-caixa { background: #fff; border-radius: 14px; width: 100%; max-width: 1150px; box-shadow: 0 20px 60px rgba(0,0,0,.35); border-top: 5px solid var(--f1-red); }
    .cal-modal-caixa.sprint { border-top-color: #e0b400; }
    .cal-modal-topo { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--linha); }
    .cal-modal-topo h2 { margin: 0; font-size: 1.2rem; font-weight: 900; text-transform: uppercase; }
    .cal-modal-fechar { border: 0; background: #eef0f3; width: 36px; height: 36px; border-radius: 50%; font-size: 22px; line-height: 34px; cursor: pointer; }
    .cal-modal-fechar:hover { background: #fde2e1; color: #b42318; }
    .cal-modal .cal-ajuda { display: none; }
    #formEtapa.cal-modo-sprint .cal-pc { display: none !important; }
    #formEtapa.cal-modo-sprint .cal-sprint-box { display: grid; border: 0; background: transparent; padding: 0; }
    #formEtapa.cal-modo-sprint .cal-sprint-box h3 { display: none; }
    /* editando a corrida: os campos da sprint só aparecem quando a sprint é marcada ali */
    #formEtapa.cal-modo-corrida:not(.cal-mostrar-sprint) .cal-sprint-box { display: none !important; }
    .cal-aviso-sprint { display: none; font-weight: 700; color: #b42318; background: #fde2e1; padding: 6px 12px; border-radius: 8px; font-size: .9rem; }
    .cal-aviso-sprint.on { display: inline-flex; align-items: center; gap: 6px; }
    body.cal-sem-scroll { overflow: hidden; }
    /* sugestões ao digitar (lista própria da página, igual em qualquer navegador) */
    .cal-sug { position: fixed; z-index: 3000; background: #fff; border: 1px solid #d6d6d6; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,.18); max-height: 280px; overflow-y: auto; display: none; font-family: 'Titillium Web', sans-serif; }
    .cal-sug.on { display: block; }
    .cal-sug div { padding: 8px 12px; cursor: pointer; font-size: .98rem; color: #15151e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cal-sug div b { color: #e10600; }
    .cal-sug div.ativo, .cal-sug div:hover { background: #fff1f0; }
    .cal-status { font-size: .85rem; font-weight: 700; color: var(--ink-3); }
    .cal-status.ok { color: #166534; }
    .cal-status.erro { color: #b42318; }
    @media (max-width: 1200px) { .cal-form { grid-template-columns: repeat(3, minmax(0,1fr)); } .cal-sprint-box { grid-template-columns: repeat(2, minmax(0,1fr)); } }
    @media (max-width: 700px) { .cal-form, .cal-sprint-box.on { grid-template-columns: 1fr; } .cal-filtros .cal-campo input, .cal-filtros .cal-campo select { width: 100%; } }
</style>

<div class="res-page cal-page">
    @if (session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Calendário</li>
        </ol>
    </nav>

    {{-- MELHORIA 20261119: topo enxuto — nome da temporada, seletor e ações com cores por função --}}
    <header class="res-hero cal-hero">
        <div class="cal-hero-titulo">
            <small class="res-kicker">Calendário</small>
            <h1 id="calNomeHero">{{ $temporada->nome ?? 'Nenhuma temporada' }}</h1>
        </div>
        <div class="cal-hero-acoes">
            @if($temporadas->count())
                {{-- id na lista de exceções do select2 global (layouts/main) --}}
                <select id="calSelTemporada" aria-label="Trocar de temporada" title="Trocar de temporada">
                    @foreach($temporadas as $t)
                        <option value="{{ route('calendario.index', [$t->id]) }}" @selected($temporada && $temporada->id === $t->id)>{{ $t->nome }}</option>
                    @endforeach
                </select>
            @endif
            <a href="#" class="cal-tbtn criar" data-painel="painelNova"><i class="bi bi-plus-lg"></i> Nova</a>
            @if($temporada)
                <a href="#" class="cal-tbtn copiar" data-painel="painelDuplicar"><i class="bi bi-files"></i> Duplicar</a>
                <a href="#" class="cal-tbtn editar" data-painel="painelRenomear"><i class="bi bi-pencil"></i> Renomear</a>
            @endif
            @if($temporadas->count())
                <a href="{{ route('calendario.exportar') }}" class="cal-tbtn excel" title="Backup: todas as temporadas, uma aba por temporada"><i class="bi bi-file-earmark-excel-fill"></i> Excel</a>
            @endif
            @if($temporada)
                <a href="#" class="cal-tbtn excluir" data-painel="painelExcluir" title="Excluir esta temporada"><i class="bi bi-trash3"></i></a>
            @endif
        </div>
    </header>

    {{-- painéis de temporada --}}
    <div class="res-card cal-painel {{ $temporada ? '' : 'on' }}" id="painelNova">
        <form method="POST" action="{{ route('calendario.temporada.store') }}">
            @csrf
            <div class="cal-campo" style="min-width:320px;">
                <label for="novaNome">Nome da nova temporada</label>
                <input type="text" id="novaNome" name="nome" maxlength="120" required placeholder="ex.: 68ª Temporada (2027)">
            </div>
            <button class="cal-btn prim" type="submit"><i class="bi bi-plus-lg"></i> Criar temporada vazia</button>
        </form>
    </div>
    @if($temporada)
    <div class="res-card cal-painel" id="painelDuplicar">
        <form method="POST" action="{{ route('calendario.temporada.store') }}">
            @csrf
            <div class="cal-campo">
                <label for="calDuplicarDe">Copiar etapas de</label>
                <select id="calDuplicarDe" name="duplicar_de">
                    @foreach($temporadas as $t)
                        <option value="{{ $t->id }}" @selected($temporada->id === $t->id)>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="cal-campo" style="min-width:300px;">
                <label for="dupNome">Nome da nova temporada</label>
                <input type="text" id="dupNome" name="nome" maxlength="120" required placeholder="ex.: 68ª Temporada (2027)">
            </div>
            <label class="cal-switch" style="background:#f3f4f6;border-color:#ddd;color:var(--ink-1);">
                <input type="checkbox" name="copiar_anotacoes" value="1" checked> Copiar anotações
            </label>
            <button class="cal-btn prim" type="submit"><i class="bi bi-files"></i> Duplicar</button>
        </form>
    </div>
    <div class="res-card cal-painel" id="painelRenomear">
        <form id="formRenomear">
            <div class="cal-campo" style="min-width:320px;">
                <label for="renNome">Nome da temporada</label>
                <input type="text" id="renNome" maxlength="120" required value="{{ $temporada->nome }}">
            </div>
            <button class="cal-btn prim" type="submit"><i class="bi bi-check2"></i> Salvar nome</button>
        </form>
    </div>
    <div class="res-card cal-painel" id="painelExcluir">
        <form method="POST" action="{{ route('calendario.temporada.delete', [$temporada->id]) }}">
            @csrf
            <div style="font-weight:700;">Excluir <b>{{ $temporada->nome }}</b> com todas as etapas e anotações? Isso não pode ser desfeito.</div>
            <button class="cal-btn perigo" type="submit"><i class="bi bi-trash"></i> Sim, excluir</button>
            <button class="cal-btn sec" type="button" data-fechar><i class="bi bi-x"></i> Cancelar</button>
        </form>
    </div>
    @endif

    @if($temporada)
    <div class="res-kpis">
        <div class="res-kpi"><span><i class="bi bi-flag-fill"></i> Etapas</span><strong id="kpiEtapas">0</strong></div>
        <div class="res-kpi"><span><i class="bi bi-lightning-charge-fill"></i> Sprints</span><strong id="kpiSprints">0</strong></div>
        <div class="res-kpi"><span><i class="bi bi-stopwatch"></i> Largadas</span><strong id="kpiLargadas">0</strong><small>corridas + sprints</small></div>
        <div class="res-kpi" id="kpiContinentesCard"><span><i class="bi bi-globe2"></i> Continentes</span><strong id="kpiContinentes">0</strong></div>
        <div class="res-kpi"><span><i class="bi bi-geo-alt-fill"></i> Sedes</span><strong id="kpiSedes">0</strong></div>
    </div>

    {{-- FORMULÁRIO --}}
    <div class="res-card">
        <div class="cal-titulo-card">
            <h2 id="tituloForm">Nova etapa</h2>
            <span class="cal-editando" id="avisoEditando"><i class="bi bi-pencil-square"></i> Editando a etapa <span id="avisoEditandoNum"></span></span>
        </div>
        <div id="formCasa">
        <form id="formEtapa" class="cal-form" autocomplete="off">
            <input type="hidden" id="f_id">
            <div class="cal-campo cal-pc"><label for="f_posicao">Ordem *</label><input type="number" min="1" step="1" id="f_posicao" required></div>
            <div class="cal-campo cal-pc"><label for="f_continente">Continente</label><input type="text" id="f_continente" data-sug="listaContinentes" maxlength="80"></div>
            <div class="cal-campo cal-pc"><label for="f_nome">Nome</label><input type="text" id="f_nome" data-sug="listaNomes" maxlength="150" placeholder="ex.: GP da Itália"></div>
            <div class="cal-campo cal-pc"><label for="f_sede">Sede (país)</label><input type="text" id="f_sede" data-sug="listaSedes" maxlength="100" placeholder="automático pelo nome"></div>
            <div class="cal-campo cal-pc"><label for="f_pista">Pista</label><input type="text" id="f_pista" data-sug="listaPistas" maxlength="150"></div>
            <div class="cal-campo cal-pc"><label for="f_tempo">Tempo</label><input type="text" id="f_tempo" maxlength="40" placeholder="1.21.846'"></div>
            <div class="cal-campo cal-pc"><label for="f_duracao">Minutos</label><input type="number" min="1" max="600" step="1" id="f_duracao" value="30"></div>
            <div class="cal-campo cal-pc"><label for="f_voltas">Voltas</label><input type="text" id="f_voltas" maxlength="40" placeholder="automático"></div>
            <div class="cal-calc cal-pc" id="calcCorrida"></div>
            <div class="cal-campo cal-obs cal-pc"><label for="f_observacoes">Observações</label><textarea id="f_observacoes" rows="2"></textarea></div>

            <div class="cal-acoes-form cal-pc">
                <label class="cal-switch"><input type="checkbox" id="f_flg_sprint"> <i class="bi bi-lightning-charge-fill"></i> Esta etapa tem Sprint</label>
                <span class="cal-aviso-sprint" id="avisoSprint"><i class="bi bi-exclamation-triangle-fill"></i> A sprint desta etapa será excluída ao salvar.</span>
            </div>
            <div class="cal-sprint-box" id="boxSprint">
                <h3><i class="bi bi-lightning-charge-fill"></i> Sprint — pista própria, mesmo número da etapa</h3>
                <div class="cal-campo"><label for="f_sprint_continente">Continente</label><input type="text" id="f_sprint_continente" data-sug="listaContinentes" maxlength="80" placeholder="mesmo da etapa"></div>
                <div class="cal-campo"><label for="f_sprint_nome">Nome</label><input type="text" id="f_sprint_nome" data-sug="listaNomes" maxlength="150" placeholder="ex.: Sprint da Coreia"></div>
                <div class="cal-campo"><label for="f_sprint_sede">Sede (país)</label><input type="text" id="f_sprint_sede" data-sug="listaSedes" maxlength="100" placeholder="automático pelo nome"></div>
                <div class="cal-campo"><label for="f_sprint_pista">Pista</label><input type="text" id="f_sprint_pista" data-sug="listaPistas" maxlength="150"></div>
                <div class="cal-campo"><label for="f_sprint_tempo">Tempo</label><input type="text" id="f_sprint_tempo" maxlength="40"></div>
                <div class="cal-campo"><label for="f_sprint_duracao">Minutos</label><input type="number" min="1" max="600" step="1" id="f_sprint_duracao" value="10"></div>
                <div class="cal-campo"><label for="f_sprint_voltas">Voltas</label><input type="text" id="f_sprint_voltas" maxlength="40" placeholder="automático"></div>
                <div class="cal-calc" id="calcSprint"></div>
                <div class="cal-campo cal-obs"><label for="f_sprint_observacoes">Observações da sprint</label><textarea id="f_sprint_observacoes" rows="2"></textarea></div>
            </div>

            <div class="cal-acoes-form">
                <button class="cal-btn prim" type="submit" id="btnSalvar"><i class="bi bi-plus-lg"></i> Adicionar etapa</button>
                <button class="cal-btn sec" type="button" id="btnLimpar"><i class="bi bi-eraser"></i> Limpar / cancelar edição</button>
                <span class="res-muted cal-ajuda">Só a ordem é obrigatória; os outros campos podem ficar vazios. Voltas são calculadas pelo tempo e duração (corrida comum +4s) e podem ser alteradas. Pode usar qualquer número (ex.: 11): os números que sobrarem ficam vagos. Se o número já estiver ocupado, as etapas a partir dele andam uma casa.</span>
            </div>
            <div class="cal-msg" id="msgForm"></div>
        </form>
        </div>
        <datalist id="listaContinentes"></datalist><datalist id="listaNomes"></datalist><datalist id="listaSedes"></datalist><datalist id="listaPistas"></datalist>
    </div>

    {{-- GRID --}}
    <div class="res-card" style="margin-top:18px;">
        <div class="cal-titulo-card"><h2>Calendário</h2><span class="res-muted">Mude o número na coluna Nº para mover a etapa (números vagos são permitidos). Clique numa célula para editar ou apagar só aquele campo.</span></div>
        <div class="cal-filtros">
            <div class="cal-campo"><label for="calBusca">Pesquisar</label><input type="text" id="calBusca" placeholder="Nome, sede, pista, observação..."></div>
            <div class="cal-campo"><label for="calFiltroContinente">Continente</label><select id="calFiltroContinente"><option value="">Todos</option></select></div>
            <div class="cal-campo"><label for="calFiltroTipo">Tipo</label>
                <select id="calFiltroTipo">
                    <option value="">Todas as etapas</option>
                    <option value="com">Com sprint</option>
                    <option value="sem">Sem sprint</option>
                </select>
            </div>
            <button class="cal-btn sec" type="button" id="btnLimparFiltros"><i class="bi bi-x-circle"></i> Limpar filtros</button>
            <div class="cal-gerar" title="Cria etapas vazias, só com a ordem, depois da última">
                <div class="cal-campo"><label for="calQtdGerar">Gerar etapas</label><input type="number" id="calQtdGerar" min="1" max="50" step="1" value="10"></div>
                <button class="cal-btn prim" type="button" id="btnGerar"><i class="bi bi-magic"></i> Gerar</button>
            </div>
            <button class="cal-btn sec" type="button" id="btnFecharBuracos" style="display:none;" title="Renumera 1, 2, 3... mantendo a ordem atual"><i class="bi bi-sort-numeric-down"></i> Fechar buracos</button>
            <span class="cal-contador" id="calContador"></span>
        </div>
        <div class="cal-msg" id="msgGrid" style="margin-top:12px;"></div>
        <div class="res-tabela-wrap">
            <table class="res-tabela" id="tabelaCalendario">
                <thead>
                    <tr>
                        <th>Nº</th><th>Tipo</th><th class="esq">Continente</th><th class="esq">Nome</th><th class="esq">Sede</th>
                        <th class="esq">Pista</th><th>Tempo</th><th>Voltas</th><th class="esq">Observações</th><th>Ações</th>
                    </tr>
                </thead>
                <tbody id="corpoCalendario"></tbody>
            </table>
        </div>
    </div>

    {{-- ANOTAÇÕES --}}
    <div class="res-card" style="margin-top:18px;">
        <div class="cal-titulo-card"><h2>Anotações</h2><span class="cal-status" id="statusAnot">Salva sozinha enquanto você digita</span></div>
        <div class="cal-anot">
            <textarea id="anotacoes" placeholder="Próximas temporadas, testes oficiais, pistas alternativas, pneus...">{{ $temporada->anotacoes }}</textarea>
        </div>
    </div>
    {{-- MODAL DE CONFIRMAÇÃO (excluir etapa / fechar buracos) --}}
    <div class="cal-conf" id="calConf" aria-hidden="true">
        <div class="cal-conf-caixa" role="alertdialog" aria-modal="true" aria-labelledby="calConfTitulo" aria-describedby="calConfTexto">
            <div class="cal-conf-icone" id="calConfIcone"><i class="bi bi-trash3"></i></div>
            <h3 id="calConfTitulo">Excluir etapa?</h3>
            <div class="cal-conf-alvo" id="calConfAlvo"></div>
            <p id="calConfTexto"></p>
            <div class="cal-conf-botoes">
                <button type="button" class="cal-btn sec" id="calConfNao">Cancelar</button>
                <button type="button" class="cal-btn perigo-forte" id="calConfSim"><i class="bi bi-trash3"></i> Excluir</button>
            </div>
        </div>
    </div>

    {{-- MODAL DE EDIÇÃO (corrida ou sprint) — o formulário é movido para cá enquanto edita --}}
    <div class="cal-modal" id="calModal" aria-hidden="true">
        <div class="cal-modal-caixa" role="dialog" aria-modal="true" aria-labelledby="calModalTitulo">
            <div class="cal-modal-topo">
                <h2 id="calModalTitulo">Editar</h2>
                <button type="button" class="cal-modal-fechar" id="calModalFechar" title="Fechar (Esc)">&times;</button>
            </div>
            <div class="cal-modal-corpo" id="calModalCorpo"></div>
        </div>
    </div>

    {{-- MELHORIA 20261126: MODAL "CADASTRAR OFICIALMENTE" — mesmo formulário de corridas/adicionar, pré-preenchido --}}
    <div class="cal-modal" id="calOficial" aria-hidden="true">
        <div class="cal-modal-caixa" role="dialog" aria-modal="true" aria-labelledby="calOficialTitulo">
            <div class="cal-modal-topo">
                <h2 id="calOficialTitulo">Cadastrar oficialmente</h2>
                <button type="button" class="cal-modal-fechar" data-of-fechar title="Fechar (Esc)">&times;</button>
            </div>
            <div class="cal-of-carregando" id="ofCarregando">Procurando temporada, evento e pista...</div>
            <form id="formOficial" autocomplete="off" hidden novalidate>
                <div class="cal-campo" data-of="temporada">
                    <label for="of_temporada">Temporada</label>
                    <select id="of_temporada"></select>
                    <span class="cal-of-status"></span>
                </div>
                <div class="cal-campo" data-of="evento">
                    <label for="of_evento">Evento</label>
                    <select id="of_evento"></select>
                    <span class="cal-of-status"></span>
                </div>
                <div class="cal-campo" data-of="pista">
                    <label for="of_pista">Pista</label>
                    <select id="of_pista"></select>
                    <span class="cal-of-status"></span>
                </div>
                <div class="cal-campo">
                    <label for="of_ordem">Ordem</label>
                    <input type="number" id="of_ordem" min="1" max="999" step="1">
                </div>
                <label class="cal-switch"><input type="checkbox" id="of_sprint"> <i class="bi bi-lightning-charge-fill"></i> Corrida Sprint</label>
                <div class="cal-of-aviso" id="ofRepetidas" hidden></div>
                <div class="cal-msg" id="msgOficial"></div>
                <div class="cal-acoes-form">
                    <button class="cal-btn prim" type="submit" id="ofSalvar"><i class="bi bi-check2"></i> Salvar</button>
                    <button class="cal-btn sec" type="button" data-of-fechar><i class="bi bi-x-lg"></i> Cancelar</button>
                </div>
            </form>
            <div class="cal-of-pronto" id="ofPronto" hidden>
                <i class="bi bi-check-circle-fill"></i>
                <p id="ofProntoTexto"></p>
                <div class="cal-acoes-form">
                    <a class="cal-btn prim" id="ofProntoLink" href="#"><i class="bi bi-flag-fill"></i> Ver corridas da temporada</a>
                    <button class="cal-btn sec" type="button" data-of-fechar>Fechar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
(function () {
    // painéis do topo (nova / duplicar / renomear / excluir)
    document.querySelectorAll('[data-painel]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var alvo = document.getElementById(a.dataset.painel);
            var abrir = !alvo.classList.contains('on');
            document.querySelectorAll('.cal-painel').forEach(function (p) { p.classList.remove('on'); });
            if (abrir) { alvo.classList.add('on'); var i = alvo.querySelector('input[type=text]'); if (i) i.focus(); }
        });
    });
    document.querySelectorAll('[data-fechar]').forEach(function (b) {
        b.addEventListener('click', function () { b.closest('.cal-painel').classList.remove('on'); });
    });
    var sel = document.getElementById('calSelTemporada');
    if (sel) sel.addEventListener('change', function () { window.location = sel.value; });
})();
</script>

@if($temporada)
<script>
(function () {
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var URL_SALVAR = @js(route('calendario.etapa.salvar', [$temporada->id]));
    var URL_MOVER = @js(route('calendario.etapa.mover', ['__ID__']));
    var URL_EXCLUIR = @js(route('calendario.etapa.delete', ['__ID__']));
    var URL_TEMPORADA = @js(route('calendario.temporada.update', [$temporada->id]));
    var URL_RENUMERAR = @js(route('calendario.temporada.renumerar', [$temporada->id]));
    var URL_GERAR = @js(route('calendario.etapa.gerar', [$temporada->id]));
    var URL_OFICIAL = @js(route('calendario.etapa.oficial', ['__ID__']));
    var etapas = @json($etapas);
    var SUG = @json($sugestoes); // valores já digitados em todos os calendários (autocompletar)

    var CAMPOS = ['continente','nome','sede','pista','tempo','voltas','observacoes',
                  'sprint_nome','sprint_continente','sprint_sede','sprint_pista','sprint_tempo','sprint_voltas','sprint_observacoes'];

    // cores por continente (os demais textos livres recebem uma cor fixa calculada pelo nome)
    var CORES = {
        'europa': ['#dbeafe','#1e40af'], 'asia': ['#ffedd5','#9a3412'], 'africa': ['#fef3c7','#92400e'],
        'america do norte': ['#fee2e2','#991b1b'], 'america do sul': ['#dcfce7','#166534'],
        'oceania': ['#cffafe','#155e75'], 'ficticio': ['#ede9fe','#5b21b6']
    };
    var EXTRAS = [['#fce7f3','#9d174d'],['#e0e7ff','#3730a3'],['#ccfbf1','#115e59'],['#f1f5f9','#334155'],['#fae8ff','#86198f']];
    function semAcento(t) { return (t || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim(); }
    function corContinente(c) {
        var k = semAcento(c);
        if (CORES[k]) return CORES[k];
        var h = 0; for (var i = 0; i < k.length; i++) h = (h * 31 + k.charCodeAt(i)) >>> 0;
        return EXTRAS[h % EXTRAS.length];
    }
    function esc(t) { return (t === null || t === undefined ? '' : String(t)).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    // MELHORIA 20261119: sede automática = só o país citado no nome ("GP da Itália" -> Itália, "Sprint Brasil" -> Brasil).
    // Sem país conhecido: tira "GP", "Grande Prêmio", "Sprint", "Corrida" e "da/do/de..." do começo.
    // Mesma regra do servidor (App\Support\PaisesCalendario::sede).
    function sedeDoNome(nome) {
        nome = (nome || '').trim(); if (!nome) return '';
        var achou = encontrarPais(nome); if (achou) return achou[1];
        var resto = nome.replace(/^\s*(g\.?\s?p\.?|grande\s+pr[eê]mio|sprint|corrida)(?=[\s\-:]|$)/i, '');
        resto = resto.replace(/^[\s\-\u2013:\u00B7]+/, '').replace(/^(da|do|de|dos|das|di|del|of|the)\s+/i, '');
        return resto.trim();
    }
    function chip(c) { if (!c) return '<span class="cal-vazio-cel">-</span>'; var k = corContinente(c); return '<span class="cal-cont" style="background:' + k[0] + ';color:' + k[1] + '">' + esc(c) + '</span>'; }
    function quebra(t) { return val(t, true); }
    // MELHORIA 20261119: campo sem informação aparece como "-"
    function val(t, linhas) {
        if (t === null || t === undefined || String(t).trim() === '') return '<span class="cal-vazio-cel">-</span>';
        var s = esc(t); return linhas ? s.replace(/\n/g, '<br>') : s;
    }
    // mesma conta do script de voltas: (minutos*60) / tempo da volta, arredonda para baixo e tira 1
    function tempoEmSegundos(t) {
        t = (t || '').replace(/['" ]/g, '');
        var p = t.split('.'); if (p.length !== 3 || p.some(function (x) { return !/^\d+$/.test(x); })) return null;
        return parseInt(p[0], 10) * 60 + parseInt(p[1], 10) + parseInt(p[2], 10) / 1000;
    }
    function formatarTempo(s) {
        var min = Math.floor(s / 60), resto = s - min * 60, seg = Math.floor(resto), ms = Math.round((resto - seg) * 1000);
        if (ms === 1000) { ms = 0; seg++; } if (seg === 60) { seg = 0; min++; }
        return min + '.' + String(seg).padStart(2, '0') + '.' + String(ms).padStart(3, '0') + "'";
    }
    function calcularVoltas(tempo, minutos, extra) {
        var s = tempoEmSegundos(tempo); minutos = parseInt(minutos, 10);
        if (s === null || !minutos) return null;
        s += extra || 0; if (s <= 0) return null;
        return { voltas: Math.max(0, Math.floor((minutos * 60) / s) - 1), tempoUsado: formatarTempo(s) };
    }
    var EXTRA_CORRIDA = 4;

    // ---------- BANDEIRAS (flagcdn.com) ----------
    // MELHORIA 20261119: todos os países e territórios do mundo (ISO 3166-1, nomes em português e inglês,
    // com e sem acento) + Inglaterra, Escócia, País de Gales, Irlanda do Norte e Kosovo. Formato "código:nome|outro nome".
    // Sem país real: bandeira quadriculada genérica.
    var LISTA_PAISES = @json(\App\Support\PaisesCalendario::LISTA); // mesma lista do servidor
    function normPais(t) { return semAcento(t).replace(/[-'’]/g, ' ').replace(/\s+/g, ' ').trim(); }
    var PAISES = {}; // nome normalizado -> [código, nome para exibir]
    LISTA_PAISES.forEach(function (linha) {
        var p = linha.indexOf(':'), cod = linha.slice(0, p), nomes = linha.slice(p + 1).split('|'), oficial = nomes[0].replace(/^\*/, '');
        nomes.forEach(function (nome) {
            var cidade = nome.charAt(0) === '*'; nome = nome.replace(/^\*/, '');
            var k = normPais(nome); if (!PAISES[k]) PAISES[k] = [cod, cidade ? oficial : nome]; // o primeiro escrito vale
        });
    });
    var NOMES_PAISES = Object.keys(PAISES).sort(function (a, b) { return b.length - a.length; }); // mais longo primeiro
    var BANDEIRA_GENERICA = 'data:image/svg+xml;utf8,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 4"><rect width="6" height="4" fill="#fff"/>' +
        '<path fill="#15151e" d="M0 0h1v1H0zM2 0h1v1H2zM4 0h1v1H4zM1 1h1v1H1zM3 1h1v1H3zM5 1h1v1H5zM0 2h1v1H0zM2 2h1v1H2zM4 2h1v1H4zM1 3h1v1H1zM3 3h1v1H3zM5 3h1v1H5z"/></svg>');
    function encontrarPais(texto) {
        var k = normPais(texto);
        if (!k) return null;
        if (PAISES[k]) return PAISES[k];
        var partes = k.split('/');                       // ex.: "Suecia/Ficticia"
        for (var i = 0; i < partes.length; i++) { var p = partes[i].trim(); if (PAISES[p]) return PAISES[p]; }
        var frase = ' ' + k.replace(/[^a-z0-9 ]/g, ' ').replace(/\s+/g, ' ').trim() + ' ';
        for (var j = 0; j < NOMES_PAISES.length; j++) { // país citado dentro do texto
            var n = NOMES_PAISES[j]; if (n.length < 4) continue;
            if (frase.indexOf(' ' + n + ' ') >= 0) return PAISES[n];
        }
        return null;
    }
    function codigoPais(texto) { var a = encontrarPais(texto); return a ? a[0] : null; }
    function bandeira(sede, nome) {
        if (!(nome || '').trim() && !(sede || '').trim()) return '';
        var cod = codigoPais(sede) || codigoPais(nome);
        var src = cod ? 'https://flagcdn.com/w40/' + cod + '.png' : BANDEIRA_GENERICA;
        return '<img class="cal-band" data-imagem-padrao="1" loading="lazy" alt="" title="' + esc(cod ? (sede || nome) : 'Sem país definido') + '" src="' + src + '"' +
            ' onerror="this.onerror=null;this.src=\'' + BANDEIRA_GENERICA.replace(/'/g, '%27') + '\'">';
    }

    function mensagem(id, texto, tipo) {
        var el = document.getElementById(id);
        el.className = 'cal-msg on ' + (tipo || 'erro'); el.textContent = texto;
        clearTimeout(el._t); el._t = setTimeout(function () { el.className = 'cal-msg'; }, tipo === 'ok' ? 2500 : 6000);
    }

    function enviar(url, dados) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(dados || {})
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) {
                if (!r.ok) {
                    var msg = j.message || ('Erro ' + r.status);
                    if (j.errors) { msg = Object.values(j.errors).map(function (v) { return v[0]; }).join(' '); }
                    if (r.status === 419) msg = 'Sessão expirada. Recarregue a página.';
                    throw new Error(msg);
                }
                return j;
            });
        });
    }

    // ---------- GRID ----------
    function linhaCorrida(e) {
        return '<tr class="cal-corrida" data-id="' + e.id + '">' +
            '<td class="nowrap"><input type="number" min="1" class="cal-num" value="' + e.ordem + '" data-id="' + e.id + '" data-atual="' + e.ordem + '" title="Digite a nova posição e tecle Enter"></td>' +
            '<td><span class="cal-tipo corrida">Corrida</span></td>' +
            '<td class="esq" data-campo="continente">' + chip(e.continente) + '</td>' +
            '<td class="esq cal-nome" data-campo="nome">' + bandeira(e.sede_exibicao, e.nome) + val(e.nome) + '</td>' +
            '<td class="esq" data-campo="sede">' + val(e.sede_exibicao) + '</td>' +
            '<td class="esq" data-campo="pista">' + val(e.pista) + '</td>' +
            '<td class="nowrap" data-campo="tempo">' + val(e.tempo) + '</td>' +
            '<td data-campo="voltas">' + val(e.voltas) + '</td>' +
            '<td class="esq cal-obs-cel" data-campo="observacoes">' + quebra(e.observacoes) + '</td>' +
            '<td class="nowrap"><button type="button" class="cal-acao oficial" data-oficial="' + e.id + '" data-parte="corrida" title="Cadastrar oficialmente a corrida"><i class="bi bi-plus-square"></i></button>' +
            '<button type="button" class="cal-acao" data-editar="' + e.id + '" data-parte="corrida" title="Editar corrida"><i class="bi bi-pencil-square"></i></button>' +
            '<button type="button" class="cal-acao del" data-excluir="' + e.id + '" title="Excluir"><i class="bi bi-trash"></i></button></td></tr>';
    }
    function linhaSprint(e) {
        return '<tr class="cal-sprint" data-id="' + e.id + '">' +
            '<td class="nowrap"><span class="cal-num-sprint">' + e.ordem + '</span></td>' +
            '<td><span class="cal-tipo sprint">Sprint</span></td>' +
            '<td class="esq" data-campo="sprint_continente">' + chip(e.sprint_continente || e.continente) + '</td>' +
            '<td class="esq cal-nome" data-campo="sprint_nome">' + bandeira(e.sprint_sede_exibicao, e.sprint_nome) + val(e.sprint_nome) + '</td>' +
            '<td class="esq" data-campo="sprint_sede">' + val(e.sprint_sede_exibicao) + '</td>' +
            '<td class="esq" data-campo="sprint_pista">' + val(e.sprint_pista) + '</td>' +
            '<td class="nowrap" data-campo="sprint_tempo">' + val(e.sprint_tempo) + '</td>' +
            '<td data-campo="sprint_voltas">' + val(e.sprint_voltas) + '</td>' +
            '<td class="esq cal-obs-cel" data-campo="sprint_observacoes">' + quebra(e.sprint_observacoes) + '</td>' +
            '<td class="nowrap"><button type="button" class="cal-acao oficial" data-oficial="' + e.id + '" data-parte="sprint" title="Cadastrar oficialmente a sprint"><i class="bi bi-plus-square"></i></button>' +
            '<button type="button" class="cal-acao" data-editar="' + e.id + '" data-parte="sprint" title="Editar sprint"><i class="bi bi-pencil-square"></i></button>' +
            '<button type="button" class="cal-acao del" data-excluir-sprint="' + e.id + '" title="Excluir só a sprint"><i class="bi bi-trash"></i></button></td></tr>';
    }

    function continentesDe(e) {
        var l = []; if (e.continente) l.push(e.continente);
        if (e.flg_sprint === 'S' && e.sprint_continente) l.push(e.sprint_continente);
        return l;
    }

    function passaFiltro(e) {
        var busca = semAcento(document.getElementById('calBusca').value);
        var cont = document.getElementById('calFiltroContinente').value;
        var tipo = document.getElementById('calFiltroTipo').value;
        if (tipo === 'com' && e.flg_sprint !== 'S') return false;
        if (tipo === 'sem' && e.flg_sprint === 'S') return false;
        if (cont && continentesDe(e).map(semAcento).indexOf(semAcento(cont)) < 0) return false;
        if (busca) {
            var texto = semAcento(CAMPOS.map(function (c) { return e[c]; }).join(' ') + ' ' + e.sede_exibicao + ' ' + (e.sprint_sede_exibicao || ''));
            if (texto.indexOf(busca) < 0) return false;
        }
        return true;
    }

    // autocompletar: junta o que veio do servidor com o que está na tela agora
    function preencherLista(id, base, campos) {
        var mapa = {};
        (base || []).forEach(function (t) { mapa[semAcento(t)] = t; });
        etapas.forEach(function (e) { campos.forEach(function (c) { var t = (e[c] || '').trim(); if (t && !mapa[semAcento(t)]) mapa[semAcento(t)] = t; }); });
        var lista = Object.keys(mapa).map(function (k) { return mapa[k]; }).sort(function (a, b) { return a.localeCompare(b, 'pt-BR'); });
        document.getElementById(id).innerHTML = lista.map(function (t) { return '<option value="' + esc(t) + '">'; }).join('');
    }

    function atualizarListas() {
        var conts = {};
        etapas.forEach(function (e) { continentesDe(e).forEach(function (c) { conts[semAcento(c)] = conts[semAcento(c)] || c; }); });
        Object.keys(CORES).forEach(function (k) { if (!conts[k]) conts[k] = null; });
        var dl = document.getElementById('listaContinentes');
        var sugestoes = ['Europa','Asia','Africa','America do Norte','America do Sul','Ficticio'];
        Object.keys(conts).forEach(function (k) { if (conts[k] && sugestoes.map(semAcento).indexOf(k) < 0) sugestoes.push(conts[k]); });
        (SUG.continentes || []).forEach(function (c) { if (sugestoes.map(semAcento).indexOf(semAcento(c)) < 0) sugestoes.push(c); });
        dl.innerHTML = sugestoes.map(function (c) { return '<option value="' + esc(c) + '">'; }).join('');
        preencherLista('listaNomes', SUG.nomes, ['nome', 'sprint_nome']);
        preencherLista('listaSedes', SUG.sedes, ['sede_exibicao', 'sprint_sede_exibicao']);
        preencherLista('listaPistas', SUG.pistas, ['pista', 'sprint_pista']);

        var selC = document.getElementById('calFiltroContinente'), atual = selC.value;
        var usados = Object.keys(conts).filter(function (k) { return conts[k]; }).map(function (k) { return conts[k]; }).sort();
        selC.innerHTML = '<option value="">Todos</option>' + usados.map(function (c) { return '<option value="' + esc(c) + '">' + esc(c) + '</option>'; }).join('');
        if (usados.indexOf(atual) >= 0) selC.value = atual;

        var sprints = etapas.filter(function (e) { return e.flg_sprint === 'S'; }).length;
        var sedes = {};
        etapas.forEach(function (e) { if (e.sede_exibicao) sedes[semAcento(e.sede_exibicao)] = 1; if (e.flg_sprint === 'S' && e.sprint_sede_exibicao) sedes[semAcento(e.sprint_sede_exibicao)] = 1; });
        document.getElementById('kpiEtapas').textContent = etapas.length;
        document.getElementById('kpiSprints').textContent = sprints;
        document.getElementById('kpiLargadas').textContent = etapas.length + sprints;
        document.getElementById('kpiContinentes').textContent = usados.length;
        document.getElementById('kpiContinentesCard').title = usados.length ? usados.join(' · ') : ''; // lista ao passar o mouse
        document.getElementById('kpiSedes').textContent = Object.keys(sedes).length;
    }

    function desenhar() {
        var corpo = document.getElementById('corpoCalendario');
        var visiveis = etapas.filter(passaFiltro);
        if (!etapas.length) {
            corpo.innerHTML = '<tr><td colspan="10" class="cal-vazio"><i class="bi bi-calendar-plus"></i> Nenhuma etapa ainda. Use o formulário acima para adicionar a primeira.</td></tr>';
        } else if (!visiveis.length) {
            corpo.innerHTML = '<tr><td colspan="10" class="cal-vazio">Nenhuma etapa com esses filtros.</td></tr>';
        } else {
            corpo.innerHTML = visiveis.map(function (e) { return (e.flg_sprint === 'S' ? linhaSprint(e) : '') + linhaCorrida(e); }).join('');
        }
        var idEd = document.getElementById('f_id').value;
        if (idEd) corpo.querySelectorAll('tr[data-id="' + idEd + '"]').forEach(function (tr) { tr.classList.add('cal-sel'); });
        var maiorNum = etapas.reduce(function (m, e) { return Math.max(m, e.ordem); }, 0), usados = {}, vagos = [];
        etapas.forEach(function (e) { usados[e.ordem] = 1; });
        for (var n = 1; n <= maiorNum; n++) if (!usados[n]) vagos.push(n);
        document.getElementById('calContador').textContent = (visiveis.length === etapas.length
            ? etapas.length + ' etapa(s)' : visiveis.length + ' de ' + etapas.length + ' etapa(s)') +
            (vagos.length ? ' · nº vago(s): ' + vagos.join(', ') : '');
        document.getElementById('btnFecharBuracos').style.display = vagos.length ? '' : 'none';
        atualizarListas();
    }

    // mover (mesma lógica da planilha)
    function mover(input) {
        var nova = parseInt(input.value, 10), atual = parseInt(input.dataset.atual, 10);
        if (!nova || nova < 1) { input.value = atual; return; }
        if (nova === atual) return;
        input.disabled = true;
        enviar(URL_MOVER.replace('__ID__', input.dataset.id), { posicao: nova })
            .then(function (j) { etapas = j.etapas; desenhar(); mensagem('msgGrid', 'Etapa movida para o nº ' + nova + '.', 'ok'); })
            .catch(function (err) { input.disabled = false; input.value = atual; mensagem('msgGrid', err.message); });
    }
    var corpo = document.getElementById('corpoCalendario');
    corpo.addEventListener('keydown', function (e) {
        if (e.target.classList.contains('cal-num') && e.key === 'Enter') { e.preventDefault(); e.target.blur(); }
    });
    corpo.addEventListener('focusout', function (e) { if (e.target.classList.contains('cal-num')) mover(e.target); });
    // ---------- EDIÇÃO DIRETO NA GRID (apaga/troca só aquele campo, sem apagar a etapa) ----------
    var LISTAS = { nome: 'listaNomes', sprint_nome: 'listaNomes', sede: 'listaSedes', sprint_sede: 'listaSedes',
                   pista: 'listaPistas', sprint_pista: 'listaPistas', continente: 'listaContinentes', sprint_continente: 'listaContinentes' };
    function dadosDaEtapa(e) {
        var d = { id: e.id, posicao: e.ordem, flg_sprint: e.flg_sprint, duracao: e.duracao, sprint_duracao: e.sprint_duracao };
        CAMPOS.forEach(function (c) { d[c] = e[c] || ''; });
        return d;
    }
    function editarCelula(td) {
        if (td.querySelector('.cal-inline')) return;
        var id = parseInt(td.closest('tr').dataset.id, 10), campo = td.dataset.campo;
        var e = etapas.find(function (x) { return x.id === id; }); if (!e) return;
        var obs = campo.indexOf('observacoes') >= 0;
        var inp = document.createElement(obs ? 'textarea' : 'input');
        inp.className = 'cal-inline'; inp.value = e[campo] || '';
        if (!obs) { inp.type = 'text'; if (LISTAS[campo]) { inp.setAttribute('data-sug', LISTAS[campo]); ligarSugestoes(inp); } }
        if (campo === 'sede') inp.placeholder = 'automático: ' + (sedeDoNome(e.nome) || '-');
        if (campo === 'sprint_sede') inp.placeholder = 'automático: ' + (sedeDoNome(e.sprint_nome) || '-');
        if (campo === 'sprint_continente') inp.placeholder = 'mesmo da etapa';
        if (campo === 'voltas' || campo === 'sprint_voltas') inp.placeholder = 'automático';
        td.innerHTML = ''; td.appendChild(inp); inp.focus(); inp.select();
        var feito = false;
        function terminar(salvar) {
            if (feito) return; feito = true;
            var novo = inp.value.trim();
            if (!salvar || novo === (e[campo] || '').trim()) { desenhar(); return; }
            var d = dadosDaEtapa(e); d[campo] = novo;
            // nome mudou direto na grid: a sede volta a ser automática (tirada do novo nome)
            if (campo === 'nome') d.sede = '';
            if (campo === 'sprint_nome') d.sprint_sede = '';
            // tempo mudou: refaz as voltas (corrida comum +4s; sprint sem)
            if (campo === 'tempo' || campo === 'sprint_tempo') {
                var sp = campo === 'sprint_tempo';
                var r = calcularVoltas(novo, sp ? e.sprint_duracao : e.duracao, sp ? 0 : EXTRA_CORRIDA);
                d[sp ? 'sprint_voltas' : 'voltas'] = r ? String(r.voltas) : '';
            }
            td.style.opacity = .5;
            enviar(URL_SALVAR, d).then(function (j) {
                etapas = j.etapas; desenhar();
                var idForm = document.getElementById('f_id').value;
                if (idForm && parseInt(idForm, 10) === id) preencherForm(etapas.find(function (x) { return x.id === id; }));
                // alteração direto na grid: sem mensagem de confirmação (só erros aparecem)
            }).catch(function (err) { desenhar(); mensagem('msgGrid', err.message); });
        }
        inp.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') { ev.preventDefault(); terminar(false); }
            else if (ev.key === 'Enter' && !(obs && ev.shiftKey)) { ev.preventDefault(); terminar(true); }
        });
        inp.addEventListener('blur', function () { terminar(true); });
    }

    corpo.addEventListener('click', function (e) {
        var td = e.target.closest('td[data-campo]');
        if (td && !e.target.closest('.cal-inline')) { editarCelula(td); return; }
        var b = e.target.closest('button'); if (!b) return;
        if (b.dataset.editar) abrirModal(parseInt(b.dataset.editar, 10), b.dataset.parte);
        if (b.dataset.oficial) abrirOficial(parseInt(b.dataset.oficial, 10), b.dataset.parte);
        if (b.dataset.excluirSprint) {
            // MELHORIA 20261119: exclui só a sprint — a corrida da etapa e a numeração continuam iguais
            var es = etapas.find(function (x) { return x.id === parseInt(b.dataset.excluirSprint, 10); });
            if (!es) return;
            var alvoS = '<b>' + bandeira(es.sprint_sede_exibicao, es.sprint_nome) + 'Sprint da etapa ' + es.ordem + (es.sprint_nome ? ' · ' + esc(es.sprint_nome) : '') + '</b>' +
                (es.sprint_pista ? '<small>' + esc(es.sprint_pista) + '</small>' : '');
            confirmar({
                titulo: 'Excluir sprint?', alvo: alvoS, botao: 'Excluir sprint',
                texto: 'Os dados da sprint serão apagados. A corrida' + (es.nome ? ' (' + es.nome + ')' : '') + ' continua e a numeração não muda.'
            }).then(function (ok) {
                if (!ok) return;
                var d = dadosDaEtapa(es);
                d.flg_sprint = 'N'; d.sprint_duracao = 10;
                CAMPOS.forEach(function (c) { if (c.indexOf('sprint_') === 0) d[c] = ''; });
                enviar(URL_SALVAR, d)
                    .then(function (j) {
                        etapas = j.etapas;
                        var idForm = document.getElementById('f_id').value;
                        if (idForm && parseInt(idForm, 10) === es.id) preencherForm(etapas.find(function (x) { return x.id === es.id; }));
                        desenhar(); mensagem('msgGrid', 'Sprint da etapa ' + es.ordem + ' excluída.', 'ok');
                    })
                    .catch(function (err) { mensagem('msgGrid', err.message); });
            });
            return;
        }
        if (b.dataset.excluir) {
            var et = etapas.find(function (x) { return x.id === parseInt(b.dataset.excluir, 10); });
            if (!et) return;
            var alvo = '<b>' + bandeira(et.sede_exibicao, et.nome) + 'Etapa ' + et.ordem + (et.nome ? ' · ' + esc(et.nome) : '') + '</b>' +
                (et.pista ? '<small>' + esc(et.pista) + '</small>' : '') +
                (et.flg_sprint === 'S' ? '<small class="sprint"><i class="bi bi-lightning-charge-fill"></i> a sprint' + (et.sprint_nome ? ' (' + esc(et.sprint_nome) + ')' : '') + ' também será excluída</small>' : '');
            confirmar({
                titulo: 'Excluir etapa?', alvo: alvo, botao: 'Excluir',
                texto: 'As etapas depois dela descem 1 número. Essa ação não pode ser desfeita.'
            }).then(function (ok) {
                if (!ok) return;
                enviar(URL_EXCLUIR.replace('__ID__', et.id))
                    .then(function (j) { etapas = j.etapas; if (document.getElementById('f_id').value == et.id) limpar(); desenhar(); mensagem('msgGrid', 'Etapa excluída. As etapas depois dela desceram 1 número.', 'ok'); })
                    .catch(function (err) { mensagem('msgGrid', err.message); });
            });
        }
    });

    // ---------- MODAL DE CONFIRMAÇÃO (substitui o confirm() do navegador) ----------
    var conf = document.getElementById('calConf'), confResolver = null;
    function confirmar(op) {
        document.getElementById('calConfTitulo').textContent = op.titulo;
        document.getElementById('calConfAlvo').innerHTML = op.alvo || '';
        document.getElementById('calConfTexto').textContent = op.texto || '';
        var icone = document.getElementById('calConfIcone');
        icone.className = 'cal-conf-icone' + (op.perigo === false ? ' info' : '');
        icone.innerHTML = '<i class="bi ' + (op.icone || 'bi-trash3') + '"></i>';
        var sim = document.getElementById('calConfSim');
        sim.className = 'cal-btn ' + (op.perigo === false ? 'azul' : 'perigo-forte');
        sim.innerHTML = (op.perigo === false ? '' : '<i class="bi bi-trash3"></i> ') + esc(op.botao || 'Confirmar');
        conf.classList.add('on'); conf.setAttribute('aria-hidden', 'false');
        setTimeout(function () { document.getElementById('calConfNao').focus(); }, 20); // foco no Cancelar, por segurança
        return new Promise(function (resolve) { confResolver = resolve; });
    }
    function responder(ok) {
        if (!confResolver) return;
        conf.classList.remove('on'); conf.setAttribute('aria-hidden', 'true');
        var r = confResolver; confResolver = null; r(ok);
    }
    document.getElementById('calConfSim').addEventListener('click', function () { responder(true); });
    document.getElementById('calConfNao').addEventListener('click', function () { responder(false); });
    conf.addEventListener('mousedown', function (ev) { if (ev.target === conf) responder(false); });
    document.addEventListener('keydown', function (ev) {
        if (!conf.classList.contains('on')) return;
        if (ev.key === 'Escape') { ev.preventDefault(); ev.stopImmediatePropagation(); responder(false); }
    }, true);

    // ---------- FORMULÁRIO ----------
    var chkSprint = document.getElementById('f_flg_sprint');
    function mostrarSprint() { document.getElementById('boxSprint').classList.toggle('on', chkSprint.checked); }
    chkSprint.addEventListener('change', function () {
        mostrarSprint();
        // MELHORIA 20261119: no modal da corrida, marcar a sprint abre os campos dela; desmarcar avisa que ela será excluída
        if (typeof modalAberto === 'function' && modalAberto() && form.classList.contains('cal-modo-corrida')) {
            form.classList.toggle('cal-mostrar-sprint', chkSprint.checked);
            var tinha = etapaNoModal && etapaNoModal.flg_sprint === 'S';
            document.getElementById('avisoSprint').classList.toggle('on', tinha && !chkSprint.checked);
            if (chkSprint.checked) setTimeout(function () { document.getElementById('f_sprint_nome').focus(); }, 20);
        } else {
            var idF = parseInt(document.getElementById('f_id').value, 10);
            var et = idF ? etapas.find(function (x) { return x.id === idF; }) : null;
            document.getElementById('avisoSprint').classList.toggle('on', !!(et && et.flg_sprint === 'S' && !chkSprint.checked));
        }
    });
    var fNome = document.getElementById('f_nome'), fSede = document.getElementById('f_sede');
    var fSprintNome = document.getElementById('f_sprint_nome'), fSprintSede = document.getElementById('f_sprint_sede');
    function placeholderSede() {
        var s = sedeDoNome(fNome.value); fSede.placeholder = s ? 'automático: ' + s : 'automático pelo nome';
        var ss = sedeDoNome(fSprintNome.value); fSprintSede.placeholder = ss ? 'automático: ' + ss : 'automático pelo nome';
    }
    function sedeSegueNome(inpNome, inpSede) {
        var anterior = inpNome.value;
        inpNome.addEventListener('focus', function () { anterior = inpNome.value; });
        inpNome.addEventListener('input', function () {
            // MELHORIA 20261119: a sede é PREENCHIDA com o país tirado do nome (antes só aparecia como dica cinza).
            // Só não mexe quando você digitou outro país de propósito.
            var sede = inpSede.value.trim();
            if (!sede || !anterior.trim() || semAcento(sede) === semAcento(sedeDoNome(anterior))) {
                inpSede.value = sedeDoNome(inpNome.value);
                inpSede._semSug = true; inpSede.dispatchEvent(new Event('input', { bubbles: true })); inpSede._semSug = false;
            }
            anterior = inpNome.value;
            placeholderSede();
        });
    }
    sedeSegueNome(fNome, fSede);
    sedeSegueNome(fSprintNome, fSprintSede);

    // voltas automáticas (podem ser alteradas depois): corrida comum soma 4s ao tempo, sprint não
    function atualizarCalculo(sprint, preencher) {
        var p = sprint ? 'f_sprint_' : 'f_';
        var r = calcularVoltas(document.getElementById(p + 'tempo').value, document.getElementById(p + 'duracao').value, sprint ? 0 : EXTRA_CORRIDA);
        var info = document.getElementById(sprint ? 'calcSprint' : 'calcCorrida');
        if (!r) { info.innerHTML = document.getElementById(p + 'tempo').value.trim() ? 'Tempo fora do formato 1.21.846\' — voltas não calculadas.' : ''; return; }
        if (preencher) document.getElementById(p + 'voltas').value = r.voltas;
        info.innerHTML = 'Cálculo: ' + esc(document.getElementById(p + 'duracao').value) + ' min ÷ <b>' + esc(r.tempoUsado) + '</b>' +
            (sprint ? '' : ' (tempo +4s)') + ' − 1 volta = <b>' + r.voltas + ' voltas</b>';
    }
    ['tempo', 'duracao'].forEach(function (c) {
        document.getElementById('f_' + c).addEventListener('input', function () { atualizarCalculo(false, true); });
        document.getElementById('f_sprint_' + c).addEventListener('input', function () { atualizarCalculo(true, true); });
    });

    function limpar() {
        document.getElementById('f_id').value = '';
        document.getElementById('f_posicao').value = etapas.reduce(function (m, e) { return Math.max(m, e.ordem); }, 0) + 1; // sugere o próximo número
        CAMPOS.forEach(function (c) { document.getElementById('f_' + c).value = ''; });
        document.getElementById('f_duracao').value = 30; document.getElementById('f_sprint_duracao').value = 10;
        chkSprint.checked = false; mostrarSprint(); placeholderSede(); atualizarCalculo(false, false); atualizarCalculo(true, false); document.getElementById('avisoSprint').classList.remove('on');
        document.getElementById('tituloForm').textContent = 'Nova etapa';
        document.getElementById('avisoEditando').classList.remove('on');
        document.getElementById('btnSalvar').innerHTML = '<i class="bi bi-plus-lg"></i> Adicionar etapa';
        document.querySelectorAll('#corpoCalendario tr.cal-sel').forEach(function (tr) { tr.classList.remove('cal-sel'); });
        if (typeof atualizarX === 'function') atualizarX();
    }
    // ---------- MODAL DE EDIÇÃO: só a parte clicada (corrida ou sprint) ----------
    var form = document.getElementById('formEtapa'), modal = document.getElementById('calModal');
    function modalAberto() { return modal.classList.contains('on'); }
    var etapaNoModal = null;
    function abrirModal(id, parte) {
        var e = etapas.find(function (x) { return x.id === id; }); if (!e) return;
        etapaNoModal = e;
        var sprint = parte === 'sprint';
        preencherForm(e);
        if (sprint) chkSprint.checked = true;
        form.classList.add(sprint ? 'cal-modo-sprint' : 'cal-modo-corrida');
        document.getElementById('calModalCorpo').appendChild(form);
        document.querySelector('.cal-modal-caixa').classList.toggle('sprint', sprint);
        var nome = sprint ? (e.sprint_nome || e.nome) : e.nome;
        document.getElementById('calModalTitulo').textContent = (sprint ? 'Editar sprint' : 'Editar corrida') + ' — etapa ' + e.ordem + (nome ? ' · ' + nome : '');
        document.getElementById('btnLimpar').innerHTML = '<i class="bi bi-x-lg"></i> Cancelar';
        modal.classList.add('on'); modal.setAttribute('aria-hidden', 'false'); document.body.classList.add('cal-sem-scroll');
        desenhar();
        setTimeout(function () { document.getElementById(sprint ? 'f_sprint_nome' : 'f_nome').focus(); }, 30);
    }
    function fecharModal() {
        if (!modalAberto()) return;
        fecharSugestoes();
        form.classList.remove('cal-modo-sprint', 'cal-modo-corrida', 'cal-mostrar-sprint');
        document.getElementById('avisoSprint').classList.remove('on'); etapaNoModal = null;
        document.getElementById('formCasa').appendChild(form);
        modal.classList.remove('on'); modal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('cal-sem-scroll');
        document.getElementById('btnLimpar').innerHTML = '<i class="bi bi-eraser"></i> Limpar / cancelar edição';
        document.getElementById('msgForm').className = 'cal-msg';
        limpar(); desenhar();
    }
    document.getElementById('calModalFechar').addEventListener('click', fecharModal);
    modal.addEventListener('mousedown', function (ev) { if (ev.target === modal) fecharModal(); });
    document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape' && modalAberto() && !sugAberta()) fecharModal(); });
    function preencherForm(e) {
        if (!e) return;
        document.getElementById('f_id').value = e.id;
        document.getElementById('f_posicao').value = e.ordem;
        CAMPOS.forEach(function (c) { document.getElementById('f_' + c).value = e[c] || ''; });
        // sede vazia = automática: mostra preenchida com o país tirado do nome
        if (!document.getElementById('f_sede').value) document.getElementById('f_sede').value = e.sede_exibicao || '';
        if (!document.getElementById('f_sprint_sede').value) document.getElementById('f_sprint_sede').value = e.sprint_sede_exibicao || '';
        document.getElementById('f_duracao').value = e.duracao || 30; document.getElementById('f_sprint_duracao').value = e.sprint_duracao || 10;
        chkSprint.checked = e.flg_sprint === 'S'; mostrarSprint(); placeholderSede(); atualizarCalculo(false, false); atualizarCalculo(true, false); document.getElementById('avisoSprint').classList.remove('on');
        document.getElementById('btnSalvar').innerHTML = '<i class="bi bi-check2"></i> Salvar alterações';
        atualizarX();
    }
    document.getElementById('btnLimpar').addEventListener('click', function () { if (modalAberto()) fecharModal(); else limpar(); });

    // botão "×" para apagar rápido um campo do formulário (a etapa só muda ao salvar)
    var camposComX = Array.prototype.slice.call(document.querySelectorAll('#formEtapa .cal-campo input[type=text]'));
    camposComX.forEach(function (inp) {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'cal-x'; b.title = 'Apagar este campo'; b.innerHTML = '&times;'; b.tabIndex = -1;
        inp.classList.add('tem-x'); inp.parentNode.appendChild(b);
        b.addEventListener('click', function () { inp.value = ''; inp.dispatchEvent(new Event('input', { bubbles: true })); inp.focus(); });
        inp.addEventListener('input', function () { b.classList.toggle('on', inp.value !== ''); });
    });
    function atualizarX() { camposComX.forEach(function (inp) { inp.parentNode.querySelector('.cal-x').classList.toggle('on', inp.value !== ''); }); }

    document.getElementById('formEtapa').addEventListener('submit', function (ev) {
        ev.preventDefault();
        var dados = { id: document.getElementById('f_id').value || null, flg_sprint: chkSprint.checked ? 'S' : 'N' };
        var pos = parseInt(document.getElementById('f_posicao').value, 10);
        if (!pos || pos < 1) { mensagem('msgForm', 'Informe a ordem (posição) da etapa.'); document.getElementById('f_posicao').focus(); return; }
        dados.posicao = pos;
        CAMPOS.forEach(function (c) { dados[c] = document.getElementById('f_' + c).value; });
        if (!chkSprint.checked) CAMPOS.forEach(function (c) { if (c.indexOf('sprint_') === 0) dados[c] = ''; }); // sem sprint: apaga a sprint
        dados.duracao = parseInt(document.getElementById('f_duracao').value, 10) || 30;
        dados.sprint_duracao = parseInt(document.getElementById('f_sprint_duracao').value, 10) || 10;
        var btn = document.getElementById('btnSalvar'); btn.disabled = true;
        var editando = !!dados.id;
        enviar(URL_SALVAR, dados)
            .then(function (j) {
                etapas = j.etapas;
                if (modalAberto()) { fecharModal(); mensagem('msgGrid', 'Etapa alterada.', 'ok'); return; }
                limpar(); desenhar();
                mensagem('msgForm', editando ? 'Etapa alterada.' : 'Etapa adicionada.', 'ok');
                fNome.focus();
            })
            .catch(function (err) { mensagem('msgForm', err.message); })
            .finally(function () { btn.disabled = false; });
    });

    // ---------- MELHORIA 20261126: CADASTRAR OFICIALMENTE ----------
    // O calendário é rascunho (texto livre); o servidor procura temporada, evento e pista no cadastro.
    var ofModal = document.getElementById('calOficial'), ofForm = document.getElementById('formOficial');
    var ofSelects = { temporada: $('#of_temporada'), evento: $('#of_evento'), pista: $('#of_pista') };
    var ofDados = null, ofEtapaId = null, ofPedido = 0, ofConfirmar = false, ofEnviando = false;
    var OF_ICONES = { ok: 'bi-check-circle-fill', atencao: 'bi-exclamation-triangle-fill', nao: 'bi-x-circle-fill' };
    function ofAberto() { return ofModal.classList.contains('on'); }
    function ofStatus(campo, status, texto) {
        var box = ofForm.querySelector('[data-of="' + campo + '"]');
        box.classList.remove('ok', 'atencao', 'nao'); if (status) box.classList.add(status);
        var s = box.querySelector('.cal-of-status');
        s.className = 'cal-of-status ' + (status || '');
        s.innerHTML = texto ? '<i class="bi ' + OF_ICONES[status] + '"></i> ' + esc(texto) : '';
    }
    // mudou alguma escolha: o aviso de corrida repetida deixa de valer
    function ofLimparAviso() {
        ofConfirmar = false;
        document.getElementById('ofRepetidas').hidden = true;
        document.getElementById('ofSalvar').innerHTML = '<i class="bi bi-check2"></i> Salvar';
    }
    function ofTemporadaEscolhida() {
        var id = parseInt(ofSelects.temporada.val(), 10);
        return ofDados.temporadas.find(function (t) { return t.id === id; });
    }
    function ofPreencher(campo, lista, busca) {
        var sel = ofSelects[campo];
        if (sel.hasClass('select2-hidden-accessible')) sel.select2('destroy'); // o select2 global do layout é refeito aqui
        sel.html('<option value="">Selecione...</option>' + lista.map(function (o) { return '<option value="' + o.id + '">' + esc(o.texto) + '</option>'; }).join(''));
        sel.val(busca.id ? String(busca.id) : '');
        // dropdownParent: sem isso a lista abre atrás do modal
        sel.select2({ theme: 'classic', width: '100%', dropdownParent: $(ofModal).find('.cal-modal-caixa') });
        ofStatus(campo, busca.status, busca.mensagem);
    }
    function abrirOficial(id, parte) {
        var e = etapas.find(function (x) { return x.id === id; }); if (!e) return;
        var sprint = parte === 'sprint', pedido = ++ofPedido;
        ofEtapaId = id; ofDados = null;
        var nome = sprint ? e.sprint_nome : e.nome;
        document.getElementById('calOficialTitulo').textContent = 'Cadastrar ' + (sprint ? 'sprint' : 'corrida') + ' — etapa ' + e.ordem + (nome ? ' · ' + nome : '');
        ofModal.querySelector('.cal-modal-caixa').classList.toggle('sprint', sprint);
        ofForm.hidden = true; document.getElementById('ofPronto').hidden = true;
        document.getElementById('ofCarregando').hidden = false;
        document.getElementById('msgOficial').className = 'cal-msg';
        ofLimparAviso();
        ofModal.classList.add('on'); ofModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('cal-sem-scroll');
        fetch(URL_OFICIAL.replace('__ID__', id) + '?parte=' + parte, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(r.status === 401 || r.status === 419 ? 'Sessão expirada. Recarregue a página.' : 'Erro ' + r.status); return r.json(); })
            .then(function (j) {
                if (pedido !== ofPedido || !ofAberto()) return; // modal fechado ou outra etapa aberta
                ofDados = j;
                document.getElementById('ofCarregando').hidden = true; ofForm.hidden = false;
                ofPreencher('temporada', j.temporadas, j.busca.temporada);
                ofPreencher('evento', j.eventos, j.busca.evento);
                ofPreencher('pista', j.pistas, j.busca.pista);
                document.getElementById('of_ordem').value = j.ordem;
                document.getElementById('of_sprint').checked = j.flg_sprint === 'S';
                var t = ofTemporadaEscolhida();
                if (t && t.finalizada && j.busca.temporada.status === 'ok') ofStatus('temporada', 'atencao', j.busca.temporada.mensagem + ' Atenção: ela está finalizada.');
                document.getElementById('ofSalvar').focus();
            })
            .catch(function (err) {
                if (pedido !== ofPedido) return;
                document.getElementById('ofCarregando').textContent = 'Não foi possível carregar: ' + err.message;
            });
    }
    function fecharOficial() {
        if (!ofAberto() || ofEnviando) return;
        ofPedido++;
        Object.keys(ofSelects).forEach(function (c) { if (ofSelects[c].hasClass('select2-hidden-accessible')) ofSelects[c].select2('close'); });
        ofModal.classList.remove('on'); ofModal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('cal-sem-scroll');
        document.getElementById('ofCarregando').textContent = 'Procurando temporada, evento e pista...';
    }
    ofModal.querySelectorAll('[data-of-fechar]').forEach(function (b) { b.addEventListener('click', fecharOficial); });
    ofModal.addEventListener('mousedown', function (ev) { if (ev.target === ofModal) fecharOficial(); });
    // fase de captura: com uma lista do select2 aberta, o Esc fecha só a lista
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && ofAberto() && !document.querySelector('.select2-container--open')) fecharOficial();
    }, true);
    Object.keys(ofSelects).forEach(function (campo) {
        ofSelects[campo].on('select2:select', function () {
            ofLimparAviso();
            if (campo === 'temporada') {
                var t = ofTemporadaEscolhida();
                if (t && t.finalizada) ofStatus(campo, 'atencao', 'Esta temporada está finalizada.'); else ofStatus(campo, '', '');
            } else {
                ofStatus(campo, '', '');
            }
        });
    });
    ['of_ordem', 'of_sprint'].forEach(function (id) { document.getElementById(id).addEventListener('input', ofLimparAviso); });
    ofForm.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var dados = {
            temporada_id: ofSelects.temporada.val() || null,
            evento_id: ofSelects.evento.val() || null,
            pista_id: ofSelects.pista.val() || null,
            ordem: document.getElementById('of_ordem').value || null,
            flg_sprint: document.getElementById('of_sprint').checked ? 'S' : 'N',
            confirmar: ofConfirmar
        };
        var falta = ['temporada', 'evento', 'pista'].filter(function (c) { return !dados[c + '_id']; });
        if (falta.length) { mensagem('msgOficial', 'Selecione: ' + falta.join(', ') + '.'); return; }
        var btn = document.getElementById('ofSalvar'); btn.disabled = true; ofEnviando = true;
        enviar(URL_OFICIAL.replace('__ID__', ofEtapaId), dados)
            .then(function (j) {
                if (!j.ok && j.repetidas) { // já existe algo parecido na temporada: pede um segundo clique
                    ofConfirmar = true;
                    var aviso = document.getElementById('ofRepetidas');
                    aviso.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Esta temporada já tem ' + (dados.flg_sprint === 'S' ? 'sprint' : 'corrida') +
                        ' com a mesma ordem ou o mesmo evento:<ul>' + j.repetidas.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('') + '</ul>Confira antes de lançar.';
                    aviso.hidden = false;
                    btn.innerHTML = '<i class="bi bi-check2-all"></i> Lançar mesmo assim';
                    return;
                }
                if (!j.ok) throw new Error('Não foi possível lançar a corrida.');
                ofForm.hidden = true;
                document.getElementById('ofProntoTexto').textContent = j.mensagem;
                document.getElementById('ofProntoLink').href = j.url;
                document.getElementById('ofPronto').hidden = false;
            })
            .catch(function (err) { mensagem('msgOficial', err.message); })
            .finally(function () { btn.disabled = false; ofEnviando = false; });
    });

    // ---------- GERAR ETAPAS VAZIAS (só a ordem) ----------
    document.getElementById('btnGerar').addEventListener('click', function () {
        var qtd = parseInt(document.getElementById('calQtdGerar').value, 10);
        if (!qtd || qtd < 1 || qtd > 50) { mensagem('msgGrid', 'Informe uma quantidade entre 1 e 50.'); return; }
        var maior = etapas.reduce(function (m, e) { return Math.max(m, e.ordem); }, 0);
        var btn = this; btn.disabled = true;
        enviar(URL_GERAR, { quantidade: qtd })
            .then(function (j) { etapas = j.etapas; limpar(); desenhar(); mensagem('msgGrid', qtd + ' etapa(s) criada(s): nº ' + (maior + 1) + (qtd > 1 ? ' a ' + (maior + qtd) : '') + '.', 'ok'); })
            .catch(function (err) { mensagem('msgGrid', err.message); })
            .finally(function () { btn.disabled = false; });
    });
    document.getElementById('calQtdGerar').addEventListener('keydown', function (ev) { if (ev.key === 'Enter') { ev.preventDefault(); document.getElementById('btnGerar').click(); } });

    // ---------- FECHAR BURACOS ----------
    document.getElementById('btnFecharBuracos').addEventListener('click', function () {
        confirmar({
            titulo: 'Fechar buracos?', perigo: false, icone: 'bi-sort-numeric-down', botao: 'Renumerar',
            texto: 'As etapas serão renumeradas 1, 2, 3... na ordem atual, eliminando os números vagos.'
        }).then(function (ok) {
            if (!ok) return;
            enviar(URL_RENUMERAR).then(function (j) { etapas = j.etapas; limpar(); desenhar(); mensagem('msgGrid', 'Numeração refeita sem buracos.', 'ok'); })
                .catch(function (err) { mensagem('msgGrid', err.message); });
        });
    });

    // ---------- FILTROS ----------
    ['calBusca', 'calFiltroContinente', 'calFiltroTipo'].forEach(function (id) {
        document.getElementById(id).addEventListener(id === 'calBusca' ? 'input' : 'change', desenhar);
    });
    document.getElementById('btnLimparFiltros').addEventListener('click', function () {
        document.getElementById('calBusca').value = ''; document.getElementById('calFiltroContinente').value = ''; document.getElementById('calFiltroTipo').value = ''; desenhar();
    });

    // ---------- RENOMEAR ----------
    var formRen = document.getElementById('formRenomear');
    formRen.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var nome = document.getElementById('renNome').value.trim(); if (!nome) return;
        enviar(URL_TEMPORADA, { nome: nome }).then(function (j) {
            document.getElementById('calNomeHero').textContent = j.nome;
            var opt = document.querySelector('#calSelTemporada option:checked'); if (opt) opt.textContent = j.nome;
            formRen.closest('.cal-painel').classList.remove('on');
        }).catch(function (err) { alert(err.message); });
    });

    // ---------- ANOTAÇÕES (salva sozinha) ----------
    var anot = document.getElementById('anotacoes'), status = document.getElementById('statusAnot'), tAnot = null, ultimo = anot.value;
    function salvarAnot() {
        if (anot.value === ultimo) return;
        var valor = anot.value;
        status.className = 'cal-status'; status.textContent = 'Salvando...';
        enviar(URL_TEMPORADA, { anotacoes: valor })
            .then(function (j) { ultimo = valor; status.className = 'cal-status ok'; status.textContent = 'Salvo às ' + j.salvo_em; })
            .catch(function (err) { status.className = 'cal-status erro'; status.textContent = 'Não salvou: ' + err.message; });
    }
    anot.addEventListener('input', function () { status.className = 'cal-status'; status.textContent = 'Alterações não salvas...'; clearTimeout(tAnot); tAnot = setTimeout(salvarAnot, 1200); });
    anot.addEventListener('blur', function () { clearTimeout(tAnot); salvarAnot(); });
    window.addEventListener('beforeunload', function (e) { if (anot.value !== ultimo) { salvarAnot(); e.preventDefault(); e.returnValue = ''; } });

    // ---------- SUGESTÕES AO DIGITAR ----------
    // Lista própria (não depende do navegador). Fonte: datalists preenchidos por atualizarListas()
    // com tudo o que já foi digitado em qualquer temporada do calendário.
    var sugBox = document.createElement('div'); sugBox.className = 'cal-sug'; document.body.appendChild(sugBox);
    var sugInput = null, sugAtivo = -1, sugItens = [];
    function sugAberta() { return sugBox.classList.contains('on'); }
    function fecharSugestoes() { sugBox.classList.remove('on'); sugInput = null; sugAtivo = -1; sugItens = []; }
    function marcar(texto, termo) {
        var i = semAcento(texto).indexOf(termo);
        if (i < 0 || !termo) return esc(texto);
        return esc(texto.slice(0, i)) + '<b>' + esc(texto.slice(i, i + termo.length)) + '</b>' + esc(texto.slice(i + termo.length));
    }
    function mostrarSugestoes(inp) {
        var dl = document.getElementById(inp.dataset.sug); if (!dl) return;
        var termo = semAcento(inp.value);
        if (!termo) { fecharSugestoes(); return; }
        var todos = Array.prototype.map.call(dl.options, function (o) { return o.value; });
        var comeca = [], contem = [];
        todos.forEach(function (t) {
            var k = semAcento(t); if (k === termo) return;
            var i = k.indexOf(termo); if (i === 0) comeca.push(t); else if (i > 0) contem.push(t);
        });
        sugItens = comeca.concat(contem).slice(0, 10);
        if (!sugItens.length) { fecharSugestoes(); return; }
        sugInput = inp; sugAtivo = -1;
        sugBox.innerHTML = sugItens.map(function (t, i) { return '<div data-i="' + i + '">' + marcar(t, termo) + '</div>'; }).join('');
        var r = inp.getBoundingClientRect();
        sugBox.style.left = r.left + 'px'; sugBox.style.top = (r.bottom + 4) + 'px'; sugBox.style.minWidth = Math.max(r.width, 180) + 'px';
        sugBox.classList.add('on');
    }
    function escolherSugestao(i) {
        if (!sugInput || i < 0 || i >= sugItens.length) return;
        var inp = sugInput; inp.value = sugItens[i]; fecharSugestoes();
        inp._semSug = true; inp.dispatchEvent(new Event('input', { bubbles: true })); inp._semSug = false;
        inp.focus();
    }
    function destacar(i) {
        sugAtivo = i;
        Array.prototype.forEach.call(sugBox.children, function (d, k) { d.classList.toggle('ativo', k === i); if (k === i) d.scrollIntoView({ block: 'nearest' }); });
    }
    sugBox.addEventListener('mousedown', function (ev) {
        var d = ev.target.closest('div[data-i]'); if (!d) return;
        ev.preventDefault(); escolherSugestao(parseInt(d.dataset.i, 10));
    });
    function ligarSugestoes(inp) {
        inp.setAttribute('autocomplete', 'off');
        inp.addEventListener('input', function () { if (!inp._semSug) mostrarSugestoes(inp); });
        inp.addEventListener('blur', function () { setTimeout(function () { if (sugInput === inp) fecharSugestoes(); }, 120); });
        inp.addEventListener('keydown', function (ev) {
            if (!sugAberta() || sugInput !== inp) return;
            if (ev.key === 'ArrowDown') { ev.preventDefault(); destacar(Math.min(sugAtivo + 1, sugItens.length - 1)); }
            else if (ev.key === 'ArrowUp') { ev.preventDefault(); destacar(Math.max(sugAtivo - 1, 0)); }
            else if (ev.key === 'Enter' && sugAtivo >= 0) { ev.preventDefault(); ev.stopImmediatePropagation(); escolherSugestao(sugAtivo); }
            else if (ev.key === 'Escape') { ev.preventDefault(); ev.stopImmediatePropagation(); fecharSugestoes(); }
            else if (ev.key === 'Tab') { fecharSugestoes(); }
        });
    }
    document.querySelectorAll('#formEtapa input[data-sug]').forEach(ligarSugestoes);
    window.addEventListener('resize', fecharSugestoes);
    document.addEventListener('scroll', function (ev) { if (!sugBox.contains(ev.target)) fecharSugestoes(); }, true);

    limpar();
    desenhar();
})();
</script>
@endif
@endsection
