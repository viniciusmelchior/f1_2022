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
                    <span id="txt-circuito">---</span>• Etapa <span id="etapa-numero">...</span>
                </div>
            </div>
            
            <button onclick="alterarCorrida('proximo')" class="botao-navegacao" id="btn-proximo" data-id="">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
            </button>
        </div>
    </header>

    <nav class="abas-f1">
        <button class="aba-item ativo" onclick="alternarAba(event, 'aba-pilotos')">Pilotos</button>
        <button class="aba-item" onclick="alternarAba(event, 'aba-equipes')">Equipes</button>
    </nav>

    <main class="paineis-conteudo">
        <section id="aba-pilotos" class="conteudo-aba ativo">
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
                    idCorrida:idCorrida //até qual corrida estou buscando no backend
                })
            })

        const res = await req.json();
        console.log(res)

        const dados = {

        }

        dados.ordem = res.dados.ordem
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

        console.log(dados)
        renderizarDados(dados)
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
        dados.pilotos.forEach((piloto, i) => {
            let diferenca = "-";
            if (i > 0) { diferenca = piloto.pontos - dados.pilotos[0].pontos; }

            htmlPilotos += `
                <tr class="linha-classificacao">
                    <td class="celda-posicao">${piloto.pos}</td>
                    <td class="celda-foto" style="background-color: ${piloto.corFundo};"><img src="${window.Laravel.baseUrl}images/${piloto.foto}" class="imagem-renderizada"></td>
                    <td class="celda-logo" style="background-color: ${piloto.corFundo};"><img src="${window.Laravel.baseUrl}images/${piloto.logoEquipe}" class="imagem-renderizada"></td>
                    <td class="celda-nome" style="background-color: ${piloto.corFundo}; color: ${piloto.corTexto};">
                        <span class="nome-piloto">${piloto.nome}</span>
                        <span class="sobrenome-piloto">${piloto.sobrenome}</span>
                    </td>
                    <td class="celda-pontos" style="background-color: ${piloto.corFundo}; color: ${piloto.corTexto};">${piloto.pontos}</td>
                    <td class="celda-diferenca">${diferenca}</td>
                </tr>
            `;
        });
        document.getElementById("lista-pilotos").innerHTML = htmlPilotos;

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

    atualizarPainel(ID_CORRIDA_INICIAL);
</script>
</body>
</html>