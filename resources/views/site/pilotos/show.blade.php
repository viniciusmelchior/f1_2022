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
    
</style>
   <div class="container">
    {{-- <h1 class="mt-3">{{$modelPiloto->nome}} {{$modelPiloto->sobrenome}}</h1> --}}
        <table class="mt-5 mb-5">
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

        {{--Gráficos--}}

        {{-- <div class="my-5" style="width: 400px; height:400px;">
            <canvas id="myChart" width="200" height="200"></canvas>
        </div> --}}
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
    $labels = [];
    $corridas = Corrida::where('user_id', Auth::user()->id)->orderBy('temporada_id')->orderBy('ordem')->get();
    foreach($corridas as $corrida){
        if($corrida->flg_sprint == 'S'){
            $corrida->pista->nome =  $corrida->pista->nome." - Sprint";
        }
        array_push($labels, $corrida->pista->nome);
    }

    $chegada = [];
    // $resultados = Corrida::where('user_id', Auth::user()->id)->orderBy('temporada_id')->orderBy('ordem')->get();
    $resultados = Resultado::where('user_id', Auth::user()->id)->get();
    foreach($resultados as $resultado){
        if($resultado->pilotoEquipe->piloto->id == $modelPiloto->id){
            array_push($chegada, $resultado->chegada);
        }
    }

    $largada = [];
    // $resultados = Corrida::where('user_id', Auth::user()->id)->orderBy('temporada_id')->orderBy('ordem')->get();
    $resultados = Resultado::where('user_id', Auth::user()->id)->get();
    foreach($resultados as $resultado){
        if($resultado->pilotoEquipe->piloto->id == $modelPiloto->id){
            array_push($largada, $resultado->largada);
        }
    }

    //dd($modelPiloto);
    //dd($chegada);

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

