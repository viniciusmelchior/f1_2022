@php 
    use App\Models\Site\Resultado;
    use App\Models\Site\Temporada;
    use App\Models\Site\Titulo;
    use App\Models\Site\Corrida;
    use App\Models\Site\PilotoEquipe;
@endphp

@extends('layouts.main')

<style>
        /* Importando uma fonte que lembre a identidade visual moderna do automobilismo */
    @import url('https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700&display=swap');

    /* Regra vital para manter seu JS (toggle) funcionando após remover o Bootstrap */
    .d-none {
        display: none !important;
    }

    /* Container Principal */
    .f1-container {
        font-family: 'Titillium Web', sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        color: #15151e; /* Preto F1 */
        background-color: #f4f4f4;
    }

    /* Headers das Sessões (Estilo F1 Bold) */
    .f1-section-header {
        font-size: 2rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #15151e;
        margin: 30px 0 20px;
        padding-left: 15px;
        border-left: 8px solid #e10600; /* Vermelho F1 */
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .f1-toggle {
        color: #e10600;
        cursor: pointer;
        font-size: 1.5rem;
        transition: transform 0.2s;
    }
    .f1-toggle:hover {
        transform: scale(1.1);
    }

    /* Layout Grid para colocar tabelas lado a lado */
    .f1-grid {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Duas colunas de tamanho igual */
        gap: 30px;
        margin-bottom: 40px;
    }

    /* Em telas pequenas, as tabelas empilham uma embaixo da outra */
    @media (max-width: 992px) {
        .f1-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Cards (Caixas brancas) */
    .f1-card {
        background-color: #ffffff;
        border-top-right-radius: 20px; /* Detalhe aerodinâmico F1 */
        border-bottom-left-radius: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 25px;
        border-right: 4px solid #e10600;
        border-bottom: 4px solid #e10600;
    }

    .f1-card h1.descricao-tabela {
        font-size: 1.5rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #333;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
        margin-top: 0;
        margin-bottom: 20px;
    }

    /* Agrupamento de Filtros */
    .f1-filters {
        display: flex;
        flex-direction: column;
        gap: 15px;
        background: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .f1-filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .f1-filter-group label {
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        color: #555;
    }

    .f1-filter-group input, 
    .f1-filter-group select {
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-family: inherit;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .f1-filter-group input:focus, 
    .f1-filter-group select:focus {
        border-color: #e10600;
    }

    .f1-filter-group input[type="number"] {
        width: 80px;
        text-align: center;
    }

    /* Filtros da Seção Resultados */
    .f1-results-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: flex-end;
        margin-bottom: 25px;
        border-right: none;
        border-bottom: none;
        border-left: 4px solid #e10600; /* Inverte o lado para destaque */
    }

    /* Tabelas estilo F1 */
    .f1-table-wrapper {
        overflow-x: auto; /* Permite scroll horizontal se a tabela for muito larga */
    }

    .f1-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
    }

    .f1-table thead {
        background-color: #15151e;
        color: #ffffff;
    }

    .f1-table th {
        padding: 12px 15px;
        text-align: left;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .f1-table th:first-child {
        text-align: center;
    }

    .f1-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        color: #333;
        font-weight: 600;
    }

    .f1-table td:first-child {
        text-align: center;
    }

    /* Listras e Hover na tabela */
    .f1-table tbody tr:nth-child(even) {
        background-color: #fbfbfb;
    }

    .f1-table tbody tr:hover {
        background-color: #f5f5f5;
        border-left: 3px solid #e10600;
    }

    /* Paginação (Ajuste conforme o retorno do seu render de paginação) */
    .f1-pagination {
        display: flex;
        justify-content: flex-end;
        gap: 5px;
        margin-top: 20px;
    }

    .mt-4 { margin-top: 1.5rem; }
    .mb-5 { margin-bottom: 3rem; }

    /* =========================================
   ESTILO DA PAGINAÇÃO F1
   ========================================= */
.f1-pagination {
    display: flex;
    justify-content: center; /* Centraliza os botões */
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 30px;
}

.botaoPaginacao {
    background-color: #15151e; /* Preto F1 */
    color: #ffffff;
    border: 2px solid #15151e;
    padding: 8px 16px;
    font-family: 'Titillium Web', sans-serif;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s ease-in-out;
    text-decoration: none;
}

/* Efeito ao passar o mouse */
.botaoPaginacao:hover {
    background-color: #e10600; /* Vermelho F1 */
    border-color: #e10600;
    color: #ffffff;
}

/* Substitui o estilo feio do bg-danger nativo do bootstrap */
.botaoPaginacao.bg-danger {
    background-color: #e10600 !important;
    border-color: #e10600 !important;
    color: #ffffff !important;
}

.botaoPaginacao:disabled {
    background-color: #cccccc;
    border-color: #cccccc;
    cursor: not-allowed;
}


    /* =========================================
   AJUSTES DE RESPONSIVIDADE (MOBILE)
   ========================================= */
    @media (max-width: 768px) {
        .f1-section-header {
            font-size: 1.5rem;
        }
        
        .f1-card {
            padding: 15px; /* Reduz o padding interno em telas pequenas */
        }
        
        /* Faz os filtros empilharem verticalmente no celular */
        .f1-filter-group {
            flex-direction: column;
            align-items: stretch;
        }
        
        .f1-filter-group label {
            margin-bottom: -5px; /* Aproxima a label do input */
        }
        
        /* Faz inputs e selects ocuparem 100% da largura no mobile */
        .f1-filter-group input,
        .f1-filter-group select,
        .f1-filter-group input[type="number"] {
            width: 100% !important; 
        }
        
        .f1-results-filters {
            flex-direction: column;
            align-items: stretch;
        }
    }     
    
    .f1-filter-group input[type="number"] {
        width: 120px; /* <-- Aumente de 80px para 120px */
        text-align: center;
    }
</style>


@section('section')
    <div id="loader"></div>
    <div id="content"></div>
    
    <div class="f1-container">

        <!-- SESSÃO: CHEGADAS -->
        <div class="f1-section-header">
            Chegadas <span id="toggle_chegadas" class="f1-toggle"><i class="bi bi-plus-circle" id="icon_chegadas"></i></span>
        </div>
        
        <div class="f1-grid d-none" id="div_chegadas">
            <!-- Chegadas: Pilotos -->
            <div class="f1-card">
                <h1 class="descricao-tabela">Pilotos</h1>
                
                <div class="f1-filters">
                    <div class="f1-filter-group">
                        <label for="inicioChegadaPilotos">Pos. Início</label>
                        <input type="number" name="inicioPosicaoChegadasPilotos" id="inicioChegadaPilotos" value="1" onchange="buscaChegadasPilotos()">
                        
                        <label for="fimChegadaPilotos">Pos. Fim</label>
                        <input type="number" name="fimPosicaoChegadasPilotos" id="fimChegadaPilotos" value="1" onchange="buscaChegadasPilotos()">
                    </div>
                    
                    <div class="f1-filter-group">
                        <label for="anoInicioChegadaPilotos">Ano Início</label>
                        <input type="number" id="anoInicioChegadaPilotos" placeholder="Ex: 2020" onchange="sincronizarEValidarAnos('anoInicioChegadaPilotos', 'anoFimChegadaPilotos'); buscaChegadasPilotos()">

                        <label for="anoFimChegadaPilotos">Ano Fim</label>
                        <input type="number" id="anoFimChegadaPilotos" placeholder="Ex: 2026" onchange="sincronizarEValidarAnos('anoInicioChegadaPilotos', 'anoFimChegadaPilotos'); buscaChegadasPilotos()">
                    </div>

                    <div class="f1-filter-group">
                        <select name="vitoriasPilotosPorTemporada" id="vitoriasPilotosPorTemporada" onchange="travarAnos('vitoriasPilotosPorTemporada', 'anoInicioChegadaPilotos', 'anoFimChegadaPilotos'); buscaChegadasPilotos()">
                            <option value="" selected id="selectTemporadaVitoriasPiloto">Todas as Temporadas</option>
                            @foreach($temporadas as $temporada)
                                <option value="{{$temporada->id}}">{{$temporada->des_temporada}} ({{$temporada->referencia}})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="f1-table-wrapper">
                    <table class="f1-table" id="tabelaChegadasPilotos">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Piloto</th>
                                <th>Chegadas</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyChegadaPilotos"></tbody>
                    </table>
                </div>
            </div>

            <!-- Chegadas: Equipes -->
            <div class="f1-card">
                <h1 class="descricao-tabela">Equipes</h1>
                
                <div class="f1-filters">
                    <div class="f1-filter-group">
                        <label for="inicioChegadaEquipes">Pos. Início</label>
                        <input type="number" name="inicioPosicaoChegadasEquipes" id="inicioChegadaEquipes" value="1" onchange="buscaChegadasEquipes()">
                        
                        <label for="fimChegadaEquipes">Pos. Fim</label>
                        <input type="number" name="fimPosicaoChegadasEquipes" id="fimChegadaEquipes" value="1" onchange="buscaChegadasEquipes()">
                    </div>

                    <div class="f1-filter-group">
                        <label for="anoInicioChegadaEquipes">Ano Início</label>
                        <input type="number" id="anoInicioChegadaEquipes" placeholder="Ex: 2020" onchange="sincronizarEValidarAnos('anoInicioChegadaEquipes', 'anoFimChegadaEquipes'); buscaChegadasEquipes()">

                        <label for="anoFimChegadaEquipes">Ano Fim</label>
                        <input type="number" id="anoFimChegadaEquipes" placeholder="Ex: 2026" onchange="sincronizarEValidarAnos('anoInicioChegadaEquipes', 'anoFimChegadaEquipes'); buscaChegadasEquipes()">
                    </div>

                    <div class="f1-filter-group">
                        <select name="vitoriasEquipesPorTemporada" id="vitoriasEquipesPorTemporada" onchange="travarAnos('vitoriasEquipesPorTemporada', 'anoInicioChegadaEquipes', 'anoFimChegadaEquipes'); buscaChegadasEquipes()">
                            <option value="" selected id="selectTemporadaVitoriasEquipe">Todas as Temporadas</option>
                            @foreach($temporadas as $temporada)
                                <option value="{{$temporada->id}}">{{$temporada->des_temporada}} ({{$temporada->referencia}})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="f1-table-wrapper">
                    <table class="f1-table" id="tabelaChegadasEquipes">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Equipe</th>
                                <th>Chegadas</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyChegadaEquipes"></tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- SESSÃO: LARGADAS -->
        <div class="f1-section-header">
            Largadas <span id="toggle_largadas" class="f1-toggle"><i class="bi bi-plus-circle" id="icon_largadas"></i></span>
        </div>

        <div class="f1-grid d-none" id="div_largadas">
            <!-- Largadas: Pilotos -->
            <div class="f1-card">
                <h1 class="descricao-tabela">Pilotos</h1>
                
                <div class="f1-filters">
                    <div class="f1-filter-group">
                        <label for="inicioLargadaPilotos">Pos. Início</label>
                        <input type="number" name="inicioPosicaoLargadasPilotos" id="inicioLargadaPilotos" value="1" onchange="buscaLargadasPilotos()">
                        
                        <label for="fimLargadaPilotos">Pos. Fim</label>
                        <input type="number" name="fimPosicaoLargadasPilotos" id="fimLargadaPilotos" value="1" onchange="buscaLargadasPilotos()">
                    </div>

                    <div class="f1-filter-group">
                        <label for="anoInicioLargadaPilotos">Ano Início</label>
                        <input type="number" id="anoInicioLargadaPilotos" placeholder="Ex: 2020" onchange="sincronizarEValidarAnos('anoInicioLargadaPilotos', 'anoFimLargadaPilotos'); buscaLargadasPilotos()">

                        <label for="anoFimLargadaPilotos">Ano Fim</label>
                        <input type="number" id="anoFimLargadaPilotos" placeholder="Ex: 2026" onchange="sincronizarEValidarAnos('anoInicioLargadaPilotos', 'anoFimLargadaPilotos'); buscaLargadasPilotos()">
                    </div>

                    <div class="f1-filter-group">
                        <select name="largadasPilotosPorTemporada" id="largadasPilotosPorTemporada" onchange="travarAnos('largadasPilotosPorTemporada', 'anoInicioLargadaPilotos', 'anoFimLargadaPilotos'); buscaLargadasPilotos()">
                            <option value="" selected id="selectLargadasPilotosPorTemporada">Todas as Temporadas</option>
                            @foreach($temporadas as $temporada)
                                <option value="{{$temporada->id}}">{{$temporada->des_temporada}} ({{$temporada->referencia}})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="f1-table-wrapper">
                    <table class="f1-table" id="tabelaLargadasPilotos">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Piloto</th>
                                <th>Largadas</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLargadaPilotos"></tbody>
                    </table>
                </div>
            </div>

            <!-- Largadas: Equipes -->
            <div class="f1-card">
                <h1 class="descricao-tabela">Equipes</h1>
                
                <div class="f1-filters">
                    <div class="f1-filter-group">
                        <label for="inicioLargadaEquipes">Pos. Início</label>
                        <input type="number" name="inicioPosicaoLargadasEquipes" id="inicioLargadaEquipes" value="1" onchange="buscaLargadasEquipes()">
                        
                        <label for="fimLargadaEquipes">Pos. Fim</label>
                        <input type="number" name="fimPosicaoLargadasEquipes" id="fimLargadaEquipes" value="1" onchange="buscaLargadasEquipes()">
                    </div>

                    <div class="f1-filter-group">
                        <label for="anoInicioLargadaEquipes">Ano Início</label>
                        <input type="number" id="anoInicioLargadaEquipes" placeholder="Ex: 2020" onchange="sincronizarEValidarAnos('anoInicioLargadaEquipes', 'anoFimLargadaEquipes'); buscaLargadasEquipes()">

                        <label for="anoFimLargadaEquipes">Ano Fim</label>
                        <input type="number" id="anoFimLargadaEquipes" placeholder="Ex: 2026" onchange="sincronizarEValidarAnos('anoInicioLargadaEquipes', 'anoFimLargadaEquipes'); buscaLargadasEquipes()">
                    </div>

                    <div class="f1-filter-group">
                        <select name="largadasEquipesPorTemporada" id="largadasEquipesPorTemporada" onchange="travarAnos('largadasEquipesPorTemporada', 'anoInicioLargadaEquipes', 'anoFimLargadaEquipes'); buscaLargadasEquipes()">
                            <option value="" selected id="selectLargadasEquipesPorTemporada">Todas as Temporadas</option>
                            @foreach($temporadas as $temporada)
                                <option value="{{$temporada->id}}">{{$temporada->des_temporada}} ({{$temporada->referencia}})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="f1-table-wrapper">
                    <table class="f1-table" id="tabelaLargadasEquipes">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Equipe</th>
                                <th>Largadas</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLargadaEquipes"></tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- SESSÃO: TÍTULOS -->
        <div class="f1-section-header">
            Títulos <span id="toggle_titulos" class="f1-toggle"><i class="bi bi-plus-circle" id="icon_titulos"></i></span>
        </div>

        <div class="f1-grid d-none" id="div_titulos">
            <!-- Títulos: Pilotos -->
            <div class="f1-card">
                <h1 class="descricao-tabela">Pilotos</h1>
                <div class="f1-table-wrapper mt-4">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Piloto</th>
                                <th>Títulos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($totalTitulosPorPiloto as $piloto_nome => $total_titulos_piloto)
                            <tr>
                                <td>#</td>
                                <td>{{$piloto_nome}}</td>
                                <td>{{$total_titulos_piloto}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> 
            
            <!-- Títulos: Equipes -->
            <div class="f1-card">
                <h1 class="descricao-tabela">Equipes</h1>
                <div class="f1-table-wrapper mt-4">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Equipe</th>
                                <th>Títulos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($totalTitulosPorEquipe as $equipe_nome => $total_titulos_equipe)
                            <tr>
                                <td>#</td>
                                <td>{{$equipe_nome}}</td>
                                <td>{{$total_titulos_equipe}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- SESSÃO: RESULTADOS -->
        <div class="f1-section-header" style="border-bottom: 2px solid #e10600; margin-top: 40px;">
            Resultados
        </div>

        <div class="f1-card f1-results-filters">
            <div class="f1-filter-group">
                <label for="busca">Busca Rápida</label>
                <input type="text" id="busca" placeholder="Busca" onkeyup="buscaResultadosCorrida()">
            </div>
            
            <div class="f1-filter-group">
                <label for="qtdResultados">Por página</label>
                <select id="qtdResultados" onchange="buscaResultadosCorrida()">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="17">17</option>
                    <option value="20">20</option>
                    <option value="25">25</option>
                    <option value="30">30</option>
                    <option value="36">36</option>
                    <option value="todos">Todos</option>
                </select>
            </div>
            
            <div class="f1-filter-group">
                <label for="temporada">Temporada</label>
                <select id="temporada" onchange="buscaResultadosCorrida()">
                    <option value="todas">Todas</option>
                    @foreach ($temporadas as $temporada)
                        <option value="{{$temporada->id}}">{{$temporada->des_temporada}} ({{$temporada->referencia}})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="f1-filter-group">
                <label for="flg_sprint">Sprint</label>
                <select id="flg_sprint" onchange="buscaResultadosCorrida()">
                    <option value="N">Não</option>
                    <option value="S">Sim</option>
                </select>
            </div>
        </div>

        <div class="f1-table-wrapper mb-5">
            <table id="tabelaResultadosCorridas" class="f1-table tabelaResultadosCorridas">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 10%;">Temporada</th>
                        <th style="width: 15%;">Pista</th>
                        <th>Pole Position</th>
                        <th>Primeiro</th>
                        <th>Segundo</th>
                        <th>Terceiro</th>
                        {{-- <th>Volta Mais Rápida</th> --}}
                    </tr>
                </thead>
                <tbody id="tbodyResultadoCorridas"></tbody>
            </table>
        </div>

        <div id="paginacao" class="f1-pagination"></div>

        <!-- HIDDEN INPUTS -->
        <input type="hidden" name="" id="url_busca" value="{{route('buscaResultadosCorrida')}}">
        <input type="hidden" name="" id="url_chegada_pilotos" value="{{route('url_chegada_pilotos')}}">
        <input type="hidden" name="" id="url_chegada_equipes" value="{{route('url_chegada_equipes')}}">
        <input type="hidden" name="" id="url_largada_pilotos" value="{{route('url_largada_pilotos')}}">
        <input type="hidden" name="" id="url_largada_equipes" value="{{route('url_largada_equipes')}}">
    </div>
@endsection

<script>
    
    // //ao abrir a pagina ja busca resultados
    window.onload = function() {
        buscaResultadosCorrida();
        buscaChegadasPilotos();
        buscaChegadasEquipes();
        buscaLargadasPilotos();
        buscaLargadasEquipes();

        $('#toggle_titulos').click(function (e) { 
            e.preventDefault();
            $('#div_titulos').toggleClass('d-none');

            var icon = $('#icon_titulos');
            if (icon.hasClass('bi-plus-circle')) {
                icon.removeClass('bi-plus-circle').addClass('bi-dash-circle');
            } else {
                icon.removeClass('bi-dash-circle').addClass('bi-plus-circle');
            }
        });

        $('#toggle_chegadas').click(function (e) { 
            e.preventDefault();
            $('#div_chegadas').toggleClass('d-none');

            var icon = $('#icon_chegadas');
            if (icon.hasClass('bi-plus-circle')) {
                icon.removeClass('bi-plus-circle').addClass('bi-dash-circle');
            } else {
                icon.removeClass('bi-dash-circle').addClass('bi-plus-circle');
            }
        });

        $('#toggle_largadas').click(function (e) { 
            e.preventDefault();
            $('#div_largadas').toggleClass('d-none');

            var icon = $('#icon_largadas');
            if (icon.hasClass('bi-plus-circle')) {
                icon.removeClass('bi-plus-circle').addClass('bi-dash-circle');
            } else {
                icon.removeClass('bi-dash-circle').addClass('bi-plus-circle');
            }
        });
    };

    async function buscaResultadosCorrida(url = null){
        
        let tbody = document.querySelector('#tbodyResultadoCorridas')
        tbody.innerHTML = ''; //limpa a tabela a cada clique
        let paginacao = document.getElementById('paginacao');
        paginacao.innerHTML = ''; //limpa os botões de paginação a cada clique

        let busca = document.getElementById('busca').value
        let qtdResultados = document.getElementById('qtdResultados').value
        url = url ? url : document.getElementById('url_busca').value;
        const token = document.querySelector('meta[name="csrf-token"]').content

        let temporada = document.getElementById('temporada').value
        let flg_sprint = document.getElementById('flg_sprint').value

        const loader = document.getElementById('loader');
        const content = document.getElementById('content');

        // Mostrar o loader
        loader.style.display = 'block';

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                busca: busca,
                qtdResultados:qtdResultados,
                temporada:temporada,
                flg_sprint:flg_sprint
            })
        })

        const res = await req.json();

        //preencher TBODY da tabela
        loader.style.display = 'none';
        preencherTbodyTabelaResultados(res.resultadosCorrida.data);
        preencherPaginacao(res.resultadosCorrida.links);

    }

    function preencherTbodyTabelaResultados(data){
      
        let tbody = document.querySelector('#tbodyResultadoCorridas')

        //se for dev usa uma url, se for produção usa outra
        // const urlImagens = 'http://127.0.0.1:8000/images/';
        const urlImagens = 'https://f1.vitorvasconcellos.com.br/images/';

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        data.forEach(element => {
            
            tbody.innerHTML += `<tr>
                                    <td>${element.ordem}</td>
                                    <td>${element.temporada}</td>
                                    <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.imagemPaisCorrida}" style="width: 25px; height:20px;">
                                        </span>${element.pista}
                                    </td>
                                    <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.equipePolePosition}" style="width: 25px; height:25px;">
                                        </span>${element.polePosition}
                                    </td>
                                   <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.equipePrimeiro}" style="width: 25px; height:25px;">
                                        </span>${element.primeiro}
                                    </td>
                                    <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.equipeSegundo}" style="width: 25px; height:25px;">
                                        </span>${element.segundo}
                                    </td>
                                    <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.equipeTerceiro}" style="width: 25px; height:25px;">
                                        </span>${element.terceiro}
                                    </td>
                                </tr>`
        });
    }

    async function buscaChegadasPilotos(inicio = 1, fim = 1){
        inicio = document.getElementById('inicioChegadaPilotos').value
        fim = document.getElementById('fimChegadaPilotos').value
        let temporada_id = document.getElementById('vitoriasPilotosPorTemporada').value
        url = document.getElementById('url_chegada_pilotos').value;
        const token = document.querySelector('meta[name="csrf-token"]').content

        let anoInicioChegadaPilotos = document.getElementById('anoInicioChegadaPilotos').value;
        let anoFimChegadaPilotos = document.getElementById('anoFimChegadaPilotos').value;

        if(inicio > fim){
            alert("Posição final não pode ser maior que a inicial")
            return
        }

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                inicio:inicio,
                fim:fim,
                temporada_id:temporada_id,
                anoInicioChegadaPilotos:anoInicioChegadaPilotos,
                anoFimChegadaPilotos:anoFimChegadaPilotos

            })
        })

        const res = await req.json();

        let tbody = document.querySelector('#tbodyChegadaPilotos')

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        res.totalVitoriasPorPiloto.forEach((piloto, index) => {
            
            tbody.innerHTML += `<tr>
                                    <td>${index+1}</td>
                                    <td>${piloto.piloto_nome_completo}</td>
                                    <td>${piloto.chegadas}</td>
                                </tr>`
        });
    }

    async function buscaChegadasEquipes(inicio = 1, fim = 1){
        inicio = document.getElementById('inicioChegadaEquipes').value
        fim = document.getElementById('fimChegadaEquipes').value
        url = document.getElementById('url_chegada_equipes').value;
        let temporada_id = document.getElementById('vitoriasEquipesPorTemporada').value;
        const token = document.querySelector('meta[name="csrf-token"]').content

        let anoInicioChegadaEquipes = document.getElementById('anoInicioChegadaEquipes').value;
        let anoFimChegadaEquipes = document.getElementById('anoFimChegadaEquipes').value;

        // console.log('equipes', anoInicioChegadaEquipes, anoFimChegadaEquipes)

        if(inicio > fim){
            alert("Posição final não pode ser maior que a inicial")
            return
        }

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                inicio:inicio,
                fim:fim,
                temporada_id:temporada_id,
                anoInicioChegadaEquipes:anoInicioChegadaEquipes,
                anoFimChegadaEquipes:anoFimChegadaEquipes
            })
        })

        const res = await req.json();
        
        let tbody = document.querySelector('#tbodyChegadaEquipes')

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        res.totalVitoriasPorEquipe.forEach((equipe, index) => {
            
            tbody.innerHTML += `<tr>
                                    <td>${index+1}</td>
                                    <td>${equipe.equipe_nome}</td>
                                    <td>${equipe.chegadas}</td>
                                </tr>`
        });
    }

    async function buscaLargadasEquipes(inicio = 1, fim = 1){
        inicio = document.getElementById('inicioLargadaEquipes').value
        fim = document.getElementById('fimLargadaEquipes').value
        url = document.getElementById('url_largada_equipes').value;
        let temporada_id = document.getElementById('largadasEquipesPorTemporada').value;
        const token = document.querySelector('meta[name="csrf-token"]').content
        let anoInicioLargadaEquipes = document.getElementById('anoInicioLargadaEquipes').value;
        let anoFimLargadaEquipes = document.getElementById('anoFimLargadaEquipes').value;

        if(inicio > fim){
            alert("Posição final não pode ser maior que a inicial")
            return
        }

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                inicio:inicio,
                fim:fim,
                temporada_id:temporada_id,
                anoInicioLargadaEquipes:anoInicioLargadaEquipes,
                anoFimLargadaEquipes:anoFimLargadaEquipes
            })
        })

        const res = await req.json();
        
        let tbody = document.querySelector('#tbodyLargadaEquipes')

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        res.totalLargadasPorEquipe.forEach((equipe, index) => {
            
            tbody.innerHTML += `<tr>
                                    <td>${index+1}</td>
                                    <td>${equipe.nome}</td>
                                    <td>${equipe.largadas}</td>
                                </tr>`
        });
    }

    async function buscaLargadasPilotos(inicio = 1, fim = 1){
        inicio = document.getElementById('inicioLargadaPilotos').value
        fim = document.getElementById('fimLargadaPilotos').value
        url = document.getElementById('url_largada_pilotos').value;
        let temporada_id = document.getElementById('largadasPilotosPorTemporada').value;
        const token = document.querySelector('meta[name="csrf-token"]').content
        let anoInicioLargadaPilotos = document.getElementById('anoInicioLargadaPilotos').value;
        let anoFimLargadaPilotos = document.getElementById('anoFimLargadaPilotos').value;

        if(inicio > fim){
            alert("Posição final não pode ser maior que a inicial")
            return
        }

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                inicio:inicio,
                fim:fim,
                temporada_id:temporada_id,
                anoInicioLargadaPilotos:anoInicioLargadaPilotos,
                anoFimLargadaPilotos:anoFimLargadaPilotos
            })
        })

        const res = await req.json();
        
        let tbody = document.querySelector('#tbodyLargadaPilotos')

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        res.totalLargadasPorPiloto.forEach((piloto, index) => {
            
            tbody.innerHTML += `<tr>
                                    <td>${index+1}</td>
                                    <td>${piloto.piloto_nome_completo}</td>
                                    <td>${piloto.largadas}</td>
                                </tr>`
        });
    }

    function preencherPaginacao(links){
        let paginacao = document.getElementById('paginacao');

        paginacao.innerHTML = '';

        links.forEach(link => {
            let paginaAtual = link.active ? 'bg-danger' : '';
            let ocultarBotao = link.url == null && link.label != '...' ? 'd-none' : '';

            if(link.label == "&laquo; Previous"){
                link.label = '<<'
            }else if (link.label == "Next &raquo;"){
                link.label = '>>'
            }

            paginacao.innerHTML += `<button class="${paginaAtual} ${ocultarBotao} botaoPaginacao" onclick="buscaResultadosCorrida('${link.url}')">${link.label}</button>`
        })

    }

    // function loadData() {
    //         const loader = document.getElementById('loader');
    //         const content = document.getElementById('content');

    //         // Mostrar o loader
    //         loader.style.display = 'block';

    //         // Simular uma requisição fetch
    //         fetch('https://jsonplaceholder.typicode.com/posts')
    //             .then(response => response.json())
    //             .then(data => {
    //                 // Esconder o loader
    //                 loader.style.display = 'none';

    //                 // Mostrar o conteúdo
    //                 content.innerHTML = '<p>Dados carregados com sucesso!</p>';
    //             })
    //             .catch(error => {
    //                 loader.style.display = 'none';
    //                 content.innerHTML = `<p>Erro ao carregar os dados: ${error}</p>`;
    //             });
    //     }

    function travarAnos(selectId, inputInicioId, inputFimId) {
        const select = document.getElementById(selectId);
        const inicio = document.getElementById(inputInicioId);
        const fim = document.getElementById(inputFimId);

        if (select && inicio && fim) {
            if (select.value !== "") { 
                // Bloqueia e limpa se selecionou uma temporada específica
                inicio.value = "";
                fim.value = "";
                inicio.disabled = true;
                fim.disabled = true;
                inicio.style.backgroundColor = "#e9ecef";
                fim.style.backgroundColor = "#e9ecef";
            } else { 
                // Desbloqueia se voltou para "Todas as Temporadas"
                inicio.disabled = false;
                fim.disabled = false;
                inicio.style.backgroundColor = "";
                fim.style.backgroundColor = "";
            }
        }
    }

    function validarIntervaloAnos(inicioId, fimId) {
        const inputInicio = document.getElementById(inicioId);
        const inputFim = document.getElementById(fimId);

        if (inputInicio && inputFim) {
            // Só faz a validação se ambos os campos estiverem preenchidos
            if (inputInicio.value !== "" && inputFim.value !== "") {
                const anoInicio = parseInt(inputInicio.value);
                const anoFim = parseInt(inputFim.value);

                if (anoFim < anoInicio) {
                    alert("Atenção: O Ano Fim não pode ser menor que o Ano Início!");
                    // Auto-corrige o campo para o usuário não ficar travado
                    inputFim.value = anoInicio; 
                }
            }
        }
    }

    function sincronizarEValidarAnos(inicioId, fimId) {
        const inputInicio = document.getElementById(inicioId);
        const inputFim = document.getElementById(fimId);

        // if (inputInicio && inputFim) {
            let valInicio = inputInicio.value;
            let valFim = inputFim.value;

        //     // 1. Se preencheu Início e Fim está vazio -> Iguala o Fim ao Início
        //     if (valInicio !== "" && valFim === "") {
        //         inputFim.value = valInicio;
        //     }
        //     // 2. Se preencheu Fim e Início está vazio -> Iguala o Início ao Fim
        //     else if (valFim !== "" && valInicio === "") {
        //         inputInicio.value = valFim;
        //     }
        //     // 3. Se ambos têm valor, valida se Fim < Início
        //     else if (valInicio !== "" && valFim !== "") {
                if (parseInt(valFim) < parseInt(valInicio)) {
                    alert("Atenção: O Ano Fim não pode ser menor que o Ano Início!");
                    inputFim.value = valInicio; // Auto-corrige
                }
        //     }
        // }
    }

</script>