@php 
 use App\Models\Site\Corrida;
 use App\Models\Site\Resultado;
@endphp
@extends('layouts.main')

@section('section')
<style>

    h1{
    text-align: center;
    }
    
    table {
        border-collapse: collapse;
        margin: auto;
    }
    
    #driver-container{
        /* border: 1px solid black; */
        margin-top: 5%;
        display: flex;
    }

    #driver-details{
        background-color: #73b2959c;
        width: 35%;
        padding: 2%;
        display: flex;
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
        background-color: #ebc83aaf;
        width: 65%;
        padding: 2%;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, 1fr);
        grid-column-gap: 0px;
        grid-row-gap: 0px;
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

    <div id="driver-container">
        <div id="driver-details">
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
                        <p>{{ $totCorridas }}</p>
                    </div>
                    <div>
                        <button id="show-other-stats" class="btn btn-primary">Exibir Mais Estatisticas</button>
                    </div>
                </div>
            </div>
            <div class="image-wrapper mt-3">
                <img src="{{asset('images/'.$modelPiloto->imagem)}}" alt="">
            </div>
        </div>
        <div id="driver-stats">
           <div>
                <h4>Campeonato de Pilotos</h4>
                <p>{{ $totTitulos }}</p>
           </div>
            <div>
                <h4>Vitórias</h4>
                <p>{{ $totVitorias }}</p>
           </div>
            <div>
                <h4>Pole Positions</h4>
                <p>{{ $totPoles }}</p>
           </div>
            <div>
                <h4>Subidas ao Pódio</h4>
                <p>{{ $totPodios }}</p>
           </div>
            <div>
                <h4>Total de Pontos</h4>
                <p>{{ $totPontos }}</p>
           </div>
            <div>
                <h4>Voltas mais rapidas</h4>
                <p>{{ $totVoltasRapidas }}</p>
           </div>
            <div class="other-stats">
                <h4>Chegadas no top 10</h4>
                <p>{{ $totTopTen }}</p>
           </div>
            <div class="other-stats">
                <h4>Melhor largada</h4>
                <p>{{ $melhorPosicaoLargada }}º</p>
           </div>
            <div class="other-stats">
                <h4>Pior Largada</h4>
                <p>{{ $piorPosicaoLargada }}º</p>
           </div>
            <div class="other-stats">
                <h4>Melhor Chegada</h4>
                <p>{{ $melhorPosicaoChegada }}º</p>
           </div>
            <div class="other-stats">
                <h4>pior Chegada</h4>
                <p>{{ $piorPosicaoChegada }}º</p>
           </div>
        </div>
    </div>

        @php 
            $resultados = Resultado::where('user_id', Auth::user()->id)->get();
        @endphp

        {{--Tabela de Largada e chegada--}}
        <section class="resultados-por-corrida">
            <h1>Resultados por Corrida</h1>
            <table class="mt-5 mb-5 tabela-resultados">
                <tr>
                    <th>Temporada</th>
                    <th>Pista</th>
                    <th>Largada</th>
                    <th>Chegada</th>
                    <th>Variação</th>
                </tr>
                @foreach($resultados as $resultado)
                    <tr @if($resultado->corrida->flg_sprint == 'S') style="font-style:italic; color:red;" @endif>
                        @if($resultado->pilotoEquipe->piloto->id == $modelPiloto->id)
                            <td> {{$resultado->corrida->temporada->ano->ano}} </td>
                            <td>{{$resultado->corrida->pista->nome}} @if($resultado->corrida->flg_sprint == 'S') - Sprint @endif</td>
                            <td>{{$resultado->largada}}</td>
                            <td>{{$resultado->chegada}}</td>
                            <td>{{$resultado->largada-$resultado->chegada}}</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </section>
       
        <div>
            <canvas id="myChart"></canvas>
        </div>

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

   @php 

    $chegada = [];
    $labels = [];
    foreach($resultados as $resultado){
        if($resultado->pilotoEquipe->piloto->id == $modelPiloto->id && $resultado->corrida->flg_sprint == 'N'){
            array_push($chegada, $resultado->chegada);
            array_push($labels, $resultado->corrida->pista->nome);
        }
    }

    $largada = [];
    foreach($resultados as $resultado){
        if($resultado->pilotoEquipe->piloto->id == $modelPiloto->id && $resultado->corrida->flg_sprint == 'N'){
            array_push($largada, $resultado->largada);
        }
    }

   @endphp
   
   <script>
    const ctx = document.getElementById('myChart');

    var labels= <?php echo json_encode($labels); ?>;
    var chegada = <?php echo json_encode($chegada); ?>;
    var largada = <?php echo json_encode($largada); ?>;
    console.log(chegada);
      
     const myChart = new Chart(ctx, {
      type: 'line',
      data: {
          labels: labels,
          datasets: [{
              label: 'Chegada',
              data: chegada,
              backgroundColor: [
                  'blue'
              ],
              borderColor: [
                  'blue'
              ],
            borderWidth: 2,
            fill: false,
            tension: 0.1
          },
        {
            label: 'Largada',
              data: largada,
              backgroundColor: [
                  'red'
              ],
              borderColor: [
                  'red'
              ],
            borderWidth: 2,
            fill: false,
            tension: 0.1 
        }]
      },
      options: {
        responsive: true,
          scales: {
              y: {
                beginAtZero: true,
                reverse: true,
                min: 0,
                max: 23,
                ticks: {
                    stepSize:1
                }
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

  </script>
@endsection

