@php 
 use App\Models\Site\Corrida;
 use App\Models\Site\Resultado;
 use App\Models\Site\Piloto;
@endphp
@extends('layouts.main')

@section('section')
<style>

    td{
        white-space: nowrap;
    }

    th{
        white-space: nowrap;
    }

    h1{
    text-align: center;
    }
    
    table {
        border-collapse: collapse;
        margin: auto;
    }

    tr:hover{
        background-color: #dce6eb;
    }
    
    #driver-container{
        /* border: 1px solid black; */
        margin-top: 5%;
        display: flex;
    }

    #driver-details{
        /* background-color: #73b2959c; */
        width: 35%;
        padding: 2%;
        display: flex;
        border: 1px solid white;
    }

    #driver-details h4{
        font-size: 16px;
        font-weight: lighter;
        margin-bottom: 0;
    }

    #driver-details p{
        font-size: 24px;
        font-weight: bolder;
    }

    .image-wrapper{
        max-width: 200px;
        margin: auto;
    }

    #driver-item-details{
        display: flex;
    }

    .image-wrapper img{
        max-width: 100%;
        border-radius: 2%;
    }

    #driver-stats{
        /* background-color: #ebc83aaf; */
        width: 65%;
        padding: 2%;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, 1fr);
        grid-column-gap: 0px;
        grid-row-gap: 0px;
        /* text-align: center; */
    }

    #driver-stats div{
        width: 150px;
        height: 100px;
        margin-right: 25px;
        margin-bottom: 25px;
    }

    #driver-stats h4{
        font-size: 18px;
        margin-bottom: 0;
        font-weight: lighter;
        text-transform: uppercase;
    }

    #driver-stats p{
        font-size: 26px;
        font-weight: bolder;
        text-transform: uppercase;
        margin-bottom: 0;
    }

    .other-stats{
        display: none;
    }

    .resultados-por-corrida{
        margin-top: 35px;
    }

    .resultados-por-corrida h1 {
        text-transform: uppercase;
    }

    .tabela-historico-equipes {
        border-collapse: collapse;
        width: 30%;
    }

    .tabela-historico-equipes td, th {
        text-align: center;
    }

    .tabela-resultados {
        border-collapse: collapse;
        width: 60%;
    }

    .tabela-resultados th, td{
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .tabela-resultados tr:hover {
        background-color: #73b2959c;
    }

    .tabela-resultados th{
        text-transform: uppercase;
    }
    
</style>
   <div class="container">
    <select name="ajaxGetStatsPilotoPorTemporada" id="ajaxGetStatsPilotoPorTemporada" class="form-select mt-3" style="width: 25%; margin:0 auto;">
        <option value="" selected id="selectGetStatsPilotoPorTemporada">Selecione uma Temporada</option>
        @foreach($temporadas as $temporada)
            <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
        @endforeach
    </select>
    <div id="driver-container">
        <div id="driver-details" class="bg-dark text-light">
            <div>
                <div>
                    <div>
                        <h4>Nome do Piloto</h4>
                        <p>{{ $modelPiloto->nomeCompleto() }}</p>
                    </div>
                    <div>
                        <h4>País/Região</h4>
                        <p>{{ $modelPiloto->pais->des_nome }}</p>
                    </div>
                    <div>
                        <h4>Status</h4>
                        @if($modelPiloto->flg_ativo == 'S')
                            <p>Em Atividade</p>
                        @else 
                            <p>Aposentado</p>
                        @endif
                    </div>
                    <div>
                        <h4>Corridas Disputadas</h4>
                        <p id="tot-corridas">{{ $totCorridas }}</p>
                    </div>
                    <div>
                        <button id="show-other-stats" class="btn btn-light text-dark">Exibir Mais Estatisticas</button>
                    </div>
                </div>
            </div>
            <div class="image-wrapper mt-3">
                {{-- <img src="{{ $modelPiloto->imagem != '' ? asset('images/'.$modelPiloto->imagem) : asset('images/piloto_placeholder.jpg') }}" alt=""> --}}
                <img src="{{ $modelPiloto->imagem != '' ? asset('images/'.$modelPiloto->imagem) : 'https://icon-library.com/images/person-png-icon/person-png-icon-29.jpg' }}" alt="">
            </div>
        </div>
        <div id="driver-stats" class="bg-dark text-light">
           <div>
                <h4>Campeonato de Pilotos</h4>
                <p>{{ $totTitulos }}</p>
           </div>
            <div>
                <h4>Vitórias</h4>
                <p id="piloto-tot-vitorias">{{ $totVitorias }}</p>
           </div>
            <div>
                <h4>Pole Positions</h4>
                <p id="piloto-tot-poles">{{ $totPoles }}</p>
           </div>
            <div>
                <h4>Subidas ao Pódio</h4>
                <p id="piloto-tot-podios">{{ $totPodios }}</p>
           </div>
            <div>
                <h4>Total de Pontos</h4>
                <p id="piloto-tot-pontos">{{ $totPontos }}</p>
           </div>
            <div>
                <h4>Voltas mais rapidas</h4>
                <p id="piloto-tot-voltas-rapidas">{{ $totVoltasRapidas }}</p>
           </div>
            <div class="other-stats">
                <h4>Chegadas no top 10</h4>
                <p id="piloto-tot-top-ten">{{ $totTopTen }}</p>
           </div>
            <div class="other-stats">
                <h4>Melhor largada</h4>
                <p id="piloto-melhor-largada">{{ $melhorPosicaoLargada }}º</p>
           </div>
            <div class="other-stats">
                <h4>Pior Largada</h4>
                <p id="piloto-pior-largada">{{ $piorPosicaoLargada }}º</p>
           </div>
            <div class="other-stats">
                <h4>Melhor Chegada</h4>
                <p id="piloto-melhor-chegada">{{ $melhorPosicaoChegada }}º</p>
           </div>
            <div class="other-stats">
                <h4>pior Chegada</h4>
                <p id="piloto-pior-chegada">{{ $piorPosicaoChegada }}º</p>
           </div>
            <div class="other-stats">
                <h4>Abandonos</h4>
                <p id="piloto-totAbandonos">{{ $totAbandonos }}</p>
           </div>
            <div class="other-stats">
                <h4>Grid Médio</h4>
                <p id="piloto-gridMedio">{{$gridMedio}}</p>
           </div>
            <div class="other-stats">
                <h4>Média Chegada</h4>
                <p id="piloto-mediaChegada">{{$mediaChegada}}</p>
           </div>
        </div>
    </div>
    <input type="hidden" id="piloto_id" name="piloto_id" value="{{$modelPiloto->id}}">

        @php 
            
        @endphp

        <section class="resultados-por-corrida">
            <h1>Histórico de Equipes</h1>
            <table class="mt-5 mb-5 tabela-historico-equipes">
                <tr>
                    <th>Temporada</th>
                    <th>Equipe</th>
                </tr>
                @foreach($equipes as $equipe)
                    <tr>
                        <td>{{$equipe->ano->ano}}</td>
                        <td style="vertical-align: middle;">
                            <img src="{{asset('images/'.$equipe->equipe->imagem)}}" style="width:25px; height:25px;">
                            <span style="display: inline-block; vertical-align: middle;">{{$equipe->equipe->nome}}</span>
                        </td>
                    </tr>
                @endforeach
            </table>
        </section>

        <hr>

        <section class="resultados-por-corrida">
            <h1>Corridas por equipe</h1>
            <table class="mt-5 mb-5 tabela-historico-equipes">
                <tr>
                    <th>Equipe</th>
                    <th>Quantidade</th>
                </tr>
                @foreach($corridasPorEquipe as $corridaPorEquipe)
                    <tr>
                        <td style="vertical-align: middle;">
                            <img src="{{asset('images/'.$corridaPorEquipe->imagem)}}" style="width:25px; height:25px;">
                            <span style="display: inline-block; vertical-align: middle;">{{$corridaPorEquipe->nome}}</span>
                        </td>
                        <td>{{$corridaPorEquipe->quantidade}}</td>
                    </tr>
                @endforeach
            </table>
        </section>

        <hr>

        <section class="" style="height: 400px;">
            <h1 class="mb-3" style="text-transform:uppercase;">Histórico de Pontuação</h1>
            <div style="width: 550px; height: 550px; margin: 0 auto;">
                <canvas id="historicoPontuacao"></canvas>
            </div>
        </section>
       
        <hr>

        <section class="" style="height: auto;">
            <h1 class="mb-3" style="text-transform:uppercase;">Histórico de posição nos campeonatos</h1>
            <table class="mt-5 mb-5 tabela-historico-equipes">
                <th>Temporada</th>
                <th>Posição</th>
                <th>Pontos</th>
                <th>Ações</th>
                @foreach ($temporadas as $temporada )
                    <tr>
                        {{-- <td>{{$temporada->ano->ano}}</td> --}}
                        <td> {{ substr($temporada->des_temporada, 0, strpos($temporada->des_temporada, ' ')) }}</td>
                        <td>
                            {{Piloto::getInfoCampeonato($temporada->id, $modelPiloto->id)['posicaoPiloto']}}
                        </td>
                        <td>
                            {{Piloto::getInfoCampeonato($temporada->id, $modelPiloto->id)['totalPontos']}}
                        </td>
                        <td>
                            <a data-toggle="tooltip" data-placement="top" title="Classificação" href="{{route('temporadas.classificacao', [$temporada->id])}}"><i class="bi bi-table"></i></a>
                        </td>
                    </tr>
                @endforeach
            </table>  
        </section>   

        <hr>

        <section class="" style="height: auto;">
            <h1 class="mb-3" style="text-transform:uppercase;">Histórico de Vitórias</h1>
                <table class="mt-5 mb-5 tabela-historico-equipes">
                    <tr>
                        <th>Temporada</th>
                        <th>Evento</th>
                        <th>Pista</th>
                        <th class="text-nowrap">Equipe</th>
                        <th>Ações</th>
                    </tr>
                    @if (count($listagemVitorias) > 0)
                        @foreach ($listagemVitorias as $vitoria)
                            <tr>
                                {{-- <td>{{$vitoria->corrida->temporada->ano->ano}}</td> --}}
                                <td> {{ substr($vitoria->corrida->temporada->des_temporada, 0, strpos($vitoria->corrida->temporada->des_temporada, ' ')) }}</td>
                                <td>
                                    @if (isset($vitoria->corrida->evento->des_nome))
                                        {{$vitoria->corrida->evento->des_nome}}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{$vitoria->corrida->pista->nome}}</td>
                                <td class="text-nowrap">{{ $vitoria->pilotoEquipe->equipe->nome }}</td>
                                <td><a data-toggle="tooltip" data-placement="top" title="Visualizar corrida" class="" href="{{route('resultados.show', [$vitoria->corrida->id])}}"><i class="bi bi-eye-fill"></i></a></td>
                            </tr>
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="5" style="font-style: italic;">Piloto não tem vitórias</td>    
                        </tr> 
                    @endif
                </table>
        </section>

        <hr>

        @if(count($vitoriasPorPista) > 0)
            <section class="resultados-por-corrida">
                <h1>Vitórias por Pista</h1>
                <table class="mt-5 mb-5 tabela-historico-equipes">
                    <tr>
                        <th>Pista</th>
                        <th>Quantidade</th>
                    </tr>
                    @foreach($vitoriasPorPista as $key => $vitoriaPorPista)
                        <tr>
                            <td style="vertical-align: middle;">
                                <span style="display: inline-block; vertical-align: middle;">{{$key}}</span>
                            </td>
                            <td>{{$vitoriaPorPista}}</td>
                        </tr>
                    @endforeach
                </table>
            </section>
            <hr>
        @endif
        
        @if(count($listagemVitorias) > 0)
            <section class="resultados-por-corrida">
                <h1>Pistas em que o piloto não venceu</h1>
                <table class="mt-5 mb-5 tabela-historico-equipes">
                    <tr>
                        <th>
                            Pista
                        </th>
                    </tr>
                    @foreach($pistasEmQueOPilotoNaoVenceu as $key => $pistaEmQueOPilotoNaoVenceu)
                        <tr>
                            <td>{{$pistaEmQueOPilotoNaoVenceu}}</td>
                        </tr>
                    @endforeach
                </table>
            </section>
            <hr>
        @endif

        <section class="" style="height: auto;">
            <h1 class="mb-3" style="text-transform:uppercase;">Histórico de Pole Positions</h1>
                <table class="mt-5 mb-5 tabela-historico-equipes">
                    <tr>
                        <th>Temporada</th>
                        <th>Evento</th>
                        <th>Pista</th>
                        <th class="text-nowrap">Equipe</th>
                        <th>Ações</th>
                    </tr>
                    @if (count($listagemPolePositions) > 0)
                        @foreach ($listagemPolePositions as $polePosition)
                            <tr>
                                {{-- <td>{{$polePosition->corrida->temporada->ano->ano}}</td> --}}
                                <td> {{ substr($polePosition->corrida->temporada->des_temporada, 0, strpos($polePosition->corrida->temporada->des_temporada, ' ')) }}</td>
                                <td>
                                    @if (isset($polePosition->corrida->evento->des_nome))
                                        {{$polePosition->corrida->evento->des_nome}}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{$polePosition->corrida->pista->nome}}</td>
                                <td class="text-nowrap">{{ $polePosition->pilotoEquipe->equipe->nome }}</td>
                                <td><a data-toggle="tooltip" data-placement="top" title="Visualizar corrida" class="" href="{{route('resultados.show', [$polePosition->corrida->id])}}"><i class="bi bi-eye-fill"></i></a></td>
                            </tr>
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="5" style="font-style: italic;">Piloto não tem pole positions</td>    
                        </tr> 
                    @endif
                </table>
        </section>

        <hr>

        @if(count($polesPorPista) > 0)
            <section class="resultados-por-corrida">
                <h1>Pole Positions por Pista</h1>
                <table class="mt-5 mb-5 tabela-historico-equipes">
                    <tr>
                        <th>Pista</th>
                        <th>Quantidade</th>
                    </tr>
                    @foreach($polesPorPista as $key => $polePorPista)
                        <tr>
                            <td style="vertical-align: middle;">
                                <span style="display: inline-block; vertical-align: middle;">{{$key}}</span>
                            </td>
                            <td>{{$polePorPista}}</td>
                        </tr>
                    @endforeach
                </table>
            </section>
            <hr>
        @endif
        
        @if (count($listagemPolePositions) > 0)
            <section class="resultados-por-corrida">
                <h1>Pistas em que o piloto não foi Pole Position</h1>
                <table class="mt-5 mb-5 tabela-historico-equipes">
                    <tr>
                        <th>
                            Pista
                        </th>
                    </tr>
                    @foreach($pistasEmQueOPilotoNaoFoiPolePosition as $key => $pistaEmQueOPilotoNaoFoiPolePosition)
                        <tr>
                            <td>{{$pistaEmQueOPilotoNaoFoiPolePosition}}</td>
                        </tr>
                    @endforeach
                </table>
            </section>
            <hr>
        @endif

        {{--Tabela de Largada e chegada--}}
        <section class="resultados-por-corrida">
            <h1>Resultados por Corrida</h1>
            <table class="mt-5 mb-5 tabela-resultados">
                <tr>
                    <th style="text-align: center;">Temporada</th>
                    <th style="text-align: center;">Evento</th>
                    <th>Pista</th>
                    <th>Largada</th>
                    <th>Chegada</th>
                    <th>Variação</th>
                </tr>
                @foreach($resultadosPorCorrida as $resultado)
                    <tr @if($resultado->corrida->flg_sprint == 'S') style="font-style:italic; color:red;" @endif>
                        @if($resultado->pilotoEquipe->piloto->id == $modelPiloto->id)
                            {{-- <td> {{$resultado->corrida->temporada->ano->ano}} </td> --}}
                            <td style="text-align: center;"> {{ substr($resultado->corrida->temporada->des_temporada, 0, strpos($resultado->corrida->temporada->des_temporada, ' ')) }}</td>
                            <td style="text-align: center;">
                                @if (isset($resultado->corrida->evento->des_nome))
                                    {{$resultado->corrida->evento->des_nome}}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{$resultado->corrida->pista->nome}} @if($resultado->corrida->flg_sprint == 'S') - Sprint @endif</td>
                            <td>{{$resultado->largada}}</td>
                            <td>{{$resultado->chegada}} <?= $resultado->flg_abandono == 'S' ? ' - Abandonou' : '' ?></td>
                            <td>{{$resultado->largada-$resultado->chegada}}</td>
                        @endif
                    </tr>
                @endforeach
            </table>
            <div class="d-flex justify-content-center">
                {{ $resultadosPorCorrida->links() }}
            </div>
        </section>
       
        {{-- <div>
            <canvas id="myChart"></canvas>
        </div> --}}

        <div class="mb-5">
            <div class="d-flex" style="justify-content: space-around;">
                <div class="">
                    <a href="{{route('pilotos.index')}}" class="btn btn-primary">Voltar</a>
                </div>
                <div>
                    <a href="{{route('pilotos.export', [$modelPiloto->id])}}" class="btn btn-secondary">Gerar Excel</a>
                </div>
            </div>
        </div>   
   </div>

   <script>
    ajaxGetStatsPilotoPorTemporada = "<?=route('ajax.ajaxGetStatsPilotoPorTemporada')?>"
   </script>

   @php 

    $chegada = [];
    $labels = [];

   @endphp
   
   <script>
    const ctx = document.getElementById('myChart');

    temporadasDisputadas = <?php echo json_encode($temporadasDisputadas); ?>;
    pontuacaoPorTemporada = <?php echo json_encode($pontuacaoPorTemporada); ?>;
      
    /*Gráfico de Histórico de Pontos dos pilotos*/
    const historioPontuacao = document.getElementById('historicoPontuacao');

    new Chart(historioPontuacao, {
    type: 'bar',
    data: {
        labels: temporadasDisputadas,
        datasets: [{
        barThickness: 60,
        label: 'Pontuação',
        backgroundColor:
        [
            'rgba(194, 26, 26, 0.993)'
        ],
        data: pontuacaoPorTemporada,
        borderWidth: 1
        }]
    },
    options: {
        scales: {
        y: {
            beginAtZero: true,
            min: 0,
        }
        }
    }
    });

  $('#show-other-stats').click(function (e) { 
    e.preventDefault();
    if( this.innerHTML === 'Exibir Mais Estatisticas'){
        this.innerHTML = 'Esconder Estatisticas';
    }else{
        this.innerHTML = 'Exibir Mais Estatisticas'
    }

    $('.other-stats').toggle();
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
           $('#piloto-melhor-largada').text(response.melhorPosicaoLargada)
           $('#piloto-pior-largada').text(response.piorPosicaoLargada)
           $('#piloto-melhor-chegada').text(response.melhorPosicaoChegada)
           $('#piloto-pior-chegada').text(response.piorPosicaoChegada)
           $('#tot-corridas').text(response.totCorridas)
           $('#piloto-totAbandonos').text(response.totAbandonos)
           $('#piloto-gridMedio').text(response.gridMedio)
           $('#piloto-mediaChegada').text(response.mediaChegada)
        },
        error:function(){
            alert("Piloto não participou da temporada selecionada")
        }
    });
      
});

  </script>
@endsection

