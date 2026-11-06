@php
    use App\Models\Site\Resultado;
@endphp

@extends('layouts.main')

@section('section')

<style>
    /* Tipografia e Cores Oficiais da F1 */
    @import url('https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap');

    .f1-stats {
        --f1-red: #e10600;
        --f1-red-dark: #b80500;
        --f1-bg: #15151e;
        --f1-surface: #1e1e27;
        --f1-surface-2: #2a2a35;
        --f1-border: #38383f;
        --f1-text: #ffffff;
        --f1-muted: #9a9aa3;
        --f1-gold: #f2c94c;
        --f1-silver: #c0c4cc;
        --f1-bronze: #cd8b5a;

        font-family: 'Titillium Web', sans-serif;
        background-color: var(--f1-bg);
        color: var(--f1-text);
        padding: 12px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        min-width: 0;
    }

    /* o app.css aplica Poppins em todos os elementos (*); nesta tela vale a fonte da F1 */
    .f1-stats, .f1-stats * { font-family: 'Titillium Web', sans-serif; }

    @media (min-width: 768px) {
        .f1-stats { padding: 24px; }
    }

    /* Cabeçalho */
    .f1-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        list-style: none;
        padding: 0;
        margin: 0 0 6px 0;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .f1-breadcrumb li a {
        color: var(--f1-muted);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .f1-breadcrumb li a:hover { color: var(--f1-red); }

    .f1-breadcrumb li::after {
        content: '>';
        margin: 0 8px;
        color: var(--f1-red);
        font-weight: 900;
    }

    .f1-breadcrumb li:last-child::after { content: ''; }

    .f1-breadcrumb li.active { color: var(--f1-red); }

    .f1-titulo {
        font-size: 24px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 20px 0;
        line-height: 1.2;
    }

    @media (min-width: 768px) {
        .f1-titulo { font-size: 30px; }
    }

    /* Cartão de filtros */
    .f1-card {
        background-color: var(--f1-surface);
        border: 1px solid var(--f1-border);
        border-radius: 8px;
        padding: 12px;
    }

    @media (min-width: 768px) {
        .f1-card { padding: 20px; }
    }

    .f1-filtros {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 16px;
    }

    @media (min-width: 768px) {
        .f1-filtros { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (min-width: 1200px) {
        .f1-filtros { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    .f1-form-group {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .f1-label {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 1px;
        color: #d0d0d2;
        margin-bottom: 8px;
        border-left: 3px solid var(--f1-red);
        padding-left: 8px;
    }

    .f1-input {
        width: 100%;
        background-color: var(--f1-bg);
        color: var(--f1-text);
        border: 1px solid var(--f1-border);
        padding: 9px 12px;
        border-radius: 6px;
        font-size: 15px;
        font-family: 'Titillium Web', sans-serif;
        font-weight: 600;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        box-sizing: border-box;
        min-height: 44px;
    }

    .f1-input:focus {
        border-color: var(--f1-red);
        box-shadow: 0 0 0 3px rgba(225, 6, 0, 0.25);
    }

    /* Alternador (substitui selects de 2 opções) */
    .f1-toggle {
        display: flex;
        background-color: var(--f1-bg);
        border: 1px solid var(--f1-border);
        border-radius: 6px;
        padding: 3px;
        gap: 3px;
        min-height: 44px;
    }

    .f1-toggle input { position: absolute; opacity: 0; pointer-events: none; }

    .f1-toggle label {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin: 0;
        padding: 6px 10px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 14px;
        color: var(--f1-muted);
        cursor: pointer;
        text-align: center;
        transition: background-color 0.2s ease, color 0.2s ease;
        user-select: none;
    }

    .f1-toggle label:hover { color: var(--f1-text); }

    .f1-toggle input:checked + label {
        background-color: var(--f1-red);
        color: #fff;
    }

    .f1-toggle input:focus-visible + label { outline: 2px solid #fff; outline-offset: 1px; }

    /* Posição máxima com atalhos */
    .f1-limite {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 6px;
    }

    .f1-limite .f1-input { width: 100%; text-align: center; padding: 9px 4px; }

    .f1-chips { display: contents; }

    .f1-chip {
        min-width: 0;
        min-height: 44px;
        background-color: var(--f1-bg);
        border: 1px solid var(--f1-border);
        border-radius: 6px;
        color: var(--f1-muted);
        font-family: 'Titillium Web', sans-serif;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        padding: 0 6px;
        transition: all 0.2s ease;
    }

    .f1-chip:hover { color: #fff; border-color: var(--f1-muted); }

    .f1-chip.f1-ativo {
        border-color: var(--f1-red);
        color: #fff;
        background-color: rgba(225, 6, 0, 0.18);
    }

    .f1-span-2 { grid-column: auto; }

    @media (min-width: 768px) {
        .f1-span-2 { grid-column: span 2; }
    }

    @media (min-width: 1200px) {
        .f1-span-2 { grid-column: auto; }
    }

    /* Botões */
    .f1-acoes {
        display: flex;
        flex-direction: column-reverse;
        gap: 10px;
        margin-top: 20px;
    }

    @media (min-width: 576px) {
        .f1-acoes { flex-direction: row; justify-content: flex-end; }
    }

    .f1-btn {
        background-color: var(--f1-red);
        color: white;
        border: 1px solid var(--f1-red);
        padding: 10px 28px;
        min-height: 46px;
        font-size: 15px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        border-radius: 6px;
        cursor: pointer;
        font-family: 'Titillium Web', sans-serif;
        transition: background-color 0.2s ease, transform 0.1s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .f1-btn:hover { background-color: var(--f1-red-dark); }
    .f1-btn:active { transform: scale(0.98); }
    .f1-btn:disabled { opacity: 0.7; cursor: wait; }

    .f1-btn-secundario {
        background-color: transparent;
        border-color: var(--f1-border);
        color: var(--f1-muted);
    }

    .f1-btn-secundario:hover { background-color: var(--f1-surface-2); color: #fff; }

    /* Select2 no tema escuro (apenas nesta tela) */
    .f1-stats .select2-container { width: 100% !important; }

    .f1-stats .select2-container--default .select2-selection--multiple {
        background-color: var(--f1-bg);
        border: 1px solid var(--f1-border);
        border-radius: 6px;
        min-height: 44px;
        padding: 4px 6px;
        cursor: text;
    }

    .f1-stats .select2-container--default.select2-container--focus .select2-selection--multiple,
    .f1-stats .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: var(--f1-red);
        box-shadow: 0 0 0 3px rgba(225, 6, 0, 0.25);
    }

    .f1-stats .select2-container .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin: 0;
        padding: 0;
    }

    .f1-stats .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: var(--f1-surface-2);
        border: 1px solid var(--f1-border);
        color: #fff;
        border-radius: 4px;
        margin: 0;
        padding: 2px 8px 2px 24px;
        font-size: 13px;
        font-weight: 600;
        position: relative;
        max-width: 100%;
        white-space: normal;
    }

    .f1-stats .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: var(--f1-muted);
        border-right: 1px solid var(--f1-border);
        height: 100%;
        padding: 0 5px;
        top: 0;
        left: 0;
    }

    .f1-stats .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background-color: var(--f1-red);
        color: #fff;
    }

    .f1-stats .select2-container .select2-search--inline .select2-search__field {
        color: #fff;
        font-family: 'Titillium Web', sans-serif;
        font-size: 15px;
        margin: 4px 0 0 4px;
        height: 26px;
    }

    .f1-stats .select2-container--default .select2-selection--multiple .select2-selection__clear {
        color: var(--f1-muted);
        margin: 0;
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px;
    }

    .f1-stats .select2-dropdown {
        background-color: var(--f1-surface);
        border: 1px solid var(--f1-red);
        color: #fff;
        font-family: 'Titillium Web', sans-serif;
        overflow: hidden;
    }

    .f1-stats .select2-results__options { max-height: 260px !important; }

    .f1-stats .select2-container--default .select2-results__option {
        padding: 9px 12px;
        font-size: 14px;
        font-weight: 600;
    }

    .f1-stats .select2-container--default .select2-results__option--selected {
        background-color: var(--f1-surface-2);
        color: var(--f1-muted);
    }

    .f1-stats .select2-container--default .select2-results__option--selected::after {
        content: '\2713';
        float: right;
        color: var(--f1-red);
        font-weight: 900;
    }

    .f1-stats .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: var(--f1-red);
        color: #fff;
    }

    .f1-stats .select2-results__message { color: var(--f1-muted); }

    /* Resultados */
    .f1-resultados { margin-top: 24px; }

    .f1-resumo {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .f1-resumo-titulo {
        font-size: 22px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
        border-left: 4px solid var(--f1-red);
        padding-left: 10px;
        line-height: 1.1;
    }

    .f1-resumo-sub {
        font-size: 13px;
        color: var(--f1-muted);
        font-weight: 600;
        margin-top: 2px;
        padding-left: 14px;
    }

    .f1-abas {
        display: flex;
        background-color: var(--f1-surface);
        border: 1px solid var(--f1-border);
        border-radius: 8px;
        padding: 4px;
        gap: 4px;
        width: 100%;
    }

    @media (min-width: 576px) {
        .f1-abas { width: auto; }
    }

    .f1-aba {
        flex: 1;
        background: transparent;
        border: none;
        color: var(--f1-muted);
        font-family: 'Titillium Web', sans-serif;
        font-weight: 800;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 8px 18px;
        border-radius: 6px;
        cursor: pointer;
        white-space: nowrap;
        min-height: 40px;
    }

    .f1-aba:hover { color: #fff; }

    .f1-aba.f1-ativo {
        background-color: var(--f1-red);
        color: #fff;
    }

    .f1-aba .f1-contador {
        display: inline-block;
        margin-left: 6px;
        background-color: rgba(255,255,255,0.15);
        border-radius: 10px;
        padding: 0 8px;
        font-size: 12px;
    }

    .f1-lista {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .f1-item {
        background-color: var(--f1-surface);
        border: 1px solid var(--f1-border);
        border-radius: 8px;
        overflow: hidden;
    }

    .f1-item-btn {
        width: 100%;
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) auto 20px;
        align-items: center;
        gap: 12px;
        background: transparent;
        border: none;
        color: #fff;
        padding: 12px 14px;
        text-align: left;
        cursor: pointer;
        font-family: 'Titillium Web', sans-serif;
    }

    .f1-item-btn:hover { background-color: var(--f1-surface-2); }

    .f1-posicao {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 15px;
        background-color: var(--f1-surface-2);
        color: var(--f1-muted);
    }

    .f1-posicao.f1-p1 { background-color: var(--f1-gold); color: #15151e; }
    .f1-posicao.f1-p2 { background-color: var(--f1-silver); color: #15151e; }
    .f1-posicao.f1-p3 { background-color: var(--f1-bronze); color: #15151e; }

    .f1-nome {
        font-size: 16px;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }

    .f1-barra {
        display: block;
        height: 6px;
        background-color: var(--f1-bg);
        border-radius: 3px;
        margin-top: 6px;
        overflow: hidden;
    }

    .f1-barra span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, var(--f1-red-dark), var(--f1-red));
        border-radius: 3px;
    }

    .f1-quantidade {
        font-size: 22px;
        font-weight: 900;
        color: var(--f1-red);
        min-width: 40px;
        text-align: right;
    }

    .f1-seta {
        color: var(--f1-muted);
        transition: transform 0.2s ease;
    }

    .f1-item-btn[aria-expanded="true"] .f1-seta { transform: rotate(180deg); }

    .f1-detalhes {
        padding: 14px;
        background-color: var(--f1-bg);
        border-top: 1px solid var(--f1-border);
    }

    .f1-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }

    /* cartão único (detalhe das equipes): mantém a largura de uma coluna em vez de ocupar a linha toda */
    .f1-stats-grid.f1-grid-unico {
        grid-template-columns: repeat(auto-fill, minmax(min(320px, 100%), 1fr));
    }

    .f1-stat-card {
        background-color: var(--f1-surface);
        border: 1px solid var(--f1-border);
        border-radius: 6px;
        overflow: hidden;
    }

    .f1-stat-header {
        background-color: var(--f1-border);
        padding: 8px 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 13px;
        color: #ededed;
        display: flex;
        justify-content: space-between;
    }

    .f1-stat-list {
        list-style: none;
        padding: 6px 12px;
        margin: 0;
        max-height: 260px;
        overflow-y: auto;
    }

    .f1-stat-list li {
        font-size: 14px;
        padding: 6px 0;
        border-bottom: 1px dashed var(--f1-border);
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .f1-stat-list li:last-child { border-bottom: none; }

    .f1-stat-list .f1-stat-pos {
        color: var(--f1-muted);
        margin-right: 6px;
        font-weight: 600;
    }

    .f1-stat-list .f1-stat-qtd {
        font-weight: 800;
        color: var(--f1-red);
    }

    .f1-aviso {
        text-align: center;
        color: var(--f1-muted);
        padding: 40px 16px;
        border: 1px dashed var(--f1-border);
        border-radius: 8px;
        font-weight: 600;
    }

    .f1-aviso i {
        display: block;
        font-size: 32px;
        color: var(--f1-red);
        margin-bottom: 8px;
    }

    .f1-aviso.f1-erro { border-color: var(--f1-red); color: #fff; }

    .f1-d-none { display: none !important; }

    @media (max-width: 575.98px) {
        .f1-chip { font-size: 11px; padding: 0; white-space: nowrap; letter-spacing: -0.2px; }
        .f1-aba { min-width: 0; padding: 8px 6px; font-size: 13px; letter-spacing: 0.3px; }
        .f1-aba .f1-contador { margin-left: 4px; padding: 0 6px; }
        .f1-titulo { font-size: 20px; }
        .f1-detalhes { padding: 10px; }
        .f1-stats-grid { grid-template-columns: minmax(0, 1fr); }
        .f1-item-btn { grid-template-columns: 30px minmax(0, 1fr) auto 16px; }
        .f1-posicao { width: 30px; height: 30px; font-size: 14px; }
        .f1-item-btn { gap: 10px; padding: 12px; }
        .f1-nome { font-size: 15px; }
        .f1-quantidade { font-size: 20px; }
        .f1-resumo-titulo { font-size: 20px; }
    }
</style>

<div class="f1-stats mt-3 mb-3">

    <ul class="f1-breadcrumb">
        <li><a href="{{route('home')}}">Home</a></li>
        <li class="active">Estatísticas</li>
    </ul>
    <h1 class="f1-titulo">Estatísticas de pilotos e equipes</h1>

    {{-- Filtros --}}
    <div class="f1-card">
        <div class="f1-filtros">

            {{-- Tipo de Consulta --}}
            <div class="f1-form-group">
                <span class="f1-label">Tipo de Análise</span>
                <div class="f1-toggle" role="radiogroup" aria-label="Tipo de Análise">
                    <input type="radio" name="tipoConsulta" id="tipoChegada" value="chegada" checked>
                    <label for="tipoChegada"><i class="bi bi-flag-fill"></i> Chegada</label>
                    <input type="radio" name="tipoConsulta" id="tipoLargada" value="largada">
                    <label for="tipoLargada"><i class="bi bi-stopwatch-fill"></i> Largada</label>
                </div>
            </div>

            {{-- Filtro Sprint --}}
            <div class="f1-form-group">
                <span class="f1-label">Corridas Sprint</span>
                <div class="f1-toggle" role="radiogroup" aria-label="Corridas Sprint">
                    <input type="radio" name="flg_sprint" id="sprintNao" value="N" checked>
                    <label for="sprintNao">Não</label>
                    <input type="radio" name="flg_sprint" id="sprintSim" value="S">
                    <label for="sprintSim">Sim</label>
                </div>
            </div>

            {{-- Posição Máxima --}}
            <div class="f1-form-group f1-span-2">
                <label for="limite" class="f1-label">Posição Máxima</label>
                <div class="f1-limite">
                    <input type="number" value="1" min="1" name="limite" id="limite" class="f1-input" inputmode="numeric">
                    <div class="f1-chips">
                        <button type="button" class="f1-chip f1-ativo" data-limite="1">Top 1</button>
                        <button type="button" class="f1-chip" data-limite="3">Top 3</button>
                        <button type="button" class="f1-chip" data-limite="5">Top 5</button>
                        <button type="button" class="f1-chip" data-limite="10">Top 10</button>
                    </div>
                </div>
            </div>

            {{-- Filtro por Temporada --}}
            <div class="f1-form-group">
                <label for="temporada" class="f1-label">Temporadas</label>
                <select name="temporada[]" id="temporada" multiple data-placeholder="Todas as temporadas">
                    @foreach($temporadas as $temporada)
                        <option value="{{$temporada->id}}">{{$temporada->des_temporada}} ({{$temporada->referencia}})</option>
                    @endforeach
                </select>
            </div>

            {{-- Pilotos Ignorados --}}
            <div class="f1-form-group">
                <label for="pilotos_ignorados" class="f1-label">Pilotos Ignorados</label>
                <select name="pilotos_ignorados[]" id="pilotos_ignorados" multiple data-placeholder="Nenhum piloto ignorado">
                    @foreach($pilotos as $piloto)
                        <option value="{{$piloto->id}}">{{$piloto->nomeCompleto()}}</option>
                    @endforeach
                </select>
            </div>

            {{-- Equipes Ignoradas --}}
            <div class="f1-form-group">
                <label for="equipes_ignoradas" class="f1-label">Equipes Ignoradas</label>
                <select name="equipes_ignoradas[]" id="equipes_ignoradas" multiple data-placeholder="Nenhuma equipe ignorada">
                    @foreach($equipes as $equipe)
                        <option value="{{$equipe->id}}">{{$equipe->nome}}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="f1-acoes">
            <button type="button" class="f1-btn f1-btn-secundario" id="botaoLimpar"><i class="bi bi-arrow-counterclockwise"></i> Limpar filtros</button>
            <button type="button" class="f1-btn" id="botaoPesquisa" onclick="buscarDados()"><i class="bi bi-search"></i> <span>Pesquisar</span></button>
        </div>
    </div>

    {{-- Resultados --}}
    <div class="f1-resultados">

        <div class="f1-aviso" id="avisoResultados">
            <i class="bi bi-bar-chart-line"></i>
            Escolha os filtros e clique em <strong>Pesquisar</strong> para ver as estatísticas.
        </div>

        <div class="f1-d-none" id="painelResultados">
            <div class="f1-resumo">
                <div>
                    <h2 class="f1-resumo-titulo" id="descricaoPagina">Resultados</h2>
                    <div class="f1-resumo-sub" id="resumoFiltros"></div>
                </div>
                <div class="f1-abas" role="tablist">
                    <button type="button" class="f1-aba f1-ativo" role="tab" aria-selected="true" data-aba="pilotos">Pilotos <span class="f1-contador" id="contadorPilotos">0</span></button>
                    <button type="button" class="f1-aba" role="tab" aria-selected="false" data-aba="equipes">Equipes <span class="f1-contador" id="contadorEquipes">0</span></button>
                </div>
            </div>

            {{-- Bloco de Pilotos --}}
            <ul class="f1-lista" id="div_pilotos" role="tabpanel">
                {{-- Preenchido via javascript --}}
            </ul>

            {{-- Bloco de Equipes --}}
            <ul class="f1-lista f1-d-none" id="div_equipes" role="tabpanel">
                {{-- Preenchido via javascript --}}
            </ul>
        </div>
    </div>
</div>

<input type="hidden" name="url_busca" id="url_busca" value="{{route('estatisticas.pilotos.equipes.buscar')}}">

<script>
    // O Select2 está no jQuery carregado no <head>; guardo a referência a ele
    const jq = window.jQuery;

    const idiomaSelect2 = {
        noResults: () => 'Nenhum resultado encontrado',
        searching: () => 'Buscando...',
        removeAllItems: () => 'Remover todos',
        removeItem: () => 'Remover'
    };

    ['#temporada', '#pilotos_ignorados', '#equipes_ignoradas'].forEach(function (id) {
        jq(id).select2({
            width: '100%',
            placeholder: jq(id).data('placeholder'),
            allowClear: true,
            closeOnSelect: false,
            dropdownParent: jq('.f1-stats'),
            language: idiomaSelect2
        });
    });

    // Atalhos da posição máxima
    const inputLimite = document.getElementById('limite');
    const chipsLimite = document.querySelectorAll('.f1-chip');

    function marcaChipLimite() {
        chipsLimite.forEach(chip => chip.classList.toggle('f1-ativo', chip.dataset.limite === inputLimite.value));
    }

    chipsLimite.forEach(chip => chip.addEventListener('click', function () {
        inputLimite.value = this.dataset.limite;
        marcaChipLimite();
    }));
    inputLimite.addEventListener('input', marcaChipLimite);

    document.getElementById('botaoLimpar').addEventListener('click', function () {
        document.getElementById('tipoChegada').checked = true;
        document.getElementById('sprintNao').checked = true;
        inputLimite.value = 1;
        marcaChipLimite();
        jq('#temporada, #pilotos_ignorados, #equipes_ignoradas').val(null).trigger('change');
    });

    // Abas Pilotos / Equipes
    document.querySelectorAll('.f1-aba').forEach(aba => aba.addEventListener('click', function () {
        document.querySelectorAll('.f1-aba').forEach(a => {
            const ativa = a === this;
            a.classList.toggle('f1-ativo', ativa);
            a.setAttribute('aria-selected', ativa);
        });
        document.getElementById('div_pilotos').classList.toggle('f1-d-none', this.dataset.aba !== 'pilotos');
        document.getElementById('div_equipes').classList.toggle('f1-d-none', this.dataset.aba !== 'equipes');
    }));

    // Abre/fecha os detalhes de um item da lista
    document.querySelectorAll('.f1-lista').forEach(lista => lista.addEventListener('click', function (e) {
        const botao = e.target.closest('.f1-item-btn');
        if (!botao) return;
        const aberto = botao.getAttribute('aria-expanded') === 'true';
        botao.setAttribute('aria-expanded', !aberto);
        botao.nextElementSibling.classList.toggle('f1-d-none', aberto);
    }));

    function escapeHtml(texto) {
        return String(texto ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    }

    async function buscarDados(){

        const botao = document.getElementById('botaoPesquisa');
        const aviso = document.getElementById('avisoResultados');
        const painel = document.getElementById('painelResultados');

        let tipo = document.querySelector('input[name="tipoConsulta"]:checked').value
        let temporada = jq('#temporada').val() || [];
        let pilotos_ignorados = jq('#pilotos_ignorados').val() || [];
        let equipes_ignoradas = jq('#equipes_ignoradas').val() || [];
        let limite = Math.max(1, parseInt(inputLimite.value, 10) || 1)
        let flg_sprint = document.querySelector('input[name="flg_sprint"]:checked').value

        // Mantém o contrato do backend: "Nenhum" era enviado como 0 e "Todas as temporadas" como vazio
        if (pilotos_ignorados.length === 0) pilotos_ignorados = ['0'];
        if (equipes_ignoradas.length === 0) equipes_ignoradas = ['0'];
        if (temporada.length === 0) temporada = [''];
        inputLimite.value = limite;
        marcaChipLimite();

        const token = document.querySelector('meta[name="csrf-token"]').content
        const url = document.getElementById('url_busca').value;

        botao.disabled = true;
        botao.querySelector('span').innerText = 'Pesquisando...';

        try {
            const req = await fetch(url, {
                method: 'POST',
                headers: {
                    'content-type' : 'application/json',
                    'accept' : 'application/json',
                    'x-csrf-token' : token
                },
                body: JSON.stringify({
                    tipo: tipo,
                    temporada: temporada,
                    pilotos_ignorados: pilotos_ignorados,
                    equipes_ignoradas: equipes_ignoradas,
                    limite: limite,
                    flg_sprint: flg_sprint
                })
            })

            if (!req.ok) throw new Error('HTTP ' + req.status);

            const res = await req.json();

            document.getElementById('descricaoPagina').innerText = res.descricaoPagina;
            document.getElementById('resumoFiltros').innerText = descreveFiltros(temporada, flg_sprint);
            document.getElementById('contadorPilotos').innerText = res.dadosPiloto.length;
            document.getElementById('contadorEquipes').innerText = res.dadosEquipe.length;

            criarTabelaEstatisticasPilotos(res)
            criarTabelaEstatisticasEquipes(res)

            if (res.dadosPiloto.length === 0) {
                mostraAviso('bi-search', 'Nenhum resultado encontrado para os filtros escolhidos.', false);
            } else {
                aviso.classList.add('f1-d-none');
                painel.classList.remove('f1-d-none');
                painel.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
        } catch (erro) {
            mostraAviso('bi-exclamation-triangle', 'Não foi possível carregar as estatísticas. Tente novamente.', true);
        } finally {
            botao.disabled = false;
            botao.querySelector('span').innerText = 'Pesquisar';
        }
    }

    function mostraAviso(icone, mensagem, erro) {
        const aviso = document.getElementById('avisoResultados');
        aviso.innerHTML = `<i class="bi ${icone}"></i>${escapeHtml(mensagem)}`;
        aviso.classList.toggle('f1-erro', erro);
        aviso.classList.remove('f1-d-none');
        document.getElementById('painelResultados').classList.add('f1-d-none');
    }

    function descreveFiltros(temporada, flg_sprint) {
        const qtdTemporadas = temporada.filter(t => t !== '').length;
        const textoTemporadas = qtdTemporadas === 0 ? 'Todas as temporadas' : qtdTemporadas + (qtdTemporadas === 1 ? ' temporada' : ' temporadas');
        return textoTemporadas + ' · ' + (flg_sprint === 'S' ? 'Corridas sprint' : 'Corridas principais');
    }

    function classePosicao(posicao) {
        return posicao <= 3 ? ' f1-p' + posicao : '';
    }

    // Monta o item da lista (cabeçalho clicável + detalhes)
    function montaItem(posicao, nome, quantidade, maximo, detalhes, classeGrid = '') {
        const largura = maximo > 0 ? Math.max(2, Math.round(quantidade / maximo * 100)) : 0;
        return `
            <li class="f1-item">
                <button type="button" class="f1-item-btn" aria-expanded="false">
                    <span class="f1-posicao${classePosicao(posicao)}">${posicao}</span>
                    <span style="min-width: 0;">
                        <span class="f1-nome">${escapeHtml(nome)}</span>
                        <span class="f1-barra"><span style="width: ${largura}%;"></span></span>
                    </span>
                    <span class="f1-quantidade">${quantidade}</span>
                    <i class="bi bi-chevron-down f1-seta"></i>
                </button>
                <div class="f1-detalhes f1-d-none">
                    <div class="f1-stats-grid ${classeGrid}">${detalhes}</div>
                </div>
            </li>`;
    }

    function montaCartao(titulo, itens, campoNome) {
        return `
            <div class="f1-stat-card">
                <div class="f1-stat-header"><span>${titulo}</span><span>${itens.length}</span></div>
                <ul class="f1-stat-list">
                    ${itens.map((item, indice) => `
                        <li>
                            <div><span class="f1-stat-pos">${indice+1}.</span>${escapeHtml(item[campoNome])}</div>
                            <span class="f1-stat-qtd">${item.quantidade}</span>
                        </li>
                    `).join('')}
                </ul>
            </div>`;
    }

    function criarTabelaEstatisticasPilotos(res){
        const maximo = res.dadosPiloto.length ? res.dadosPiloto[0].quantidade : 0;

        document.getElementById('div_pilotos').innerHTML = res.dadosPiloto.map((dadoPiloto, key) =>
            montaItem(key+1, dadoPiloto.nome_piloto, dadoPiloto.quantidade, maximo,
                montaCartao('Equipes', dadoPiloto.equipes, 'equipe_nome') +
                montaCartao('Eventos', dadoPiloto.eventos, 'evento') +
                montaCartao('Pistas', dadoPiloto.pistas, 'pista') +
                montaCartao('Países', dadoPiloto.paises, 'pais'))
        ).join('');
    }

    function criarTabelaEstatisticasEquipes(res){
        // Sempre em ordem decrescente: equipes pelo total e, dentro de cada equipe, pilotos pela quantidade
        const equipes = [...res.dadosEquipe].sort((a, b) => b.quantidade_equipe - a.quantidade_equipe);
        const maximo = equipes.length ? equipes[0].quantidade_equipe : 0;

        document.getElementById('div_equipes').innerHTML = equipes.map((dadoEquipe, key) => {
            const pilotos = [...dadoEquipe.pilotos].sort((a, b) => b.quantidade - a.quantidade);
            return montaItem(key+1, dadoEquipe.equipe_nome, dadoEquipe.quantidade_equipe, maximo,
                montaCartao('Pilotos', pilotos, 'nome_piloto'), 'f1-grid-unico');
        }).join('');
    }
</script>
@endsection
