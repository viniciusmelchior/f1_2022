<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Grid - Classificação Oficial</title>
    <!-- CSRF Token -->
     
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap');

        :root {
            --cor-vermelho-f1: #e10600;
            --cor-fundo-f1: #15151e;       
            --cor-painel-f1: #0f0f14;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Roboto Condensed', -apple-system, sans-serif;
        }

        body {
            background-color: var(--cor-fundo-f1);
            color: #ffffff;
            padding: 20px 10px;
            display: flex;
            justify-content: center;
        }

        .conteiner-principal {
            width: 100%;
            max-width: 600px;
        }

        /* CABEÇALHO DO PAINEL */
        .painel-corrida {
            background-color: var(--cor-painel-f1);
            border-top: 5px solid var(--cor-vermelho-f1);
            padding: 15px;
            text-align: center;
            position: relative;
            margin-bottom: 15px;
        }

        .navegacao-corrida {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .botao-navegacao {
            background: none;
            border: 2px solid #ffffff;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, border-color 0.2s;
        }

        .botao-navegacao svg {
            width: 20px;
            height: 20px;
            fill: #ffffff; 
            transition: fill 0.2s;
        }

        .botao-navegacao:hover:not(:disabled) {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .botao-navegacao:disabled {
            opacity: 0.2;
            cursor: not-allowed;
        }

        .bloco-central-header {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .info-temporada {
            font-size: 0.8rem;
            color: #88888d;
            letter-spacing: 2px;
            font-weight: 700;
        }

        .alinhamento-titulo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 4px 0;
        }

        .imagem-bandeira-header {
            width: 35px;
            height: 22px;
            object-fit: cover;
            border-radius: 2px;
            border: 1px solid #333338;
        }

        .nome-evento {
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            font-style: italic;
        }

        .localizacao-pista {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: 0.9rem;
            color: #bbbbbc;
            font-weight: 400;
        }

        .icone-mapa {
            width: 14px;
            height: 14px;
            fill: #bbbbbc;
        }

        /* ABAS DE NAVEGAÇÃO */
        .abas-f1 {
            display: flex;
            margin-bottom: 15px;
            gap: 5px;
        }

        .aba-item {
            flex: 1;
            background-color: #2b2b35;
            color: #ffffff;
            border: none;
            padding: 12px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            letter-spacing: 1px;
            font-style: italic;
        }

        .aba-item.ativo {
            background-color: var(--cor-vermelho-f1);
        }

        /* MELHORIA 20261110 (layout): seletor Geral / Sprints em controle segmentado */
        :root { --cor-sprint: #ffc107; }

        .seletor-tipo {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background-color: #0b0b10;
            border: 1px solid #2b2b35;
            border-radius: 999px;
            padding: 4px;
            margin-bottom: 15px;
        }

        /* "pílula" que desliza para o botão ativo */
        .seletor-tipo .indicador {
            position: absolute;
            top: 4px;
            bottom: 4px;
            left: 4px;
            width: calc(50% - 4px);
            border-radius: 999px;
            background-color: var(--cor-vermelho-f1);
            box-shadow: 0 0 14px rgba(225, 6, 0, 0.55);
            transition: transform 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
        }

        .seletor-tipo.modo-sprint .indicador {
            transform: translateX(100%);
            background-color: var(--cor-sprint);
            box-shadow: 0 0 14px rgba(255, 193, 7, 0.5);
        }

        .aba-tipo {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: none;
            border: none;
            color: #6e6e78;
            padding: 10px 8px;
            font-weight: 700;
            font-style: italic;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
            cursor: pointer;
            border-radius: 999px;
            transition: color 0.2s;
        }

        .aba-tipo svg { width: 16px; height: 16px; fill: currentColor; }
        .aba-tipo:not(.ativo):hover { color: #ffffff; }
        .aba-tipo.ativo { color: #ffffff; cursor: default; }
        .aba-tipo[data-tipo="sprint"].ativo { color: #15151e; }

        /* faixa do cabeçalho indica o tipo exibido */
        .selo-tipo {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 3px 12px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            background-color: rgba(225, 6, 0, 0.15);
            color: #ff4d47;
            border: 1px solid rgba(225, 6, 0, 0.5);
        }

        body.modo-sprint .selo-tipo {
            background-color: rgba(255, 193, 7, 0.12);
            color: var(--cor-sprint);
            border-color: rgba(255, 193, 7, 0.5);
        }

        body.modo-sprint .painel-corrida { border-top-color: var(--cor-sprint); }
        .painel-corrida { transition: border-top-color 0.25s; }

        .conteudo-aba {
            display: none;
        }

        .conteudo-aba.ativo {
            display: block;
        }

        /* ESTILIZAÇÃO DAS TABELAS */
        .tabela-f1 {
            width: 100%;
            border-collapse: collapse;
        }

        .tabela-f1 thead th {
            font-size: 0.8rem;
            color: #88888d;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            padding-bottom: 8px;
        }

        .linha-classificacao td {
            height: 48px;
            vertical-align: middle;
            text-transform: uppercase;
            font-size: 1.1rem;
            /* padding: 0; */
            border-bottom: 2px solid #ffffff; 
            transition: filter 0.15s ease;
        }

        /* Hover agora usa filtro de brilho para funcionar com qualquer cor vinda do banco */
        .linha-classificacao:hover td {
            filter: brightness(1.2);
            cursor: pointer;
        }

        /* COMPONENTES DAS CÉLULAS */
        .celda-posicao {
            background-color: var(--cor-fundo-f1) !important; 
            color: #ffffff !important;
            font-weight: 700;
            font-style: italic;
            text-align: center;
        }

        .celda-foto, .celda-logo {
            text-align: center;
            padding: 4px !important;
            background-color: inherit; 
        }

        .imagem-renderizada {
            height: 38px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            display: inline-block;
            vertical-align: middle;
            background-color:  var(--cor-fundo-f1);
            border-radius: 25px;
        }

        .celda-nome {
            padding-left: 12px;
            letter-spacing: 0.5px;
            /* font-style: italic; */
            background-color: inherit;
            font-weight: bolder;
        }

        .nome-piloto {
            font-weight: 300;
            opacity: 0.8;
            margin-right: 2px;
            font-style: italic;
        }

        .sobrenome-piloto {
            font-weight: 900;
            margin-left: 0;
        }

        .celda-pontos {
            font-weight: 700;
            font-style: italic;
            font-size: 1.2rem;
            text-align: center;
        }

        .celda-diferenca {
            background-color: var(--cor-fundo-f1) !important;
            font-size: 0.9rem;
            color: #88888d !important;
            font-weight: 700;
            text-align: center;
        }

        /* REMOVIDO: Classes antigas de escuderias (.escuderia-mclaren, etc) */

        /* RESPONSIVIDADE */
        @media (max-width: 450px) {
            .linha-classificacao td { 
                height: 42px; 
                font-size: 0.95rem; 
                border-bottom: 1.5px solid #ffffff; 
            }

            .col-foto-header, .celda-foto { 
                display: none !important; 
            }

            .nome-piloto { 
                display: none !important; 
            }

            .celda-nome { padding-left: 8px; }
            .celda-pontos { font-size: 1rem; }
            .nome-evento { font-size: 1.2rem; }
        }

        /* Actions */
        .actions { margin-top: 30px; display: flex; justify-content: center; }
        .back-btn {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: #fff; padding: 10px 25px; border-radius: 30px; cursor: pointer;
            font-weight: 700; font-size: 12px; display: flex; align-items: center; gap: 10px;
            transition: 0.3s;
            margin-bottom: 5%;
        }

        /* MELHORIA 20261110 (playoff) ------------------------------------------------ */
        :root { --cor-playoff: #00d2be; --cor-ouro: #f5c542; }

        /* MELHORIA 20261110 (playoff2): selo mais legível sobre qualquer cor de equipe */
        .selo-po {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 8px;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 0.66rem;
            font-weight: 700;
            font-style: normal;
            letter-spacing: 0.6px;
            line-height: 1.2;
            vertical-align: middle;
            background-color: #0b0b10;
            color: var(--cor-playoff);
            border: 1px solid var(--cor-playoff);
            white-space: nowrap;
            text-transform: uppercase;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
        }
        .selo-po .seed { opacity: 0.75; font-weight: 400; }
        .selo-po.vivo { background-color: var(--cor-playoff); color: #06332f; border-color: #0b0b10; box-shadow: 0 0 0 1px var(--cor-playoff), 0 1px 3px rgba(0, 0, 0, 0.35); }
        .selo-po.provisorio { border-style: dashed; }
        .selo-po.eliminado { color: #b4b4bc; border-color: #55555e; }
        .selo-po.vice { color: #15151e; background-color: #c9ccd3; border-color: #c9ccd3; }
        .selo-po.campeao { color: #15151e; background-color: var(--cor-ouro); border-color: var(--cor-ouro); }

        /* marcador na célula da posição */
        .linha-classificacao.linha-po-vivo .celda-posicao,
        .linha-classificacao.linha-po-provisorio .celda-posicao { box-shadow: inset 4px 0 0 var(--cor-playoff); }
        .linha-classificacao.linha-po-eliminado .celda-posicao { box-shadow: inset 4px 0 0 #55555e; }
        .linha-classificacao.linha-po-campeao .celda-posicao { box-shadow: inset 4px 0 0 var(--cor-ouro); }

        /* faixa de status do playoff acima da tabela de pilotos */
        .faixa-po {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: var(--cor-painel-f1);
            border: 1px solid #2b2b35;
            border-left: 4px solid var(--cor-playoff);
            padding: 8px 10px;
            margin-bottom: 10px;
        }
        .faixa-po[hidden] { display: none; }
        .faixa-po.encerrado { border-left-color: var(--cor-ouro); }
        .faixa-po-marca {
            flex-shrink: 0;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            padding: 3px 8px;
            border-radius: 4px;
            background-color: var(--cor-playoff);
            color: #06332f;
            text-transform: uppercase;
        }
        .faixa-po.encerrado .faixa-po-marca { background-color: var(--cor-ouro); color: #15151e; }
        .faixa-po-texto { flex: 1; min-width: 0; }
        .faixa-po-texto strong { display: block; font-size: 0.9rem; text-transform: uppercase; font-style: italic; letter-spacing: 0.5px; }
        .faixa-po-texto span { display: block; font-size: 0.74rem; color: #88888d; }
        .faixa-po-botao {
            flex-shrink: 0;
            background: none;
            border: 1px solid #3a3a45;
            color: #ffffff;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
        }
        .faixa-po-botao:hover { border-color: var(--cor-playoff); color: var(--cor-playoff); }
        @media (max-width: 450px) { .faixa-po-texto span { display: none; } }

        .linha-corte-po td {
            padding: 4px 0 !important;
            height: auto !important;
            text-align: center;
            font-size: 0.68rem;
            letter-spacing: 2px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--cor-playoff);
            border-bottom: 2px dashed var(--cor-playoff);
            background: none !important;
        }

        .po-topo {
            background-color: var(--cor-painel-f1);
            border-left: 4px solid var(--cor-playoff);
            padding: 10px 14px;
            margin-bottom: 14px;
        }
        .po-topo strong { display: block; font-size: 1rem; text-transform: uppercase; font-style: italic; letter-spacing: 1px; }
        .po-topo span { font-size: 0.8rem; color: #88888d; }

        .po-campeao {
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(90deg, rgba(245, 197, 66, 0.25), rgba(245, 197, 66, 0.03));
            border: 1px solid var(--cor-ouro);
            padding: 10px 14px;
            margin-bottom: 14px;
        }
        .po-campeao svg { width: 30px; height: 30px; fill: var(--cor-ouro); flex-shrink: 0; }
        .po-campeao small { display: block; color: var(--cor-ouro); font-size: 0.7rem; letter-spacing: 2px; font-weight: 700; text-transform: uppercase; }
        .po-campeao b { font-size: 1.2rem; text-transform: uppercase; font-style: italic; }

        .po-fase { margin-bottom: 18px; }
        .po-fase-cab {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            border-bottom: 2px solid #2b2b35;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .po-fase-cab h3 { font-size: 0.95rem; text-transform: uppercase; font-style: italic; letter-spacing: 1px; }
        .po-fase-cab span { font-size: 0.75rem; color: #88888d; font-weight: 700; }
        .po-corridas { font-size: 0.72rem; color: #6e6e78; margin-bottom: 8px; }
        .po-corridas .feita { color: #bbbbbc; }

        .po-grade { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .po-grade.unica { grid-template-columns: minmax(0, 320px); justify-content: center; }
        @media (max-width: 450px) { .po-grade { grid-template-columns: 1fr; } }

        .po-card { background-color: var(--cor-painel-f1); border: 1px solid #2b2b35; min-width: 0; }
        .po-card-rotulo { font-size: 0.65rem; color: #6e6e78; letter-spacing: 1.5px; font-weight: 700; padding: 4px 8px 0; text-transform: uppercase; }
        .po-lado {
            display: grid;
            grid-template-columns: 4px 22px minmax(0, 1fr) auto;
            align-items: center;
            gap: 6px;
            padding: 5px 8px 5px 0;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        .po-lado + .po-lado { border-top: 1px solid #1f1f28; }
        .po-faixa { align-self: stretch; }
        .po-seed { text-align: center; color: #88888d; font-weight: 700; font-size: 0.75rem; }
        .po-nome { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .po-nome b { font-weight: 900; }
        .po-nome .nome-piloto { font-size: 0.8rem; }
        .po-pts { font-weight: 700; font-style: italic; min-width: 30px; text-align: right; }
        .po-lado.vencedor .po-nome, .po-lado.vencedor .po-pts { color: var(--cor-playoff); }
        .po-lado.perdedor { opacity: 0.4; }
        .po-lado.lider .po-pts { color: var(--cor-playoff); }
        .po-lado.vazio .po-nome { color: #6e6e78; font-style: italic; text-transform: none; }
        .po-card-rodape { font-size: 0.68rem; color: #6e6e78; padding: 3px 8px 5px; border-top: 1px solid #1f1f28; text-align: right; }
        .po-card-rodape.encerrado { color: var(--cor-playoff); }
        .po-regras { font-size: 0.72rem; color: #6e6e78; line-height: 1.4; margin-top: 6px; }
    </style>
</head>
<body>

<div class="conteiner-principal">
    
    <header class="painel-corrida">
        <div class="navegacao-corrida">
            <button onclick="alterarCorrida('anterior')" class="botao-navegacao" id="btn-anterior" data-id="">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/></svg>
            </button>
            
            <div class="bloco-central-header">
                <div class="info-temporada" id="txt-temporada">---</div>
                <div class="alinhamento-titulo">
                    <img src="" id="img-bandeira" class="imagem-bandeira-header" alt="Bandeira">
                    <h1 class="nome-evento" id="txt-nome-corrida">Carregando...</h1>
                </div>
                <div class="localizacao-pista">
                    <svg class="icone-mapa" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                    <span id="txt-circuito">---</span>• <span id="rotulo-etapa">Etapa</span> <span id="etapa-numero">...</span>
                </div>
                @if($temSprint)
                    <span class="selo-tipo" id="selo-tipo">Classificação Geral</span>
                @endif
            </div>
            
            <button onclick="alterarCorrida('proximo')" class="botao-navegacao" id="btn-proximo" data-id="">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
            </button>
        </div>
    </header>

    {{-- MELHORIA 20261110: classificação geral (sem pontos de sprint) x classificação só das sprints --}}
    @if($temSprint)
    <nav class="seletor-tipo" id="seletor-tipo" role="tablist" aria-label="Tipo de classificação">
        <span class="indicador" aria-hidden="true"></span>
        <button class="aba-tipo ativo" data-tipo="principal" role="tab" aria-selected="true" onclick="alternarTipo(event, 'principal')">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg> Geral
        </button>
        <button class="aba-tipo" data-tipo="sprint" role="tab" aria-selected="false" onclick="alternarTipo(event, 'sprint')">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg> Sprints
        </button>
    </nav>
    @endif

    <nav class="abas-f1">
        <button class="aba-item ativo" onclick="alternarAba(event, 'aba-pilotos')">Pilotos</button>
        <button class="aba-item" onclick="alternarAba(event, 'aba-equipes')">Equipes</button>
        @if($temPlayoff)
            <button class="aba-item" id="aba-btn-playoff" onclick="alternarAba(event, 'aba-playoff')">Playoffs</button>
        @endif
    </nav>

    <main class="paineis-conteudo">
        <section id="aba-pilotos" class="conteudo-aba ativo">
            @if($temPlayoff)
                {{-- MELHORIA 20261110 (playoff2): status do playoff na classificação geral --}}
                <div class="faixa-po" id="faixa-playoff" hidden>
                    <span class="faixa-po-marca">Playoff</span>
                    <div class="faixa-po-texto"><strong id="faixa-playoff-titulo"></strong><span id="faixa-playoff-sub"></span></div>
                    <button type="button" class="faixa-po-botao" onclick="abrirAbaPlayoff()">Ver chave ›</button>
                </div>
            @endif
            <table class="tabela-f1">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">Pos</th>
                        <th class="col-foto-header" style="width: 48px;"></th>
                        <th style="width: 48px;"></th>
                        <th style="text-align: left; padding-left: 12px;">Piloto</th>
                        <th style="width: 75px; text-align: center;">Pts</th>
                        <th style="width: 45px; text-align: center;">Dif</th>
                    </tr>
                </thead>
                <tbody id="lista-pilotos"></tbody>
            </table>
        </section>
        
        <section id="aba-equipes" class="conteudo-aba">
            <table class="tabela-f1">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">Pos</th>
                        <th style="width: 48px;"></th>
                        <th style="text-align: left; padding-left: 12px;">Equipe</th>
                        <th style="width: 75px; text-align: center;">Pts</th>
                        <th style="width: 45px; text-align: center;">Dif</th>
                    </tr>
                </thead>
                <tbody id="lista-equipes"></tbody>
            </table>
        </section>

        @if($temPlayoff)
        {{-- MELHORIA 20261110 (playoff): chaveamento até a etapa exibida --}}
        <section id="aba-playoff" class="conteudo-aba">
            <div id="conteudo-playoff"></div>
        </section>
        @endif
    </main>

    <input type="hidden" name="post.temporadas.classificacao" id="post.temporadas.classificacao" value="{{route('post.temporadas.classificacao')}}">
    <input type="hidden" name="id" id="id" value="{{$id}}">
    
    <div class="actions">
        <button class="back-btn" onclick="window.history.back()">
            <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/></svg>
            VOLTAR
        </button>
    </div>
</div>

<script>
   window.Laravel = {
        baseUrl: '{{ asset('/') }}'
    };
</script>

<script>
    const ID_CORRIDA_INICIAL = null;
    let tipoClassificacao = 'principal'; // MELHORIA 20261110
    let ordemAtual = null;

    // MOCK ATUALIZADO: Sem classes CSS, agora com corFundo e corTexto puras
    const dbMock = {
        "9": {
            ano: "2025", evento: "São Paulo GP", circuito: "Autódromo de Interlagos", urlBandeira: "imagens/bandeiras/bra.png",
            idAnterior: null, idProximo: 10, temAnterior: false, temProximo: true,
            pilotos: [
                { pos: 1, nome: "Charles", sobrenome: "Leclerc", pontos: 310, foto: "imagens/pilotos/leclerc.png", logoEquipe: "imagens/equipes/ferrari.png", corFundo: "#e80020", corTexto: "#ffffff" },
                { pos: 2, nome: "Max", sobrenome: "Verstappen", pontos: 305, foto: "imagens/pilotos/verstappen.png", logoEquipe: "imagens/equipes/redbull.png", corFundo: "#0c1e43", corTexto: "#ffffff" },
                { pos: 3, nome: "Lando", sobrenome: "Norris", pontos: 290, foto: "imagens/pilotos/norris.png", logoEquipe: "imagens/equipes/mclaren.png", corFundo: "#ff8700", corTexto: "#000000" }
            ],
            equipes: [
                { pos: 1, nome: "Ferrari", pontos: 580, logoEquipe: "imagens/equipes/ferrari.png", corFundo: "#e80020", corTexto: "#ffffff" },
                { pos: 2, nome: "McLaren", pontos: 572, logoEquipe: "imagens/equipes/mclaren.png", corFundo: "#ff8700", corTexto: "#000000" }
            ]
        },
        "10": {
            ano: "2025", evento: "Las Vegas GP", circuito: "Las Vegas Strip Circuit", urlBandeira: "imagens/bandeiras/usa.jpg",
            idAnterior: 9, idProximo: 11, temAnterior: true, temProximo: true,
            pilotos: [
                { pos: 1, nome: "Max", sobrenome: "Verstappen", pontos: 393, foto: "imagens/pilotos/verstappen.png", logoEquipe: "imagens/equipes/redbull.png", corFundo: "#0c1e43", corTexto: "#ffffff" },
                { pos: 2, nome: "Lando", sobrenome: "Norris", pontos: 388, foto: "imagens/pilotos/norris.png", logoEquipe: "imagens/equipes/mclaren.png", corFundo: "#ff8700", corTexto: "#000000" },
                { pos: 3, nome: "Oscar", sobrenome: "Piastri", pontos: 385, foto: "imagens/pilotos/piastri.png", logoEquipe: "imagens/equipes/mclaren.png", corFundo: "#ff8700", corTexto: "#000000" }
            ],
            equipes: [
                { pos: 1, nome: "McLaren", pontos: 773, logoEquipe: "imagens/equipes/mclaren.png", corFundo: "#ff8700", corTexto: "#000000" },
                { pos: 2, nome: "Red Bull Racing", pontos: 393, logoEquipe: "imagens/equipes/redbull.png", corFundo: "#0c1e43", corTexto: "#ffffff" }
            ]
        },
        "11": {
            ano: "2025", evento: "Abu Dhabi GP", circuito: "Yas Marina Circuit", urlBandeira: "imagens/bandeiras/uae.png",
            idAnterior: 10, idProximo: null, temAnterior: true, temProximo: false,
            pilotos: [
                { pos: 1, nome: "Lando", sobrenome: "Norris", pontos: 423, foto: "imagens/pilotos/norris.png", logoEquipe: "imagens/equipes/mclaren.png", corFundo: "#ff8700", corTexto: "#000000" },
                { pos: 2, nome: "Max", sobrenome: "Verstappen", pontos: 421, foto: "imagens/pilotos/verstappen.png", logoEquipe: "imagens/equipes/redbull.png", corFundo: "#0c1e43", corTexto: "#ffffff" },
                { pos: 3, nome: "George", sobrenome: "Russell", pontos: 319, foto: "imagens/pilotos/russell.png", logoEquipe: "imagens/equipes/mercedes.png", corFundo: "#00d2be", corTexto: "#000000" }
            ],
            equipes: [
                { pos: 1, nome: "McLaren", pontos: 833, logoEquipe: "imagens/equipes/mclaren.png", corFundo: "#ff8700", corTexto: "#000000" },
                { pos: 2, nome: "Mercedes F1", pontos: 469, logoEquipe: "imagens/equipes/mercedes.png", corFundo: "#00d2be", corTexto: "#000000" }
            ]
        }
    };

    //Função que roda ao iniciar a tela; Busca os dados e monta na tabela
    async function atualizarPainel(idCorrida) {
        // setTimeout(() => {
        //     const dadosMockados = dbMock[idCorrida];
        //     if(dadosMockados) renderizarDados(dadosMockados);
        // }, 120); 

        // console.log(idCorrida)

        const url = document.getElementById('post.temporadas.classificacao').value
        const id = document.getElementById('id').value
        const token = document.querySelector('meta[name="csrf-token"]').content
        const req = await fetch(url, {
                method: 'POST',
                headers: {
                    'content-type' : 'application/json',
                    'x-csrf-token' : token
                },
                body: JSON.stringify({
                    id: id,
                    idCorrida:idCorrida, //até qual corrida estou buscando no backend
                    tipo: tipoClassificacao // MELHORIA 20261110: 'principal' (sem sprints) ou 'sprint'
                })
            })

        const res = await req.json();
        console.log(res)

        const dados = {

        }

        dados.ordem = res.dados.ordem
        ordemAtual = res.dados.ordem
        dados.total_corridas = res.dados.total_corridas
        dados.ano = res.dados.ano
        dados.des_temporada = res.dados.des_temporada
        dados.evento = res.dados.evento
        dados.circuito = res.dados.circuito
        dados.contagem_corrida = res.dados.contagem_corrida
        dados.url_bandeira = res.dados.url_bandeira

        dados.pilotos = [
            // { pos: 1, nome: "Lando", sobrenome: "Norris", pontos: 423, foto: "imagens/pilotos/norris.png", logoEquipe: "imagens/equipes/mclaren.png", corFundo: "#ff8700", corTexto: "#000000" },
            // { pos: 2, nome: "Max", sobrenome: "Verstappen", pontos: 421, foto: "imagens/pilotos/verstappen.png", logoEquipe: "imagens/equipes/redbull.png", corFundo: "#0c1e43", corTexto: "#ffffff" },
            // { pos: 3, nome: "George", sobrenome: "Russell", pontos: 319, foto: "imagens/pilotos/russell.png", logoEquipe: "imagens/equipes/mercedes.png", corFundo: "#00d2be", corTexto: "#000000" }
        ],

       dados.pilotos = res.dados.pilotos.map((piloto, index) => {
            const sobrenomeMinusculo = piloto.sobrenome.toLowerCase().replace("'", ""); // Trata nomes como O'Ward
            const fotoTratada = piloto.foto 
                ? piloto.foto 
                : `imagens/pilotos/${sobrenomeMinusculo}.jpg`;

            const logoTratado = piloto.logoEquipe 
                ? piloto.logoEquipe 
                : `imagens/equipes/default.png`;

            return {
                pos: index + 1, // O index começa em 0, então somamos 1 para a posição real
                piloto_id: piloto.piloto_id, // MELHORIA 20261110 (playoff)
                nome: piloto.nome,
                sobrenome: piloto.sobrenome,
                pontos: piloto.pontos,
                foto: fotoTratada,
                logoEquipe: logoTratado,
                corFundo: piloto.color, 
                corTexto: piloto.color === "#ffffff" ? "#000000" : "#ffffff" // Garante contraste para o seu próprio card!
            };
        }),

        dados.equipes = res.dados.equipes ? res.dados.equipes.map((equipe, index) => {
            return {
                pos: index + 1,
                nome: equipe.nome,
                pontos: equipe.pontos,
                logoEquipe: equipe.logoEquipe || `imagens/equipes/default.png`,
                corFundo: equipe.color,
                corTexto: equipe.color === "#ffffff" ? "#000000" : "#ffffff"
            };
        }) : []

        dados.temAnterior = false
        dados.temProximo = false
        dados.idAnterior = null
        dados.idProximo = null
        
        if (res.dados.ordem < res.dados.total_corridas) {
            dados.temProximo = true
            dados.temAnterior = true
            dados.idAnterior = parseInt(dados.ordem)-1
            dados.idProximo = parseInt(dados.ordem) +1
        }

        if (res.dados.ordem == res.dados.total_corridas) {
            dados.temAnterior = true
            dados.idAnterior = parseInt(dados.ordem)-1
        }

        if (res.dados.ordem == 1){
            dados.temAnterior = false
        }

        // MELHORIA 20261110 (nav sprint): no modo sprint as setas pulam direto para a sprint anterior/seguinte
        const nav = res.dados.navegacao
        if (nav) {
            dados.temAnterior = nav.anterior !== null
            dados.temProximo = nav.proximo !== null
            dados.idAnterior = nav.anterior
            dados.idProximo = nav.proximo
        }
        const rotuloEtapa = document.getElementById('rotulo-etapa')
        if (rotuloEtapa) rotuloEtapa.textContent = nav ? 'Sprint' : 'Etapa'

        dados.playoff = res.dados.playoff || null // MELHORIA 20261110 (playoff)

        console.log(dados)
        renderizarDados(dados)
        renderizarPlayoff(dados.playoff)
    }

    function renderizarDados(dados) {
        console.log('renderizando dados', dados)
        document.getElementById("txt-temporada").innerText = `${dados.des_temporada} • ${dados.ano}`;
        document.getElementById("img-bandeira").src = `${window.Laravel.baseUrl}images/${dados.url_bandeira}`;
        document.getElementById("txt-nome-corrida").innerText = dados.evento;
        document.getElementById("txt-circuito").innerText = dados.circuito;
        document.getElementById("etapa-numero").innerText = dados.contagem_corrida
        
        const btnAnt = document.getElementById("btn-anterior");
        const btnProx = document.getElementById("btn-proximo");

        btnAnt.disabled = !dados.temAnterior;
        btnProx.disabled = !dados.temProximo;

        btnAnt.setAttribute('data-id', dados.idAnterior || "");
        btnProx.setAttribute('data-id', dados.idProximo || "");

        // RENDER PILOTOS (Injetando cores inline nas TDs de conteúdo)
        let htmlPilotos = "";
        // MELHORIA 20261110 (playoff): marca os classificados só na classificação geral
        const po = (dados.playoff && tipoClassificacao === 'principal') ? dados.playoff : null;
        dados.pilotos.forEach((piloto, i) => {
            if (po && !po.iniciado && i === po.corte && dados.pilotos.length > po.corte) {
                htmlPilotos += `<tr class="linha-corte-po"><td colspan="6">▲ Zona de classificação para o playoff</td></tr>`;
            }
            let diferenca = "-";
            if (i > 0) { diferenca = piloto.pontos - dados.pilotos[0].pontos; }

            htmlPilotos += `
                <tr class="linha-classificacao ${classeLinhaPlayoff(po, piloto.piloto_id)}">
                    <td class="celda-posicao">${piloto.pos}</td>
                    <td class="celda-foto" style="background-color: ${piloto.corFundo};"><img src="${window.Laravel.baseUrl}images/${piloto.foto}" class="imagem-renderizada"></td>
                    <td class="celda-logo" style="background-color: ${piloto.corFundo};"><img src="${window.Laravel.baseUrl}images/${piloto.logoEquipe}" class="imagem-renderizada"></td>
                    <td class="celda-nome" style="background-color: ${piloto.corFundo}; color: ${piloto.corTexto};">
                        <span class="nome-piloto">${piloto.nome}</span>
                        <span class="sobrenome-piloto">${piloto.sobrenome}</span>${seloPlayoff(po, piloto.piloto_id)}
                    </td>
                    <td class="celda-pontos" style="background-color: ${piloto.corFundo}; color: ${piloto.corTexto};">${piloto.pontos}</td>
                    <td class="celda-diferenca">${diferenca}</td>
                </tr>
            `;
        });
        document.getElementById("lista-pilotos").innerHTML = htmlPilotos;
        renderizarFaixaPlayoff(po);

        // RENDER EQUIPES (Injetando cores inline nas TDs de conteúdo)
        let htmlEquipes = "";
        dados.equipes.forEach((equipe, i) => {
            let diferenca = "-";
            if (i > 0) { diferenca = equipe.pontos - dados.equipes[0].pontos; }

            htmlEquipes += `
                <tr class="linha-classificacao">
                    <td class="celda-posicao">${equipe.pos}</td>
                    <td class="celda-logo" style="background-color: ${equipe.corFundo};"><img src="${window.Laravel.baseUrl}images/${equipe.logoEquipe}" class="imagem-renderizada"></td>
                    <td class="celda-nome" style="background-color: ${equipe.corFundo}; color: ${equipe.corTexto};">${equipe.nome}</td>
                    <td class="celda-pontos" style="background-color: ${equipe.corFundo}; color: ${equipe.corTexto};">${equipe.pontos}</td>
                    <td class="celda-diferenca">${diferenca}</td>
                </tr>
            `;
        });
        document.getElementById("lista-equipes").innerHTML = htmlEquipes;
    }

    // MELHORIA 20261110 (playoff) ----------------------------------------------------
    function escPo(v) {
        const mapa = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return String(v ?? '').replace(/[&<>"']/g, c => mapa[c]);
    }

    function numPo(v) {
        if (v === null || v === undefined) return '';
        const n = Number(v);
        return Number.isInteger(n) ? n : n.toFixed(1);
    }

    // MELHORIA 20261110 (playoff2): selo ao lado do nome na tabela de pilotos
    function seloPlayoff(po, pilotoId) {
        if (!po || !po.situacao) return '';
        const s = po.situacao[pilotoId];
        if (!s) return '';
        const titulo = escPo(s.detalhe || ('Cabeça de chave ' + s.seed));
        if (s.status === 'provisorio') return `<span class="selo-po provisorio" title="Na zona de classificação: seria o cabeça de chave ${s.seed}">PO <span class="seed">#${s.seed}</span></span>`;
        if (s.status === 'campeao') return `<span class="selo-po campeao" title="Campeão do playoff (cabeça de chave ${s.seed})">🏆 Campeão</span>`;
        if (s.status === 'eliminado' && s.rotulo === 'Vice') return `<span class="selo-po vice" title="${titulo}">Vice PO</span>`;
        if (s.status === 'eliminado') return `<span class="selo-po eliminado" title="${titulo}">${escPo(s.rotulo)}</span>`;
        return `<span class="selo-po vivo" title="${titulo}">${escPo(s.rotulo || 'PO')} <span class="seed">#${s.seed}</span></span>`;
    }

    function classeLinhaPlayoff(po, pilotoId) {
        if (!po || !po.situacao || !po.situacao[pilotoId]) return '';
        return 'linha-po-' + po.situacao[pilotoId].status; // prefixo próprio: .po-campeao é o banner da chave
    }

    function renderizarFaixaPlayoff(po) {
        const faixa = document.getElementById('faixa-playoff');
        if (!faixa) return;
        faixa.hidden = !po;
        if (!po) return;
        const vivos = Object.values(po.situacao || {}).filter(s => s.status === 'vivo').length;
        let titulo, sub;
        if (!po.iniciado) {
            titulo = `Fase classificatória · ${po.classificatorias_feitas} de ${po.qtd_classificatorias}`;
            sub = `Os ${po.qtd_classificados} primeiros avançam ao mata-mata (só corridas principais)`;
        } else if (po.campeao) {
            titulo = `Campeão: ${po.campeao.nome} ${po.campeao.sobrenome}`;
            sub = po.vice ? `Vice: ${po.vice.nome} ${po.vice.sobrenome}` : 'Playoff encerrado';
        } else {
            titulo = po.fase_atual;
            sub = `${vivos} piloto${vivos === 1 ? '' : 's'} na disputa · ${po.qtd_classificados} classificados`;
        }
        faixa.classList.toggle('encerrado', !!po.campeao);
        document.getElementById('faixa-playoff-titulo').textContent = titulo;
        document.getElementById('faixa-playoff-sub').textContent = sub;
    }

    function abrirAbaPlayoff() {
        if (tipoClassificacao === 'sprint') alternarTipo(null, 'principal'); // playoff só existe no geral
        const botao = document.getElementById('aba-btn-playoff');
        if (botao) botao.click();
    }

    function ladoPlayoff(p, pontos, confronto, outro) {
        if (!p) return `<div class="po-lado vazio"><span class="po-faixa"></span><span class="po-seed">-</span><span class="po-nome">A definir</span><span class="po-pts"></span></div>`;
        let classe = '';
        if (confronto.encerrado && confronto.vencedor) {
            classe = confronto.vencedor.piloto_id === p.piloto_id ? 'vencedor' : 'perdedor';
        } else if (pontos !== null && outro !== null && pontos > outro) {
            classe = 'lider';
        }
        return `<div class="po-lado ${classe}">
                    <span class="po-faixa" style="background-color: ${escPo(p.cor || '#444')}"></span>
                    <span class="po-seed">${p.seed}</span>
                    <span class="po-nome" title="${escPo(p.nome + ' ' + p.sobrenome + ' · ' + (p.equipe || ''))}"><span class="nome-piloto">${escPo(p.nome)}</span> <b>${escPo(p.sobrenome)}</b></span>
                    <span class="po-pts">${numPo(pontos)}</span>
                </div>`;
    }

    function cardPlayoff(c, iniciado) {
        let rodape = '';
        if (c.encerrado) rodape = `<div class="po-card-rodape encerrado">Encerrado · ${c.total} de ${c.total} corridas</div>`;
        else if (iniciado && c.a && c.b) rodape = `<div class="po-card-rodape">${c.feitas} de ${c.total} corridas</div>`;
        return `<div class="po-card">
                    <div class="po-card-rotulo">${escPo(c.rotulo)}</div>
                    ${ladoPlayoff(c.a, c.pontos_a, c, c.pontos_b)}
                    ${ladoPlayoff(c.b, c.pontos_b, c, c.pontos_a)}
                    ${rodape}
                </div>`;
    }

    function cabecalhoFase(po, r) {
        const corridas = r.corridas.map((c, i) => c
            ? `<span class="${c.feita ? 'feita' : ''}">${c.feita ? '✔' : '○'} ${c.ordem}ª ${escPo(c.evento || '')}</span>`
            : `<span>○ corrida ${i + 1} a cadastrar</span>`).join(' · ');
        const info = po.iniciado ? `${r.feitas} de ${r.total} corridas` : `${r.total} corridas`;
        return `<div class="po-fase-cab"><h3>${r.nome}</h3><span>${info}</span></div>
                <div class="po-corridas">${corridas}</div>`;
    }

    function renderizarPlayoff(po) {
        const alvo = document.getElementById('conteudo-playoff');
        if (!alvo) return;
        if (!po) { alvo.innerHTML = ''; return; }

        let html = '';
        if (po.campeao) {
            html += `<div class="po-campeao">
                        <svg viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V19H7v2h10v-2h-4v-3.1a5.01 5.01 0 0 0 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>
                        <div><small>Campeão do playoff</small><b>${escPo(po.campeao.nome)} ${escPo(po.campeao.sobrenome)}</b></div>
                     </div>`;
        }

        const subtitulo = po.iniciado
            ? `${po.qtd_classificados} classificados · ${po.qtd_classificatorias} classificatórias + ${po.qtd_mata_mata} corridas de mata-mata (${po.qtd_corridas} no total)`
            : `Fase classificatória: ${po.classificatorias_feitas} de ${po.qtd_classificatorias} corridas · se terminasse agora, estes seriam os confrontos`;
        html += `<div class="po-topo">
                    <strong>${po.iniciado ? 'Playoff · ' + escPo(po.fase_atual) : 'Chaveamento provisório'}</strong>
                    <span>${subtitulo}</span>
                 </div>`;

        po.rodadas.forEach(r => {
            const unica = r.confrontos.length === 1 ? ' unica' : '';
            html += `<div class="po-fase">${cabecalhoFase(po, r)}<div class="po-grade${unica}">${r.confrontos.map(c => cardPlayoff(c, po.iniciado)).join('')}</div></div>`;
        });

        html += `<p class="po-regras">Classificam os ${po.qtd_classificados} primeiros da fase classificatória (só corridas principais, sem sprints).
                 Em cada confronto avança quem somar mais pontos nas 3 corridas da fase; em caso de empate, avança o melhor cabeça de chave.
                 O playoff não altera a classificação geral nem a das sprints.</p>`;

        alvo.innerHTML = html;
    }

    function alterarCorrida(direcao) {
        const idAlvo = document.getElementById(`btn-${direcao}`).getAttribute('data-id');
        if (idAlvo) {
            console.log('buscando corrida com ordem', idAlvo)
            atualizarPainel(idAlvo);
        }
    }

    function alternarAba(evento, idAba) {
        document.querySelectorAll('.conteudo-aba').forEach(aba => aba.classList.remove('ativo'));
        document.querySelectorAll('.aba-item').forEach(botao => botao.classList.remove('ativo'));
        document.getElementById(idAba).classList.add('ativo');
        evento.currentTarget.classList.add('ativo');
    }

    // MELHORIA 20261110: troca entre classificação geral e só sprints mantendo a etapa exibida
    function alternarTipo(evento, tipo) {
        if (tipo === tipoClassificacao) return;
        tipoClassificacao = tipo;
        document.querySelectorAll('.aba-tipo').forEach(botao => {
            const ativo = botao.dataset.tipo === tipo;
            botao.classList.toggle('ativo', ativo);
            botao.setAttribute('aria-selected', ativo ? 'true' : 'false');
        });
        const sprint = tipo === 'sprint';
        document.getElementById('seletor-tipo').classList.toggle('modo-sprint', sprint);
        document.body.classList.toggle('modo-sprint', sprint);
        const selo = document.getElementById('selo-tipo');
        if (selo) selo.textContent = sprint ? 'Classificação das Sprints' : 'Classificação Geral';
        // MELHORIA 20261110 (etapa sprint): ao trocar Geral/Sprints sempre mostra a última corrida do modo
        // (null = o servidor escolhe a última etapa no Geral e a última sprint com resultado no Sprints)
        const ordemAlvo = null;

        // MELHORIA 20261110 (playoff): sprints não têm playoff — a aba só existe na classificação geral
        const botaoPlayoff = document.getElementById('aba-btn-playoff');
        if (botaoPlayoff) {
            if (sprint && botaoPlayoff.classList.contains('ativo')) {
                document.querySelector('.aba-item').click(); // volta para Pilotos
            }
            botaoPlayoff.style.display = sprint ? 'none' : '';
        }
        atualizarPainel(ordemAlvo);
    }

    // MELHORIA 20261110: ?tipo=sprint (link do modal da listagem de temporadas) já abre na classificação das sprints
    if (new URLSearchParams(window.location.search).get('tipo') === 'sprint' && document.getElementById('seletor-tipo')) {
        alternarTipo(null, 'sprint'); // também carrega o painel
    } else {
        atualizarPainel(ID_CORRIDA_INICIAL);
    }

    // MELHORIA 20261110 (playoff2): ?aba=playoff (listagem de corridas / modal das temporadas) abre na chave
    if (new URLSearchParams(window.location.search).get('aba') === 'playoff') abrirAbaPlayoff();
</script>
</body>
</html>