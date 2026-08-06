@php 
 use App\Models\Site\Corrida;
 use App\Models\Site\Resultado;
 use App\Models\Site\Piloto;
@endphp
@extends('layouts.main')

@section('section')
<style>
    /* Variáveis de Cores Estilo F1 */
    :root {
        --f1-red: #e10600;
        --f1-dark: #15151e;
        --f1-darker: #11101d;
        --f1-gray: #38383f;
        --f1-light: #f4f4f4;
        --f1-text: #ffffff;
    }

    /* === 1. CARD DE PERFIL (HERO CARD) === */
    .driver-profile-card {
        background-color: var(--f1-dark);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        margin-top: 30px;
        margin-bottom: 40px;
        overflow: hidden;
    }

    .profile-header {
        background-color: var(--f1-darker);
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        border-bottom: 4px solid var(--f1-red);
    }

    .profile-title {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .profile-title h2 {
        color: white;
        margin: 0;
        font-weight: 900;
        text-transform: uppercase;
        font-size: 2rem;
        letter-spacing: -1px;
    }

    .badge-f1 {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .badge-country { background: rgba(255,255,255,0.1); color: white; }
    .badge-active { background: rgba(40, 167, 69, 0.15); color: #28a745; }
    .badge-retired { background: rgba(220, 53, 69, 0.15); color: #dc3545; }

    .season-selector {
        flex-grow: 1;
        max-width: 350px;
        min-width: 250px;
    }

    .f1-select {
        width: 100%;
        background-color: var(--f1-dark);
        color: white;
        border: 2px solid var(--f1-gray);
        border-radius: 6px;
        padding: 10px 15px;
        font-weight: bold;
        text-transform: uppercase;
        transition: 0.3s;
        cursor: pointer;
    }

    .f1-select:focus {
        outline: none;
        border-color: var(--f1-red);
        box-shadow: 0 0 0 3px rgba(225, 6, 0, 0.2);
    }

    .profile-body {
        display: flex;
        padding: 30px;
        gap: 30px;
    }

    .profile-image-container {
        flex: 0 0 300px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .profile-image-container img {
        width: 100%;
        border-radius: 8px;
        border: 2px solid var(--f1-gray);
        box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    }

    .profile-stats-grid {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 15px;
        align-content: start;
    }

    .stat-box {
        background: rgba(255,255,255,0.03);
        border-radius: 8px;
        padding: 15px;
        border-left: 4px solid var(--f1-red);
        transition: transform 0.2s, background 0.2s;
    }

    .stat-box:hover {
        transform: translateY(-3px);
        background: rgba(255,255,255,0.08);
    }

    .stat-box span {
        display: block;
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .stat-box strong {
        display: block;
        font-size: 24px;
        color: white;
        font-weight: 900;
        line-height: 1;
    }

    .other-stats { display: none; }
    
    #show-other-stats {
        width: 100%;
        background: transparent;
        border: 2px solid var(--f1-red);
        color: white;
        text-transform: uppercase;
        font-weight: bold;
        border-radius: 6px;
        padding: 10px;
        transition: 0.3s;
    }
    
    #show-other-stats:hover {
        background: var(--f1-red);
    }

    @media (max-width: 992px) {
        .profile-body { flex-direction: column; align-items: center; }
        .profile-image-container { width: 100%; max-width: 400px; }
        .profile-header { justify-content: center; }
    }

    /* === 2. ACCORDIONS DAS TABELAS === */
    details.f1-accordion {
        background: white;
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #eee;
        overflow: hidden;
    }

    details.f1-accordion summary {
        background: var(--f1-darker);
        color: white;
        padding: 18px 25px;
        font-size: 1.1rem;
        font-weight: 800;
        text-transform: uppercase;
        cursor: pointer;
        list-style: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 6px solid var(--f1-red);
        transition: background 0.3s;
    }
    
    details.f1-accordion summary::-webkit-details-marker {
        display: none;
    }

    details.f1-accordion summary:hover {
        background: var(--f1-dark);
    }

    details.f1-accordion summary .toggle-icon {
        transition: transform 0.3s ease;
        font-size: 1.2rem;
    }

    details[open].f1-accordion summary .toggle-icon {
        transform: rotate(180deg);
        color: var(--f1-red);
    }

    .accordion-body {
        padding: 20px;
        background: #fafafa;
    }

    /* === 3. TABELAS === */
    .table-responsive { width: 100%; overflow-x: auto; }
    .f1-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    
    .f1-table th { 
        background: #eaeaea; 
        color: var(--f1-darker); 
        text-transform: uppercase; 
        font-size: 12px; 
        padding: 12px 15px; 
        text-align: left;
        cursor: pointer;
        user-select: none;
    }

    .f1-table th .sort-icon {
        font-size: 11px;
        opacity: 0.4;
        transition: opacity 0.2s, color 0.2s;
        margin-left: 4px;
    }

    .f1-table th:hover .sort-icon {
        opacity: 1;
        color: var(--f1-red);
    }

    .f1-table td { padding: 15px; border-bottom: 1px solid #e0e0e0; vertical-align: middle; color: var(--f1-darker); }
    .f1-table tr:hover { background-color: white; }
    .f1-table a { color: var(--f1-darker); font-size: 1.2rem; transition: 0.2s; }
    .f1-table a:hover { color: var(--f1-red); }

    /* Estilo para Bandeiras */
    .flag-img {
        width: 22px;
        height: 15px;
        object-fit: cover;
        border-radius: 2px;
        margin-right: 6px;
        vertical-align: middle;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    /* Footer Fixo Estilo F1 */
    .footer-show-pilotos {
        position: fixed;
        bottom: 0; left: 0; width: 100%;
        background-color: var(--f1-darker);
        border-top: 3px solid var(--f1-red);
        padding: 15px 10px;
        display: flex; flex-wrap: wrap; justify-content: center; gap: 15px;
        z-index: 1000;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.3);
    }

    .footer-show-pilotos a {
        color: white; text-decoration: none; font-size: 13px; text-transform: uppercase;
        font-weight: 600; padding: 5px 15px; border-radius: 20px;
        background: rgba(255, 255, 255, 0.1); transition: 0.3s; white-space: nowrap;
    }

    .footer-show-pilotos a:hover { background: var(--f1-red); color: white; }
    .main-content-padding { padding-bottom: 100px; }
    @media (max-width: 768px) { .main-content-padding { padding-bottom: 140px; } }
</style>

<div class="container main-content-padding">
    
    <!-- CARD DE PERFIL MODERNO -->
    <div class="driver-profile-card">
        <div class="profile-header">
            <div class="profile-title">
                <h2>{{ $modelPiloto->nomeCompleto() }}</h2>
                <span class="badge-f1 badge-country">
                    @if(isset($modelPiloto->pais->imagem) && $modelPiloto->pais->imagem != '')
                        <img src="{{ asset('images/' . $modelPiloto->pais->imagem) }}" class="flag-img" alt="{{ $modelPiloto->pais->des_nome }}">
                    @else
                        <i class="bi bi-globe"></i>
                    @endif
                    {{ $modelPiloto->pais->des_nome }}
                </span>
                @if($modelPiloto->flg_ativo == 'S')
                    <span class="badge-f1 badge-active"><i class="bi bi-circle-fill" style="font-size:8px;"></i> Em Atividade</span>
                @else 
                    <span class="badge-f1 badge-retired"><i class="bi bi-circle-fill" style="font-size:8px;"></i> Aposentado</span>
                @endif
            </div>

            <div class="season-selector">
                <select name="ajaxGetStatsPilotoPorTemporada" id="ajaxGetStatsPilotoPorTemporada" class="f1-select">
                    <option value="" selected id="selectGetStatsPilotoPorTemporada">Todas as Temporadas (Geral)</option>
                    @foreach($temporadas as $temporada)
                        <option value="{{$temporada->id}}">{{$temporada->des_temporada}} ({{$temporada->referencia}})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="profile-body">
            <div class="profile-image-container">
                <img src="{{ $modelPiloto->imagem != '' ? asset('images/'.$modelPiloto->imagem) : 'https://icon-library.com/images/person-png-icon/person-png-icon-29.jpg' }}" alt="Foto do Piloto">
                <button id="show-other-stats">Exibir Mais Estatísticas</button>
            </div>

            <div class="profile-stats-grid">
                <div class="stat-box">
                    <span>Corridas</span>
                    <strong id="tot-corridas">{{ $totCorridas }}</strong>
                </div>
                <div class="stat-box">
                    <span>Campeonatos</span>
                    <strong>{{ $totTitulos }}</strong>
                </div>
                <div class="stat-box">
                    <span>Vitórias</span>
                    <strong id="piloto-tot-vitorias">{{ $totVitorias }}</strong>
                </div>
                <div class="stat-box">
                    <span>Poles</span>
                    <strong id="piloto-tot-poles">{{ $totPoles }}</strong>
                </div>
                <div class="stat-box">
                    <span>Pódios</span>
                    <strong id="piloto-tot-podios">{{ $totPodios }}</strong>
                </div>
                <div class="stat-box">
                    <span>Pontos</span>
                    <strong id="piloto-tot-pontos">{{ $totPontos }}</strong>
                </div>
                <div class="stat-box">
                    <span>Voltas Rápidas</span>
                    <strong id="piloto-tot-voltas-rapidas">{{ $totVoltasRapidas }}</strong>
                </div>
                
                <div class="stat-box other-stats">
                    <span>Top 10</span>
                    <strong id="piloto-tot-top-ten">{{ $totTopTen }}</strong>
                </div>
                <div class="stat-box other-stats">
                    <span>Melhor Largada</span>
                    <strong id="piloto-melhor-largada">{{ $melhorPosicaoLargada }}º</strong>
                </div>
                <div class="stat-box other-stats">
                    <span>Pior Largada</span>
                    <strong id="piloto-pior-largada">{{ $piorPosicaoLargada }}º</strong>
                </div>
                <div class="stat-box other-stats">
                    <span>Melhor Chegada</span>
                    <strong id="piloto-melhor-chegada">{{ $melhorPosicaoChegada }}º</strong>
                </div>
                <div class="stat-box other-stats">
                    <span>Pior Chegada</span>
                    <strong id="piloto-pior-chegada">{{ $piorPosicaoChegada }}º</strong>
                </div>
                <div class="stat-box other-stats">
                    <span>Abandonos</span>
                    <strong id="piloto-totAbandonos">{{ $totAbandonos }}</strong>
                </div>
                <div class="stat-box other-stats">
                    <span>Grid Médio</span>
                    <strong id="piloto-gridMedio">{{$gridMedio}}</strong>
                </div>
                <div class="stat-box other-stats">
                    <span>Média Chegada</span>
                    <strong id="piloto-mediaChegada">{{$mediaChegada}}</strong>
                </div>
            </div>
        </div>
    </div>
    
    <input type="hidden" id="piloto_id" name="piloto_id" value="{{$modelPiloto->id}}">

    <!-- ACORDEÕES DE TABELAS -->

    <details class="f1-accordion">
        <summary>Histórico de Equipes <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Temporada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Equipe <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipes as $equipe)
                            <tr>
                                <td>{{$equipe->ano->ano}}</td>
                                <td>
                                    <img src="{{asset('images/'.$equipe->equipe->imagem)}}" style="width:25px; height:25px; border-radius:3px; margin-right: 10px;">
                                    <span class="fw-bold">{{$equipe->equipe->nome}}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="f1-accordion">
        <summary>Corridas por equipe <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Equipe <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Corridas <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Vitórias <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Poles <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pódios <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Top 10 <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($corridasPorEquipe as $corridaPorEquipe)
                            <tr>
                                <td>
                                    <img src="{{asset('images/'.$corridaPorEquipe->imagem)}}" style="width:25px; height:25px; border-radius:3px; margin-right: 10px;">
                                    <span class="fw-bold">{{$corridaPorEquipe->nome}}</span>
                                </td>
                                <td><span class="badge bg-secondary">{{$corridaPorEquipe->quantidade}}</span></td>
                                <td class="text-success fw-bold">{{Piloto::getInfoPorEquipe($modelPiloto->id, $corridaPorEquipe->equipe_id, 1, 1, 1, 1000)}}</td>
                                <td class="fw-bold">{{Piloto::getInfoPorEquipe($modelPiloto->id, $corridaPorEquipe->equipe_id, 1, 1000, 1,1)}}</td>
                                <td class="text-primary fw-bold">{{Piloto::getInfoPorEquipe($modelPiloto->id, $corridaPorEquipe->equipe_id, 1, 3, 1, 1000)}}</td>
                                <td>{{Piloto::getInfoPorEquipe($modelPiloto->id, $corridaPorEquipe->equipe_id, 1, 10, 1, 1000)}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="f1-accordion">
        <summary>Histórico nos campeonatos <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Temporada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Posição Final <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pontos <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($temporadas as $temporada)
                            <tr>
                                <td class="fw-bold">{{ substr($temporada->des_temporada, 0, strpos($temporada->des_temporada, ' ')) }}</td>
                                <td><span class="badge bg-dark rounded-pill px-3 py-2">{{Piloto::getInfoCampeonato($temporada->id, $modelPiloto->id)['posicaoPiloto']}}º</span></td>
                                <td class="fw-bold fs-5 text-danger">{{Piloto::getInfoCampeonato($temporada->id, $modelPiloto->id)['totalPontos']}}</td>
                                <td>
                                    <a data-toggle="tooltip" data-placement="top" title="Ver Classificação Completa" href="{{route('temporadas.classificacao', [$temporada->id])}}">
                                        <i class="bi bi-list-ol"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details> 

    <details class="f1-accordion" id="historico-vitorias">
        <summary>Histórico de Vitórias <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="alert alert-dark text-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2 text-danger"></i> Corridas seguidas sem vitória atualmente: <strong>{{$corridaSeguidasSemVencer}}</strong>
            </div>
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Temporada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Evento <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Equipe <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($listagemVitorias) > 0)
                            @foreach ($listagemVitorias as $vitoria)
                                <tr>
                                    <td class="fw-bold">{{ substr($vitoria->corrida->temporada->des_temporada, 0, strpos($vitoria->corrida->temporada->des_temporada, ' ')) }}</td>
                                    <td>{{ isset($vitoria->corrida->evento->des_nome) ? $vitoria->corrida->evento->des_nome : '-' }}</td>
                                    <td>
                                        @if(isset($vitoria->corrida->pista->pais->imagem))
                                            <img src="{{ asset('images/' . $vitoria->corrida->pista->pais->imagem) }}" class="flag-img" alt="Bandeira">
                                        @endif
                                        {{$vitoria->corrida->pista->nome}}
                                    </td>
                                    <td><span class="badge bg-dark">{{ $vitoria->pilotoEquipe->equipe->nome }}</span></td>
                                    <td>
                                        <a data-toggle="tooltip" data-placement="top" title="Visualizar corrida" href="{{route('resultados.show', [$vitoria->corrida->id])}}">
                                            <i class="bi bi-play-circle-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else 
                            <tr><td colspan="5" class="text-center text-muted" style="font-style: italic;">Nenhuma vitória registrada.</td></tr> 
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    @if(count($vitoriasPorPista) > 0)
        <details class="f1-accordion" id="vitorias-pista">
            <summary>Vitórias por Pista <i class="bi bi-chevron-down toggle-icon"></i></summary>
            <div class="accordion-body">
                <div class="table-responsive">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th>Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th>Quantidade de Vitórias <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vitoriasPorPista as $key => $vitoriaPorPista)
                                <tr>
                                    <td class="fw-bold">
                                        {{$key}}
                                    </td>
                                    <td><span class="badge bg-success rounded-pill px-3 py-2 fs-6">{{$vitoriaPorPista}} <i class="bi bi-trophy-fill ms-1"></i></span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @endif
        
    @if(count($listagemVitorias) > 0)
        <details class="f1-accordion" id="pistas-sem-vitoria">
            <summary>Pistas sem vitória <i class="bi bi-chevron-down toggle-icon"></i></summary>
            <div class="accordion-body">
                <div class="table-responsive">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th>Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th>Corridas Disputadas <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pistasEmQueOPilotoNaoVenceu as $key => $pistaEmQueOPilotoNaoVenceu)
                                <tr>
                                    <td class="fw-bold">
                                        {{$key}}
                                    </td>
                                    <td>{{$pistaEmQueOPilotoNaoVenceu}} corrida(s)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @endif

    <details class="f1-accordion" id="historico-poles">
        <summary>Histórico de Pole Positions <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="alert alert-dark text-center mb-4" role="alert">
                <i class="bi bi-stopwatch-fill me-2 text-danger"></i> Corridas seguidas sem pole position atualmente: <strong>{{$corridaSeguidasSemPolePosition}}</strong>
            </div>
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Temporada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Evento <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Equipe <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($listagemPolePositions) > 0)
                            @foreach ($listagemPolePositions as $polePosition)
                                <tr>
                                    <td class="fw-bold">{{ substr($polePosition->corrida->temporada->des_temporada, 0, strpos($polePosition->corrida->temporada->des_temporada, ' ')) }}</td>
                                    <td>{{ isset($polePosition->corrida->evento->des_nome) ? $polePosition->corrida->evento->des_nome : '-' }}</td>
                                    <td>
                                        @if(isset($polePosition->corrida->pista->pais->imagem))
                                            <img src="{{ asset('images/' . $polePosition->corrida->pista->pais->imagem) }}" class="flag-img" alt="Bandeira">
                                        @endif
                                        {{$polePosition->corrida->pista->nome}}
                                    </td>
                                    <td><span class="badge bg-dark">{{ $polePosition->pilotoEquipe->equipe->nome }}</span></td>
                                    <td>
                                        <a data-toggle="tooltip" data-placement="top" title="Visualizar corrida" href="{{route('resultados.show', [$polePosition->corrida->id])}}">
                                            <i class="bi bi-play-circle-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else 
                            <tr><td colspan="5" class="text-center text-muted" style="font-style: italic;">Nenhuma pole position registrada.</td></tr> 
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    @if(count($polesPorPista) > 0)
        <details class="f1-accordion" id="poles-pista">
            <summary>Pole Positions por Pista <i class="bi bi-chevron-down toggle-icon"></i></summary>
            <div class="accordion-body">
                <div class="table-responsive">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th>Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th>Quantidade de Poles <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($polesPorPista as $key => $polePorPista)
                                <tr>
                                    <td class="fw-bold">
                                        {{$key}}
                                    </td>
                                    <td><span class="badge bg-primary rounded-pill px-3 py-2 fs-6">{{$polePorPista}} <i class="bi bi-stopwatch ms-1"></i></span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @endif
        
    @if (count($listagemPolePositions) > 0)
        <details class="f1-accordion" id="pistas-sem-pole">
            <summary>Pistas sem Pole Position <i class="bi bi-chevron-down toggle-icon"></i></summary>
            <div class="accordion-body">
                <div class="table-responsive">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th>Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th>Corridas Disputadas <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pistasEmQueOPilotoNaoFoiPolePosition as $key => $pistaEmQueOPilotoNaoFoiPolePosition)
                                <tr>
                                    <td class="fw-bold">
                                        {{$key}}
                                    </td>
                                    <td>{{$pistaEmQueOPilotoNaoFoiPolePosition}} corrida(s)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @endif

    <div class="d-flex justify-content-center gap-4 mb-5">
        <a href="{{route('pilotos.index')}}" class="btn btn-outline-dark fw-bold px-4 py-2 text-uppercase"><i class="bi bi-arrow-left"></i> Voltar</a>
        <a href="{{route('pilotos.export', [$modelPiloto->id])}}" class="btn btn-success fw-bold px-4 py-2 text-uppercase"><i class="bi bi-file-earmark-excel"></i> Gerar Excel</a>
    </div>   
</div>

<div class="footer-show-pilotos shadow">
    <a href="#ajaxGetStatsPilotoPorTemporada"><i class="bi bi-house-door"></i> Início</a>
    <a href="#historico-vitorias"><i class="bi bi-trophy"></i> Histórico de Vitórias</a>
    <a href="#vitorias-pista"><i class="bi bi-geo-alt"></i> Vitórias por Pista</a>
    <a href="#pistas-sem-vitoria"><i class="bi bi-x-circle"></i> Pistas sem vitórias</a>
    <a href="#historico-poles"><i class="bi bi-stopwatch"></i> Histórico de Poles</a>
    <a href="#poles-pista"><i class="bi bi-geo-alt-fill"></i> Poles por Pista</a>
    <a href="#pistas-sem-pole"><i class="bi bi-x-circle-fill"></i> Pistas sem Pole</a>
</div>

<script>
 ajaxGetStatsPilotoPorTemporada = "<?=route('ajax.ajaxGetStatsPilotoPorTemporada')?>"
</script>

@php 
 $chegada = [];
 $labels = [];
@endphp

<script>
    $('#show-other-stats').click(function (e) { 
        e.preventDefault();
        if( this.innerHTML === 'Exibir Mais Estatísticas'){
            this.innerHTML = 'Ocultar Estatísticas';
        }else{
            this.innerHTML = 'Exibir Mais Estatísticas'
        }
        $('.other-stats').fadeToggle(300);
    });

    $('.footer-show-pilotos a').click(function() {
        let targetId = $(this).attr('href');
        if (targetId !== '#ajaxGetStatsPilotoPorTemporada') {
            $(targetId).attr('open', true);
        }
    });

    $('#ajaxGetStatsPilotoPorTemporada').change(function (e) { 
        e.preventDefault();

        temporada_id = $('#ajaxGetStatsPilotoPorTemporada').val();
        piloto_id = $('#piloto_id').val();
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        if(temporada_id != ''){
            selectTemporadaPolesPiloto = $('#selectGetStatsPilotoPorTemporada').text('Geral');
        }

        $.ajax({
            type: "POST",
            url: ajaxGetStatsPilotoPorTemporada,
            data: {temporada_id: temporada_id, piloto_id: piloto_id},
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
               $('#piloto-tot-vitorias').text(response.totVitorias)
               $('#piloto-tot-poles').text(response.totPoles)
               $('#piloto-tot-podios').text(response.totPodios)
               $('#piloto-tot-pontos').text(response.totPontos)
               $('#piloto-tot-voltas-rapidas').text(response.totVoltasRapidas)
               $('#piloto-tot-top-ten').text(response.totTopTen)
               $('#piloto-melhor-largada').text(response.melhorPosicaoLargada + 'º')
               $('#piloto-pior-largada').text(response.piorPosicaoLargada + 'º')
               $('#piloto-melhor-chegada').text(response.melhorPosicaoChegada + 'º')
               $('#piloto-pior-chegada').text(response.piorPosicaoChegada + 'º')
               $('#tot-corridas').text(response.totCorridas)
               $('#piloto-totAbandonos').text(response.totAbandonos)
               $('#piloto-gridMedio').text(response.gridMedio)
               $('#piloto-mediaChegada').text(response.mediaChegada)
            },
            error:function(){
                alert("O Piloto não participou da temporada selecionada.")
            }
        });
    });
</script>
@endsection