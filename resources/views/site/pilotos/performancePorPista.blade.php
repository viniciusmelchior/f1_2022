@extends('layouts.main') {{-- Substitua pelo seu layout principal se necessário --}}

@section('section')
<div class="f1-painel-container">
    <!-- ESTILOS EXCLUSIVOS DO ECRÃ (Temática F1, Totalmente Responsivo & Sem conflito com Bootstrap) -->
    <style>
        :root {
            --f1-cor-primaria: #E10600;      /* Vermelho F1 */
            --f1-cor-escura: #15151E;        /* Preto de alta intensidade (Carbono) */
            --f1-cinza-grafite: #38383F;     /* Textos secundários */
            --f1-cinza-superficie: #F9F9FA;  /* Fundo de blocos */
            --f1-cinza-borda: #E0E0E2;       /* Bordas sutis */
            --f1-texto-claro: #FFFFFF;
            --f1-sucesso: #00A650;
        }

        .f1-painel-container {
            font-family: 'Titillium Web', 'Segoe UI', Roboto, Arial, sans-serif;
            color: var(--f1-cor-escura);
            padding: 15px;
            background-color: #fff;
            box-sizing: border-box;
            height: auto;
        }

        /* Cabeçalho Estilizado F1 */
        .f1-cabecalho {
            border-bottom: 4px solid var(--f1-cor-escura);
            padding-bottom: 12px;
            margin-bottom: 25px;
            position: relative;
        }

        .f1-cabecalho::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 70px;
            height: 4px;
            background-color: var(--f1-cor-primaria);
        }

        .f1-titulo-tela {
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            color: var(--f1-cor-escura);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Grid de Filtros e Painel de Controlo */
        .f1-filtro-grupo {
            background: var(--f1-cinza-superficie);
            border: 1px solid var(--f1-cinza-borda);
            border-left: 5px solid var(--f1-cor-primaria);
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .f1-filtro-grid {
            display: flex;
            flex-direction: row;
            align-items: flex-end;
            gap: 15px;
            width: 100%;
        }

        .f1-filtro-coluna {
            flex: 1;
            position: relative;
            min-width: 200px;
        }

        /* Coluna menor dedicada ao botão de limpar */
        .f1-filtro-coluna-acao {
            flex: 0 0 auto;
        }

        .f1-filtro-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
            color: var(--f1-cinza-grafite);
            letter-spacing: 0.5px;
        }

        /* COMPONENTE SELECT PESQUISÁVEL UNIFICADO */
        .f1-select-pesquisavel {
            position: relative;
            width: 100%;
        }

        .f1-input-busca {
            width: 100%;
            padding: 12px 35px 12px 12px;
            font-size: 14px;
            border: 1px solid var(--f1-cinza-borda);
            border-radius: 4px;
            background-color: #fff;
            box-sizing: border-box;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .f1-input-busca:focus {
            outline: none;
            border-color: var(--f1-cor-primaria);
            box-shadow: 0 0 0 3px rgba(225, 6, 0, 0.15);
        }

        /* Seta nativa simulada */
        .f1-select-pesquisavel::after {
            content: '▼';
            font-size: 10px;
            color: var(--f1-cinza-grafite);
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            transition: transform 0.2s ease;
        }
        
        .f1-select-pesquisavel.f1-aberto::after {
            transform: translateY(-50%) rotate(180deg);
        }

        /* Lista Flutuante de Opções */
        .f1-lista-opcoes {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid var(--f1-cor-escura);
            border-top: none;
            border-radius: 0 0 4px 4px;
            z-index: 999;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            margin-top: 2px;
        }

        .f1-opcao {
            padding: 10px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.15s ease;
            border-left: 3px solid transparent;
        }

        .f1-opcao:hover {
            background-color: var(--f1-cinza-superficie);
            border-left-color: var(--f1-cor-primaria);
            font-weight: 600;
        }

        .f1-opcao.f1-sem-resultado {
            color: var(--f1-cinza-grafite);
            font-style: italic;
            cursor: default;
            background-color: #fff !important;
            border-left: none;
        }

        /* BOTÃO LIMPAR FILTROS */
        .f1-btn-limpar {
            background-color: transparent;
            color: var(--f1-cinza-grafite);
            border: 1px solid var(--f1-cinza-borda);
            border-radius: 4px;
            height: 45px;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
            width: 100%;
        }

        .f1-btn-limpar:hover {
            background-color: var(--f1-cor-escura);
            color: var(--f1-texto-claro);
            border-color: var(--f1-cor-escura);
        }

        /* Mensagens de Feedback */
        .f1-status-container {
            margin-bottom: 25px;
        }

        .f1-alerta-info, .f1-alerta-vazio {
            padding: 15px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .f1-alerta-info {
            background-color: #E6F4EA;
            border-left: 4px solid var(--f1-sucesso);
            color: #137333;
        }

        .f1-alerta-vazio {
            background-color: #FCE8E6;
            border-left: 4px solid var(--f1-cor-primaria);
            color: #C5221F;
        }

        /* Bloco de Resultados */
        .f1-resultado-bloco {
            display: none;
        }

        /* Cards de Métricas */
        .f1-grid-cards {
            display: flex;
            flex-direction: row;
            gap: 20px;
            margin-bottom: 25px;
            width: 100%;
        }

        .f1-card-metric {
            flex: 1;
            background: var(--f1-cor-escura);
            color: var(--f1-texto-claro);
            padding: 20px;
            border-radius: 4px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .f1-card-metric::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--f1-cor-primaria) 70%, #fff 70%);
            background-size: 20px 100%;
        }

        .f1-card-titulo {
            font-size: 11px;
            text-transform: uppercase;
            color: #96969A;
            letter-spacing: 1px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .f1-card-valor {
            font-size: 38px;
            font-weight: 800;
            line-height: 1;
        }

        .f1-card-subtexto {
            font-size: 12px;
            margin-top: 6px;
        }

        /* Tabelas e Resultados */
        .f1-tabela-topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .f1-tabela-titulo {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--f1-cor-escura);
            border-left: 3px solid var(--f1-cor-primaria);
            padding-left: 8px;
            margin: 0;
        }

        /* Contador de resultados solicitados */
        .f1-contador-badge {
            background-color: var(--f1-cor-escura);
            color: var(--f1-texto-claro);
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .f1-tabela-responsiva {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--f1-cinza-borda);
            border-radius: 4px;
            -webkit-overflow-scrolling: touch; /* Suavidade no iOS */
        }

        .f1-tabela {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
            background-color: #fff;
            min-width: 500px; /* Garante estrutura legível sob scroll no telemóvel */
        }

        .f1-tabela th {
            background-color: var(--f1-cor-escura);
            color: var(--f1-texto-claro);
            padding: 14px 16px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            cursor: pointer;
            user-select: none;
        }

        .f1-tabela th:hover {
            background-color: var(--f1-cinza-grafite);
        }

        .f1-tabela th i {
            margin-left: 6px;
            font-size: 11px;
            color: #96969A;
        }

        .f1-tabela td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--f1-cinza-borda);
        }

        /* Efeito Hover na Tabela */
        .f1-tabela tbody tr:hover {
            background-color: rgba(225, 6, 0, 0.04);
        }

        .f1-col-numero {
            text-align: center;
            width: 110px;
        }

        .f1-col-acoes {
            text-align: center;
            width: 90px;
        }

        .f1-btn-acao {
            color: var(--f1-cinza-grafite);
            font-size: 18px;
            text-decoration: none;
            display: inline-block;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .f1-btn-acao:hover {
            color: var(--f1-cor-primaria);
            transform: scale(1.2);
        }

        .f1-badge-posicao {
            display: inline-block;
            padding: 3px 8px;
            font-weight: 700;
            border-radius: 3px;
            background-color: var(--f1-cinza-superficie);
            border: 1px solid var(--f1-cinza-borda);
            min-width: 35px;
            text-align: center;
        }

        .f1-badge-posicao.f1-podio {
            background-color: #FFF7E6;
            border-color: #FFA500;
            color: #D48800;
        }

        /* MEDIA QUERIES PARA TOTAL RESPONSIVIDADE EM DISPOSITIVOS MÓVEIS (TELEMÓVEL) */
        @media (max-width: 768px) {
            .f1-filtro-grid {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            
            .f1-filtro-coluna {
                width: 100%;
                min-width: 0;
            }

            .f1-btn-limpar {
                height: 42px;
                width: 100%;
            }

            .f1-grid-cards {
                flex-direction: column;
                gap: 12px;
            }

            .f1-card-valor {
                font-size: 32px;
            }

            .f1-tabela-topo {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }
        }
    </style>

    <!-- CABEÇALHO -->
    <div class="f1-cabecalho">
        <h1 class="f1-titulo-tela">
            <i class='bx bx-line-chart'></i> Desempenho do Piloto por Pista
        </h1>
    </div>

    <!-- FILTROS COM BUSCA UNIFICADA INSIDE SELECT & BOTAO LIMPAR -->
    <div class="f1-filtro-grupo">
        <div class="f1-filtro-grid">
            
            <!-- Dropdown Piloto -->
            <div class="f1-filtro-coluna">
                <label class="f1-filtro-label">Selecione o Piloto</label>
                <div class="f1-select-pesquisavel" id="f1_dropdown_piloto">
                    <input type="text" class="f1-input-busca" placeholder="Clique ou digite para pesquisar piloto..." autocomplete="off">
                    <input type="hidden" id="valor_piloto" value="">
                    
                    <div class="f1-lista-opcoes">
                        @foreach ($pilotos as $piloto )
                            <div class="f1-opcao" data-value="{{$piloto->id}}">{{$piloto->nome}} {{$piloto->sobrenome}}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Dropdown Pista -->
            <div class="f1-filtro-coluna">
                <label class="f1-filtro-label">Selecione a Pista</label>
                <div class="f1-select-pesquisavel" id="f1_dropdown_pista">
                    <input type="text" class="f1-input-busca" placeholder="Clique ou digite para pesquisar pista..." autocomplete="off">
                    <input type="hidden" id="valor_pista" value="">
                    
                    <div class="f1-lista-opcoes">
                        @foreach ($pistas as $pista)
                            <div class="f1-opcao" data-value="{{$pista->id}}">{{$pista->nome}}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Botão de Limpar Integrado -->
            <div class="f1-filtro-coluna-acao">
                <button type="button" class="f1-btn-limpar" id="f1_btn_limpar_filtros" title="Limpar todos os campos">
                    <i class='bx bx-eraser'></i> Limpar
                </button>
            </div>

        </div>
    </div>

    <!-- PAINEL DE STATUS / MENSAGENS -->
    <div class="f1-status-container">
        <div id="f1_msg_aguardando" class="f1-alerta-info">
            <i class='bx bx-info-circle'></i> Selecione obrigatoriamente um Piloto e uma Pista.
        </div>
        
        <div id="f1_msg_sem_registro" class="f1-alerta-vazio" style="display: none;">
            <i class='bx bx-error-circle'></i> Não existem registros (piloto nunca correu na pista).
        </div>
    </div>

    <!-- BLOCO DE RESULTADOS -->
    <div id="f1_bloco_resultados" class="f1-resultado-bloco">
        
        <!-- CARDS DE VITÓRIA E POLE (Textos secundários excluídos a pedido do utilizador) -->
        <div class="f1-grid-cards">
            <div class="f1-card-metric">
                <div class="f1-card-titulo">Vitórias</div>
                <div id="f1_card_vitorias_valor" class="f1-card-valor">0</div>
            </div>

            <div class="f1-card-metric">
                <div class="f1-card-titulo">Poles</div>
                <div id="f1_card_poles_valor" class="f1-card-valor">0</div>
                <div id="f1_card_poles_subtexto" class="f1-card-subtexto" style="display: none;"></div>
            </div>
        </div>

        <!-- TOPO DA TABELA COM CONTADOR DINÂMICO -->
        <div class="f1-tabela-topo">
            <h2 class="f1-tabela-titulo">Histórico de Posições</h2>
            <div id="f1_total_corridas_badge" class="f1-contador-badge">0 Corridas disputadas</div>
        </div>

        <!-- TABELA DETALHADA -->
        <div class="f1-tabela-responsiva">
            <table class="f1-tabela" id="f1_tabela_dados">
                <thead>
                    <tr>
                        <th onclick="ordenarTabela(0)">Temporada <i class='bx bx-sort'></i></th>
                        <th onclick="ordenarTabela(1)" class="f1-col-numero">Ano <i class='bx bx-sort'></i></th>
                        <th onclick="ordenarTabela(2)" class="f1-col-numero">Largada <i class='bx bx-sort'></i></th>
                        <th onclick="ordenarTabela(3)" class="f1-col-numero">Chegada <i class='bx bx-sort'></i></th>
                        <th class="f1-col-acoes">Ações</th>
                    </tr>
                </thead>
                <tbody id="f1_tabela_corpo">
                    <!-- Gerado dinamicamente por JS -->
                </tbody>
            </table>
        </div>

    </div>
</div>

<input type="hidden" name="buscaPerformancePorPista" id="buscaPerformancePorPista" value="{{route('buscaPerformancePorPista')}}">

<!-- LOGICA DINÂMICA COMPLETA -->
<script>
    const urlFormatoCorrida = "{{ route('resultados.show', ':id') }}";
    // Mock estruturado para validações reativas rápidas
    const BANCO_MOCK_F1 = {
        "1_101": { vitorias: 8, poles: 7, corridas: [
            { temporada: "F1 World Championship", ano: 2021, largada: 2, chegada: 1, link: "#" },
            { temporada: "F1 World Championship", ano: 2020, largada: 1, chegada: 1, link: "#" },
            { temporada: "F1 World Championship", ano: 2019, largada: 2, chegada: 1, link: "#" }
        ]},
        "2_102": { vitorias: 2, poles: 1, corridas: [
            { temporada: "F1 World Championship", ano: 2023, largada: 1, chegada: 1, link: "#" },
            { temporada: "F1 World Championship", ano: 2022, largada: 2, chegada: 3, link: "#" }
        ]},
        // Caso de teste específico: Histórico existente mas SEM POLE
        "3_103": { vitorias: 0, poles: 0, corridas: [
            { temporada: "F1 World Championship", ano: 2022, largada: 4, llegada: 5, link: "#" }
        ]}
    };

    document.addEventListener("DOMContentLoaded", function() {
        inicializarDropdownPesquisavel("f1_dropdown_piloto");
        inicializarDropdownPesquisavel("f1_dropdown_pista");

        // Evento para o botão de limpar campos
        document.getElementById("f1_btn_limpar_filtros").addEventListener("click", limparTodosCampos);
    });

    // Converte e gere o comportamento do Select Pesquisável Unificado
    function inicializarDropdownPesquisavel(containerId) {
        const container = document.getElementById(containerId);
        const inputBusca = container.querySelector(".f1-input-busca");
        const inputOculto = container.querySelector("input[type='hidden']");
        const lista = container.querySelector(".f1-lista-opcoes");
        const opcoes = container.querySelectorAll(".f1-opcao");

        // Expande ou recolhe a listagem flutuante
        inputBusca.addEventListener("click", function(e) {
            e.stopPropagation();
            fecharTodosDropdowns(containerId);
            container.classList.toggle("f1-aberto");
            lista.style.display = container.classList.contains("f1-aberto") ? "block" : "none";
        });

        // Filtragem interna ao digitar dentro do próprio select
        inputBusca.addEventListener("input", function() {
            const termo = inputBusca.value.toLowerCase();
            let encontrou = false;

            const avisoAntigo = lista.querySelector(".f1-sem-resultado");
            if (avisoAntigo) avisoAntigo.remove();

            opcoes.forEach(opcao => {
                if (opcao.textContent.toLowerCase().includes(termo)) {
                    opcao.style.display = "block";
                    encontrou = true;
                } else {
                    opcao.style.display = "none";
                }
            });

            if (!encontrou) {
                const divAviso = document.createElement("div");
                divAviso.className = "f1-opcao f1-sem-resultado";
                divAviso.textContent = "Nenhum resultado encontrado";
                lista.appendChild(divAviso);
            }
            
            container.classList.add("f1-aberto");
            lista.style.display = "block";
        });

        // Evento disparado ao selecionar um item da lista
        opcoes.forEach(opcao => {
            opcao.addEventListener("click", function(e) {
                e.stopPropagation();
                inputBusca.value = opcao.textContent;
                inputOculto.value = opcao.getAttribute("data-value");
                
                lista.style.display = "none";
                container.classList.remove("f1-aberto");

                // Validação e atualização síncrona imediata
                verificarEAtualizarPainel();
            });
        });
    }

    function fecharTodosDropdowns(exceptId = null) {
        document.querySelectorAll(".f1-select-pesquisavel").forEach(container => {
            if (exceptId && container.id === exceptId) return;
            container.classList.remove("f1-aberto");
            const lista = container.querySelector(".f1-lista-opcoes");
            if (lista) lista.style.display = "none";
        });
    }

    document.addEventListener("click", function() {
        fecharTodosDropdowns();
    });

    // Função de limpeza absoluta de dados solicitada
    function limparTodosCampos() {
        // Reseta Inputs de Busca e Valores Ocultos dos Dropdowns
        document.querySelectorAll(".f1-select-pesquisavel").forEach(container => {
            container.querySelector(".f1-input-busca").value = "";
            container.querySelector("input[type='hidden']").value = "";
            // Restaura visibilidade das opções da lista interna
            container.querySelectorAll(".f1-opcao").forEach(opt => opt.style.display = "block");
            const aviso = container.querySelector(".f1-sem-resultado");
            if (aviso) aviso.remove();
        });

        // Restaura os blocos de mensagens para o estado inicial padrão
        document.getElementById("f1_msg_aguardando").style.display = "flex";
        document.getElementById("f1_msg_sem_registro").style.display = "none";
        document.getElementById("f1_bloco_resultados").style.display = "none";
    }

    // Processamento central das validações obrigatórias e renderização do painel
    async function verificarEAtualizarPainel() {
        const idPiloto = document.getElementById("valor_piloto").value;
        const idPista = document.getElementById("valor_pista").value;

        const msgAguardando = document.getElementById("f1_msg_aguardando");
        const msgSemRegistro = document.getElementById("f1_msg_sem_registro");
        const blocoResultados = document.getElementById("f1_bloco_resultados");

        // Reset de visibilidade dos alertas
        msgAguardando.style.display = "none";
        msgSemRegistro.style.display = "none";
        blocoResultados.style.display = "none";

        // Verifica a obrigatoriedade absoluta dos dois campos selecionados
        if (!idPiloto || !idPista) {
            msgAguardando.style.display = "flex";
            return;
        }

        const chaveBusca = `${idPiloto}_${idPista}`;
        // const dados = BANCO_MOCK_F1[chaveBusca];
        const dados = await buscaDados(idPiloto, idPista)

        console.log(dados)

        // Regra de Negócio: Se o piloto não tiver corridas gravadas nesta pista
        if (!dados || !dados.corridas || dados.corridas.length === 0) {
            msgSemRegistro.style.display = "flex";
            return;
        }

        // Atualização dos valores nos Cards principais (Sem subtextos deletérios)
        document.getElementById("f1_card_vitorias_valor").textContent = dados.vitorias;
        document.getElementById("f1_card_poles_valor").textContent = dados.poles;
        
        const subtextoPole = document.getElementById("f1_card_poles_subtexto");

        // Regra de Negócio: Piloto sem pole na pista selecionada
        // if (dados.poles === 0) {
        //     subtextoPole.textContent = "Piloto sem pole na pista selecionada";
        //     subtextoPole.style.color = "var(--f1-cor-primaria)";
        //     subtextoPole.style.fontWeight = "700";
        //     subtextoPole.style.display = "block";
        // } else {
        //     subtextoPole.textContent = "";
        //     subtextoPole.style.display = "none";
        // }

        // Injeta a quantidade total de resultados coletados (Corridas Disputadas)
        const totalCorridas = dados.corridas.length;
        document.getElementById("f1_total_corridas_badge").textContent = `${totalCorridas} ${totalCorridas === 1 ? 'Corrida disputada' : 'Corridas disputadas'}`;

        // Construção dinâmica das linhas da tabela histórica
        const corpoTabela = document.getElementById("f1_tabela_corpo");
        corpoTabela.innerHTML = "";

        dados.corridas.forEach(corrida => {
            const tr = document.createElement("tr");
            const urlFinal = urlFormatoCorrida.replace(':id', corrida.link);
            const badgeLargada = corrida.largada <= 3 ? "f1-badge-posicao f1-podio" : "f1-badge-posicao";
            const badgeChegada = corrida.chegada <= 3 ? "f1-badge-posicao f1-podio" : "f1-badge-posicao";

            tr.innerHTML = `
                <td>${corrida.temporada}</td>
                <td class="f1-col-numero">${corrida.ano}</td>
                <td class="f1-col-numero"><span class="${badgeLargada}">${corrida.largada}º</span></td>
                <td class="f1-col-numero"><span class="${badgeChegada}">${corrida.chegada}º</span></td>
                <td class="f1-col-acoes">
                    <a href="${urlFinal}" class="f1-btn-acao" title="Ver detalhes da corrida">
                        <i class='bx bx-show'></i>
                    </a>
                </td>
            `;
            corpoTabela.appendChild(tr);
        });

        // Ativa exibição do bloco com os dados organizados
        blocoResultados.style.display = "block";
    }

    //função ajax que busca as informações no backend
    async function buscaDados(idPiloto, idPista){

        const url = document.getElementById('buscaPerformancePorPista').value
        const token = document.querySelector('meta[name="csrf-token"]').content
        const req = await fetch(url, {
                method: 'POST',
                headers: {
                    'content-type' : 'application/json',
                    'x-csrf-token' : token
                },
                body: JSON.stringify({
                    idPiloto: idPiloto,
                    idPista:idPista //até qual corrida estou buscando no backend
                })
            })

        const res = await req.json();
        console.log(res)

        const dados = {
            
        }

        dados.vitorias = res.vitorias
        dados.poles = res.poles
        // dados.corridas = [
        //     { temporada: "F1 World Championship", ano: 2021, largada: 2, chegada: 1, link: "#" },
        //     { temporada: "F1 World Championship", ano: 2020, largada: 1, chegada: 1, link: "#" },
        //     { temporada: "F1 World Championship", ano: 2019, largada: 2, chegada: 1, link: "#" }
        // ]

        dados.corridas = res.dados.map((corrida, index) => { 
            return{
                temporada: corrida.des_temporada,
                ano: corrida.referencia,
                largada: corrida.largada,
                chegada: corrida.chegada,
                link: corrida.link
            }
        })

        return dados;
    }

    // Algoritmo de ordenação estruturada das tabelas por coluna (Atualizado para Ordenação Natural)
    let ordenacaoAscendente = true;
    function ordenarTabela(indiceColuna) {
        const corpo = document.getElementById("f1_tabela_corpo");
        const linhas = Array.from(corpo.rows);
        
        ordenacaoAscendente = !ordenacaoAscendente;

        linhas.sort((linhaA, lineB) => {
            let valA = linhaA.cells[indiceColuna].textContent.replace('º', '').trim();
            let valB = lineB.cells[indiceColuna].textContent.replace('º', '').trim();

            // Se ambos forem números puros (como o Ano), mantém a subtração matemática rápida
            if (!isNaN(valA) && !isNaN(valB)) {
                return ordenacaoAscendente ? Number(valA) - Number(valB) : Number(valB) - Number(valA);
            }
            
            // CORREÇÃO: Usando { numeric: true } para fazer ordenação natural (ex: 2 vem antes de 11)
            return ordenacaoAscendente 
                ? valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' }) 
                : valB.localeCompare(valA, undefined, { numeric: true, sensitivity: 'base' });
        });

        linhas.forEach(linha => corpo.appendChild(linha));
    }
</script>
@endsection
