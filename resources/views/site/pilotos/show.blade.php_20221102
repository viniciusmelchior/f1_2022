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
    
    table, th, td {
    border: 1px solid black;
    }
    
    table {
    border-collapse: collapse;
    margin: auto;
    }
    
    th, td{
    padding: 10px;
    text-align: center!important;
    width: 190px;
    }
    
    th{
    font-weight: bold;
    }
    
    
    tr:nth-child(even) {
    background-color: #dce6eb;
    }
    
    tr:hover:nth-child(1n + 2) {
    background-color: #a0200f;
    color: #fff;
    }
    
    .header-tabelas{
        padding: 15px;
        background-color: rgba(194, 26, 26, 0.993);
        text-align: center;
        font-size: 25px;
        font-weight: bolder;
        color: white;
    }

    .image-wrapper{
        max-width: 250px;
        margin: auto;
    }

    .image-wrapper img{
        max-width: 100%;
        border-radius: 2%;
    }
    
</style>
   <div class="container">
    {{-- <h1 class="mt-3">{{$modelPiloto->nome}} {{$modelPiloto->sobrenome}}</h1> --}}
    <div class="image-wrapper mt-3">
        <img src="{{asset('images/'.$modelPiloto->imagem)}}" alt="">
    </div>
        <table class="mt-2 mb-5">
            <tr>
                <th colspan="2">{{$modelPiloto->nome}} {{$modelPiloto->sobrenome}}</th>
            </tr>
            <tr>
                <td>Total De Corridas</td>
                <td>{{$totCorridas}}</td>
            </tr>
            <tr>
                <td>Vitórias</td>
                <td>{{$totVitorias}}</td>
            </tr>
            <tr>
                <td>Pole Positions</td>
                <td>{{$totPoles}}</td>
            </tr>
            <tr>
                <td>Pódios</td>
                <td>{{$totPodios}}</td>
            </tr>
            <tr>
                <td>Total de Pontos</td>
                <td>{{$totPontos}}</td>
            </tr>
            <tr>
                <td>Chegadas no Top 10</td>
                <td>{{$totTopTen}}</td>
            </tr>
            <tr>
                <td>Melhor Largada</td>
                <td>{{$melhorPosicaoLargada}}</td>
            </tr>
            <tr>
                <td>Pior Largada</td>
                <td>{{$piorPosicaoLargada}}</td>
            </tr>
            <tr>
                <td>Melhor Chegada</td>
                <td>{{$melhorPosicaoChegada}}</td>
            </tr>
            <tr>
                <td>Pior Chegada</td>
                <td>{{$piorPosicaoChegada}}</td>
            </tr>
            <tr>
                <td>Voltas Mais Rápidas</td>
                <td>{{$totVoltasRapidas}}</td>
            </tr>
            {{-- <tr>
                <td>Abandonos</td>
                <td>0</td>
            </tr> --}}
            <tr>
                <td>Status</td>
                <td>
                    @if($modelPiloto->flg_ativo == 'S')
                        Em Atividade
                    @else 
                        Aposentado
                    @endif
                </td>
            </tr>
        </table>

        @php 
            $resultados = Resultado::where('user_id', Auth::user()->id)->get();
        @endphp

        {{--Tabela de Largada e chegada--}}
        <h1>Resultados por Corrida</h1>
        <table class="mt-5 mb-5">
            <tr>
                <th></th>
                <th>Largada</th>
                <th>Chegada</th>
                <th>Variação</th>
            </tr>
            @foreach($resultados as $resultado)
                <tr>
                    @if($resultado->pilotoEquipe->piloto->id == $modelPiloto->id)
                        <td>{{$resultado->corrida->pista->nome}} - {{$resultado->corrida->temporada->ano->ano}}</td>
                        <td>{{$resultado->largada}}</td>
                        <td>{{$resultado->chegada}}</td>
                        <td>{{$resultado->largada-$resultado->chegada}}</td>
                    @endif
                </tr>
            @endforeach
        </table>

        {{--Gráficos--}}
        <div>
            <canvas id="myChart"></canvas>
        </div>

        {{--Graficos Final--}}
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
        if($resultado->pilotoEquipe->piloto->id == $modelPiloto->id){
            array_push($chegada, $resultado->chegada);
            array_push($labels, $resultado->corrida->pista->nome);
        }
    }

    $largada = [];
    foreach($resultados as $resultado){
        if($resultado->pilotoEquipe->piloto->id == $modelPiloto->id){
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
  
  </script>
@endsection

