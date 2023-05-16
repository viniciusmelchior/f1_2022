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

    /*importado dos pilotos*/
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
        {{-- <table class="mt-5 mb-5">
            <tr>
                <th colspan="2">{{$modelEquipe->nome}}</th>
            </tr>
            <tr>
                <td>Total De Largadas</td>
                <td>{{$totCorridas}}</td>
            </tr>
            <tr>
                <td>Vitórias</td>
                <td>{{$totVitorias}}</td>
            </tr>
            <tr>
                <td>Titulos de Construtores</td>
                <td>{{$totTitulos}}</td>
            </tr>
            <tr>
                <td>Titulos de Pilotos</td>
                <td>{{$totTitulosPilotos}}</td>
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
            <tr>
                <td>Status</td>
                <td>
                    @if($modelEquipe->flg_ativo == 'S')
                        Em Atividade
                    @else 
                        Aposentado
                    @endif
                </td>
            </tr>
        </table> --}}
        <select name="ajaxGetStatsEquipePorTemporada" id="ajaxGetStatsEquipePorTemporada" class="form-select mt-3" style="width: 20%; margin:0 auto;">
            <option value="" selected id="selectGetStatsEquipePorTemporada">Selecione uma Temporada</option>
            @foreach($temporadas as $temporada)
                <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
            @endforeach
        </select>
        <div id="driver-container">
            <div id="driver-details" class="bg-dark text-light">
                <div>
                    <div>
                        <div>
                            <h4>Nome da Equipe</h4>
                            <p>{{ $modelEquipe->nome }}</p>
                        </div>
                        <div>
                            <h4>País/Região</h4>
                            <p>{{ $modelEquipe->pais->des_nome }}</p>
                        </div>
                        <div>
                            <h4>Status</h4>
                            @if($modelEquipe->flg_ativo == 'S')
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
                    <img src="{{asset('images/'.$modelEquipe->imagem)}}" alt="">
                </div>
            </div>
            <div id="driver-stats" class="bg-dark text-light">
               <div>
                    <h4>Campeonato de Pilotos</h4>
                    <p>{{ $totTitulos }}</p>
               </div>
                <div>
                    <h4>Vitórias</h4>
                    <p id="equipe-tot-vitorias">{{ $totVitorias }}</p>
               </div>
                <div>
                    <h4>Pole Positions</h4>
                    <p id="equipe-tot-poles">{{ $totPoles }}</p>
               </div>
                <div>
                    <h4>Subidas ao Pódio</h4>
                    <p id="equipe-tot-podios">{{ $totPodios }}</p>
               </div>
                <div>
                    <h4>Total de Pontos</h4>
                    <p id="equipe-tot-pontos">{{ $totPontos }}</p>
               </div>
                <div>
                    <h4>Voltas mais rapidas</h4>
                    <p id="equipe-tot-voltas-rapidas">{{ $totVoltasRapidas }}</p>
               </div>
                <div class="other-stats">
                    <h4>Chegadas no top 10</h4>
                    <p id="equipe-tot-top-ten">{{ $totTopTen }}</p>
               </div>
                <div class="other-stats">
                    <h4>Melhor largada</h4>
                    <p id="equipe-melhor-largada">{{ $melhorPosicaoLargada }}º</p>
               </div>
                <div class="other-stats">
                    <h4>Pior Largada</h4>
                    <p id="equipe-pior-largada">{{ $piorPosicaoLargada }}º</p>
               </div>
                <div class="other-stats">
                    <h4>Melhor Chegada</h4>
                    <p id="equipe-melhor-chegada">{{ $melhorPosicaoChegada }}º</p>
               </div>
                <div class="other-stats">
                    <h4>pior Chegada</h4>
                    <p id="equipe-pior-chegada">{{ $piorPosicaoChegada }}º</p>
               </div>
                {{-- <div class="other-stats">
                    <h4>Abandonos</h4>
                    <p>{{ $totAbandonos }}</p>
               </div> --}}
                {{-- <div class="other-stats">
                    <h4>Grid Médio</h4>
                    <p>{{$gridMedio}}</p>
               </div> --}}
                {{-- <div class="other-stats">
                    <h4>Média Chegada</h4>
                    <p>{{$mediaChegada}}</p>
               </div> --}}
            </div>
        </div>

        <input type="hidden" id="equipe_id" name="equipe_id" value="{{$modelEquipe->id}}">

        <section class="mt-3" style="height: 400px;">
            <h1 class="mb-3" style="text-transform:uppercase;">Histórico de Pontuação</h1>
            <div style="width: 550px; height: 550px; margin: 0 auto;">
                <canvas id="historicoPontuacao"></canvas>
            </div>
        </section>

        {{--Graficos Final--}}
        <div class="mb-5">
            <div class="d-flex" style="justify-content: space-around;">
                <div class="">
                    <a href="{{route('equipes.index')}}" class="btn btn-primary">Voltar</a>
                </div>
                <div>
                    <a href="" class="btn btn-secondary">Gerar Excel</a>
                    {{-- <a href="{{route('equipes.export', [$modelEquipe->id])}}" class="btn btn-secondary">Gerar Excel</a> --}}
                </div>
            </div>
        </div>
   </div>

   <script>
    ajaxGetStatsEquipePorTemporada = "<?=route('ajax.ajaxGetStatsEquipePorTemporada')?>"
   </script>

<script>
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

  $('#ajaxGetStatsEquipePorTemporada').change(function (e) { 
    e.preventDefault();

    temporada_id = $('#ajaxGetStatsEquipePorTemporada').val();
    equipe_id = $('#equipe_id').val();
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    if(temporada_id != ''){
        selectTemporadaPolesPiloto = $('#selectGetStatsEquipePorTemporada').text('Geral');
    }

    $.ajax({
        type: "POST",
        url: ajaxGetStatsEquipePorTemporada,
        data: {temporada_id: temporada_id, equipe_id: equipe_id},
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        success: function (response) {
           $('#equipe-tot-vitorias').text(response.totVitorias)
           $('#equipe-tot-poles').text(response.totPoles)
           $('#equipe-tot-podios').text(response.totPoles)
           $('#equipe-tot-pontos').text(response.totPontos)
           $('#equipe-tot-voltas-rapidas').text(response.totVoltasRapidas)
           $('#equipe-tot-top-ten').text(response.totTopTen)
           $('#equipe-melhor-largada').text(response.melhorPosicaoLargada)
           $('#equipe-pior-largada').text(response.piorPosicaoLargada)
           $('#equipe-melhor-chegada').text(response.melhorPosicaoChegada)
           $('#equipe-pior-chegada').text(response.piorPosicaoChegada)
           $('#tot-corridas').text(response.totCorridas)
        },
        error:function(){
            alert(error)
        }
    });
      
});
</script>
@endsection

