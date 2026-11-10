@php 
 use App\Models\Site\Corrida;
 use App\Models\Site\Resultado;
 use App\Models\Site\Piloto;
@endphp
@extends('layouts.main')

@section('section')
@include('site.partials.perfil-f1-estilos')

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
                {{-- MELHORIA 20261110 (playoff) --}}
                <div class="stat-box">
                    <span>Títulos Playoff</span>
                    <strong>{{ $totTitulosPlayoff }}</strong>
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
                                    <a class="link-perfil" href="{{ route('equipes.show', [$equipe->equipe->id]) }}" title="Ver perfil da equipe">{{$equipe->equipe->nome}}</a>
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
                                    <a class="link-perfil" href="{{ route('equipes.show', [$corridaPorEquipe->equipe_id]) }}" title="Ver perfil da equipe">{{$corridaPorEquipe->nome}}</a>
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
                            @php $infoCampeonato = Piloto::getInfoCampeonato($temporada->id, $modelPiloto->id); /* MELHORIA 20261105: 1 chamada por temporada */ @endphp
                            <tr>
                                <td class="fw-bold">{{ substr($temporada->des_temporada, 0, strpos($temporada->des_temporada, ' ')) }}</td>
                                <td><span class="badge bg-dark rounded-pill px-3 py-2">{{ $infoCampeonato['posicaoPiloto'] }}º</span></td>
                                <td class="fw-bold fs-5 text-danger">{{ $infoCampeonato['totalPontos'] }}</td>
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

    {{-- ===================== MELHORIA 20261105: SPRINTS ===================== --}}
    <details class="f1-accordion" id="sprints-piloto">
        <summary><span>Sprints <span class="sprint-contador" id="sprintContador">{{ count($sprintsPiloto) }}</span></span> <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="sprint-filtro-info" id="sprintFiltroInfo"><i class="bi bi-funnel"></i> Todas as temporadas</div>

            <div class="sprint-painel">
                <div class="profile-stats-grid" id="sprintStats"></div>
            </div>

            <div class="sprint-subtitulo">Por temporada <small class="text-muted text-lowercase fw-normal">(clique na temporada para ver as sprints)</small></div>
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Temporada</th><th>Sprints</th><th>Vitórias</th><th>Poles</th><th>Pódios</th><th>Pontos</th><th>Média Chegada</th>
                        </tr>
                    </thead>
                    <tbody id="sprintPorTemporada"></tbody>
                </table>
            </div>
        </div>
    </details>
    <script>window.SPRINTS_PILOTO = @json($sprintsPiloto); window.TITULOS_SPRINT_PILOTO = @json($titulosSprint); /* MELHORIA 20261110 */</script>

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
                                    <td><a href="{{ route('equipes.show', [$vitoria->pilotoEquipe->equipe->id]) }}" title="Ver perfil da equipe"><span class="badge bg-dark">{{ $vitoria->pilotoEquipe->equipe->nome }}</span></a></td>
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
                                    <td><a href="{{ route('equipes.show', [$polePosition->pilotoEquipe->equipe->id]) }}" title="Ver perfil da equipe"><span class="badge bg-dark">{{ $polePosition->pilotoEquipe->equipe->nome }}</span></a></td>
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
    <a href="#sprints-piloto"><i class="bi bi-lightning-charge"></i> Sprints</a>
    <a href="#historico-vitorias"><i class="bi bi-trophy"></i> Histórico de Vitórias</a>
    @if(count($vitoriasPorPista) > 0)<a href="#vitorias-pista"><i class="bi bi-geo-alt"></i> Vitórias por Pista</a>@endif
    @if(count($listagemVitorias) > 0)<a href="#pistas-sem-vitoria"><i class="bi bi-x-circle"></i> Pistas sem vitórias</a>@endif
    <a href="#historico-poles"><i class="bi bi-stopwatch"></i> Histórico de Poles</a>
    @if(count($polesPorPista) > 0)<a href="#poles-pista"><i class="bi bi-geo-alt-fill"></i> Poles por Pista</a>@endif
    @if(count($listagemPolePositions) > 0)<a href="#pistas-sem-pole"><i class="bi bi-x-circle-fill"></i> Pistas sem Pole</a>@endif
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

@include('site.partials.perfil-f1-ordenacao')

{{-- MELHORIA 20261105: estatísticas das sprints (independente do script acima) --}}
<script>
(function () {
    const sprints = window.SPRINTS_PILOTO || [];
    const esc = t => String(t === null || t === undefined ? '' : t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const media = v => v.length ? (v.reduce((a, b) => a + b, 0) / v.length).toFixed(1).replace('.', ',') : '-';
    const num = v => Number.isInteger(v) ? v : String(v).replace('.', ',');

    function resumo(lista) {
        const chegadas = lista.filter(s => s.chegada > 0).map(s => s.chegada);
        const largadas = lista.filter(s => s.largada > 0).map(s => s.largada);
        return {
            total: lista.length,
            vitorias: lista.filter(s => s.chegada === 1).length,
            poles: lista.filter(s => s.largada === 1).length,
            podios: lista.filter(s => s.chegada >= 1 && s.chegada <= 3).length,
            pontos: Math.round(lista.reduce((a, s) => a + s.pontos, 0) * 10) / 10,
            abandonos: lista.filter(s => s.abandono).length,
            melhorChegada: chegadas.length ? Math.min(...chegadas) + 'º' : '-',
            melhorLargada: largadas.length ? Math.min(...largadas) + 'º' : '-',
            gridMedio: media(largadas),
            mediaChegada: media(chegadas),
        };
    }

    function classePos(p, pontos) {
        if (p === 1) return 'p1';
        if (p === 2) return 'p2';
        if (p === 3) return 'p3';
        return pontos > 0 ? 'pontua' : '';
    }

    function linhaVazia(colunas, temporadaId) {
        return '<tr><td colspan="' + colunas + '" class="sprint-vazio">Nenhuma sprint disputada' + (temporadaId ? ' nesta temporada' : '') + '.</td></tr>';
    }

    function renderizar(temporadaId, nomeTemporada) {
        const lista = temporadaId ? sprints.filter(s => String(s.temporada_id) === String(temporadaId)) : sprints;
        const r = resumo(lista);
        document.getElementById('sprintContador').textContent = r.total;
        document.getElementById('sprintFiltroInfo').innerHTML = '<i class="bi bi-funnel"></i> ' + esc(nomeTemporada || 'Todas as temporadas');

        // MELHORIA 20261110: títulos da classificação só das sprints
        const titulosSprint = (window.TITULOS_SPRINT_PILOTO || []).filter(t => !temporadaId || String(t) === String(temporadaId)).length;
        const tiles = [
            ['Títulos Sprint', titulosSprint],
            ['Sprints', r.total], ['Vitórias', r.vitorias], ['Poles', r.poles], ['Pódios', r.podios],
            ['Pontos', num(r.pontos)], ['Abandonos', r.abandonos],
            ['Melhor Largada', r.melhorLargada], ['Melhor Chegada', r.melhorChegada], ['Grid Médio', r.gridMedio], ['Média Chegada', r.mediaChegada],
        ];
        document.getElementById('sprintStats').innerHTML = tiles.map(t => '<div class="stat-box"><span>' + t[0] + '</span><strong>' + esc(t[1]) + '</strong></div>').join('');

        // por temporada (mantém a ordem: mais recente primeiro); clicar na linha expande as sprints da temporada
        const grupos = [];
        lista.forEach(s => {
            let g = grupos.find(x => x.id === s.temporada_id);
            if (!g) { g = { id: s.temporada_id, nome: s.temporada + (s.ano ? ' (' + s.ano + ')' : ''), itens: [] }; grupos.push(g); }
            g.itens.push(s);
        });
        const abrirTodas = grupos.length === 1; // filtrando uma temporada: já mostra as sprints
        document.getElementById('sprintPorTemporada').innerHTML = grupos.length ? grupos.map(g => {
            const x = resumo(g.itens);
            const campeao = (window.TITULOS_SPRINT_PILOTO || []).some(t => String(t) === String(g.id)); // MELHORIA 20261110
            return '<tr class="sprint-temporada' + (abrirTodas ? ' aberta' : '') + '" data-temporada="' + esc(g.id) + '" title="Clique para ver as sprints">' +
                '<td class="fw-bold"><i class="bi bi-chevron-right sprint-seta"></i>' + esc(g.nome) + (campeao ? '<span class="tag-titulo"><i class="bi bi-trophy-fill"></i> Campeão Sprint</span>' : '') + '</td><td><span class="badge bg-secondary">' + x.total + '</span></td>' +
                '<td class="text-success fw-bold">' + x.vitorias + '</td><td class="fw-bold">' + x.poles + '</td><td class="text-primary fw-bold">' + x.podios + '</td>' +
                '<td class="fw-bold text-danger">' + num(x.pontos) + '</td><td>' + x.mediaChegada + '</td></tr>' +
                '<tr class="sprint-detalhe' + (abrirTodas ? '' : ' oculto') + '" data-detalhe="' + esc(g.id) + '"><td colspan="7">' +
                    '<div class="table-responsive"><table class="sprint-corridas"><thead><tr>' +
                    '<th>Etapa</th><th>Evento / Pista</th><th>Equipe</th><th>Largada</th><th>Chegada</th><th>+/-</th><th>Pontos</th><th>Status</th><th></th>' +
                    '</tr></thead><tbody>' + g.itens.map(linhaSprint).join('') + '</tbody></table></div>' +
                '</td></tr>';
        }).join('') : linhaVazia(7, temporadaId);
    }

    // uma sprint dentro da temporada expandida
    function linhaSprint(s) {
        let delta = '<span class="sprint-delta igual">&ndash;</span>';
        if (s.largada > 0 && s.chegada > 0) {
            const d = s.largada - s.chegada;
            if (d > 0) delta = '<span class="sprint-delta sobe"><i class="bi bi-caret-up-fill"></i>' + d + '</span>';
            else if (d < 0) delta = '<span class="sprint-delta desce"><i class="bi bi-caret-down-fill"></i>' + (-d) + '</span>';
            else delta = '<span class="sprint-delta igual">=</span>';
        }
        const status = (s.abandono ? '<span class="sprint-tag dnf">DNF</span>' : '') + (s.volta_rapida ? '<span class="sprint-tag vmr">VMR</span>' : '');
        const local = s.evento ? '<span class="fw-bold">' + esc(s.evento) + '</span><br><small class="text-muted">' + esc(s.pista) + '</small>' : esc(s.pista);
        return '<tr>' +
            '<td>' + esc(s.ordem) + 'ª</td>' +
            '<td>' + (s.bandeira ? '<img src="' + esc(s.bandeira) + '" class="flag-img" alt=""> ' : '') + local + '</td>' +
            '<td>' + (s.equipe_imagem ? '<img src="' + esc(s.equipe_imagem) + '" style="width:25px; height:25px; border-radius:3px; margin-right:8px;">' : '') + (s.equipe_link ? '<a class="link-perfil" href="' + esc(s.equipe_link) + '" title="Ver perfil da equipe">' + esc(s.equipe) + '</a>' : esc(s.equipe)) + '</td>' +
            '<td>' + (s.largada ? s.largada + 'º' : '-') + '</td>' +
            '<td>' + (s.chegada ? '<span class="sprint-pos ' + classePos(s.chegada, s.pontos) + '">' + s.chegada + 'º</span>' : '-') + '</td>' +
            '<td>' + delta + '</td>' +
            '<td class="fw-bold">' + num(s.pontos) + '</td>' +
            '<td>' + (status || '-') + '</td>' +
            '<td><a title="Visualizar corrida" href="' + esc(s.link) + '"><i class="bi bi-play-circle-fill"></i></a></td>' +
            '</tr>';
    }

    // clicar na temporada abre/fecha as sprints dela
    document.getElementById('sprintPorTemporada').addEventListener('click', e => {
        const linha = e.target.closest('tr.sprint-temporada');
        if (!linha) return;
        const detalhe = document.querySelector('#sprintPorTemporada tr[data-detalhe="' + linha.dataset.temporada + '"]');
        if (!detalhe) return;
        const abrir = detalhe.classList.contains('oculto');
        detalhe.classList.toggle('oculto', !abrir);
        linha.classList.toggle('aberta', abrir);
    });

    renderizar('', '');

    // acompanha o seletor de temporada do topo (o script original continua cuidando do card principal)
    const seletor = document.getElementById('ajaxGetStatsPilotoPorTemporada');
    if (seletor) {
        const aoMudar = () => {
            const opcao = seletor.options[seletor.selectedIndex];
            renderizar(seletor.value, seletor.value ? opcao.textContent.trim() : '');
        };
        // com jQuery (select2 dispara o change por ele) o .on cobre os dois casos
        if (window.jQuery) window.jQuery(seletor).on('change', aoMudar);
        else seletor.addEventListener('change', aoMudar);
    }
})();
</script>
@endsection