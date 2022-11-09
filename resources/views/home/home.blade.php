@php 
    use App\Models\Site\Resultado;
    use App\Models\Site\Temporada;
    use App\Models\Site\Corrida;
    use App\Models\Site\PilotoEquipe;
@endphp

@extends('layouts.main')

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

@section('section')
    <div class="container">
        <div class="header-tabelas m-3">Vitórias</div>
        <div class="d-flex">
            <div>
               <h1>Pilotos</h1>
               <table class="m-5">
                    <tr>
                        <th>#</th>
                        <th>Piloto</th>
                        <th>Vitórias</th>
                    </tr>
                    @php 
                        $vitoriasPiloto = Resultado::where('user_id', Auth::user()->id)->where('chegada', 1)->get();
                        $vencedores = [];
                        foreach($vitoriasPiloto as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($vencedores, $item->pilotoEquipe->piloto->nomeCompleto());
                            }
                        }

                        $totPorPiloto = array_count_values($vencedores);
                        arsort($totPorPiloto);
                    @endphp
                    @foreach($totPorPiloto as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
                        </tr>
                    @endforeach
               </table>
            </div>

            <div>
                <h1>Equipes</h1>
                <table class="m-5">
                     <tr>
                         <th>#</th>
                         <th>Equipe</th>
                         <th>Vitórias</th>
                     </tr>
                     @php
                        $vitoriaEquipes = Resultado::where('user_id', Auth::user()->id)->where('chegada', 1)->get();
                        $vencedores = [];
                        foreach($vitoriaEquipes as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($vencedores, $item->pilotoEquipe->equipe->nome);
                            }
                        }

                        $totPorEquipe = array_count_values($vencedores);
                        arsort($totPorEquipe);
                    @endphp
                     @foreach($totPorEquipe as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
                        </tr>
                     @endforeach
                </table>
             </div>
        </div>

        <hr class="separador">

        <div class="header-tabelas m-3">Pole Positions</div>

        <div class="d-flex">
            <div>
               <h1>Pilotos</h1>
               <table class="m-5">
                    <tr>
                        <th>#</th>
                        <th>Piloto</th>
                        <th>Poles</th>
                    </tr>
                    @php 
                        $polePilotos = Resultado::where('user_id', Auth::user()->id)->where('largada', 1)->get();
                        $poles = [];
                        foreach($polePilotos as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($poles, $item->pilotoEquipe->piloto->nomeCompleto());
                            }
                        }

                        $totPorPiloto = array_count_values($poles);
                        arsort($totPorPiloto);
                    @endphp
                    @foreach($totPorPiloto as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
                        </tr>
                    @endforeach
               </table>
            </div>

            <div>
                <h1>Equipes</h1>
                <table class="m-5">
                     <tr>
                         <th>#</th>
                         <th>Piloto</th>
                         <th>Poles</th>
                     </tr>
                     @php 
                         $poleEquipes = Resultado::where('user_id', Auth::user()->id)->where('largada', 1)->get();
                        $poles = [];
                        foreach($poleEquipes as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($poles, $item->pilotoEquipe->equipe->nome);
                            }
                        }

                        $totPorEquipe= array_count_values($poles);
                        arsort($totPorEquipe);
                     @endphp
                     @foreach($totPorEquipe as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
                        </tr>
                     @endforeach
                </table>
             </div>
        </div>

        <hr class="separador">
        @php 
            $temporadas = Temporada::where('user_id', Auth::user()->id)->get();
        @endphp
        <div>
            <select name="mudarTemporada" id="mudarTemporada">
                <option value="" selected>Selecione uma Temporada</option>
                @foreach($temporadas as $temporada)
                    <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                @endforeach
            </select>
        </div>

        <h1 id="tituloClassificacao">Classificação Geral</h1>
    
    <div class="d-flex">
        <div class="montaTabelaPilotos">
            <table class="m-5" id="tabelaClassificacaoPilotos">
                <tr>
                    <th>Posição</th>
                    <th>Piloto</th>
                    <th>Pontos</th>
                </tr>
                <tr>
                    <td colspan="3">Selecione uma Temporada</td>
                </tr>
            </table>
        </div>
       
        <div class="montaTabelaEquipes">
            <table class="m-5" id="tabelaClassificacaoEquipes">
                <tr>
                    <th>Posição</th>
                    <th>Equipe</th>
                    <th>Pontos</th>
                </tr>
                <tr>
                    <td colspan="3">Selecione uma Temporada</td>
                </tr>
            </table>
        </div>
    </div>

    {{--resultados--}}
    @php 
        //dados das corridas
    $resultadoCorridas = Corrida::where('user_id', Auth::user()->id)->orderBy('temporada_id')->orderBy('ordem')->get();
    @endphp

    <hr>

    <h1 id="">Resultados</h1>
    <div class="montaTabelaEquipes">
        <table class="mb-5 mt-5" id="tabelaClassificacaoEquipes">
            <tr>
                <th>#</th>
                <th>Temporada</th>
                <th>Pista</th>
                <th>Pole Position</th>
                <th>Primeiro</th>
                <th>Segundo</th>
                <th>Terceiro</th>
                <th>Volta Mais Rápida</th>
            </tr>
            @foreach($resultadoCorridas as $key => $resultadoCorrida)
            @php 

            $primeiro = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $resultadoCorrida->id)->where('chegada', 1)->first();
            $polePosition = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $resultadoCorrida->id)->where('largada', 1)->first();
            $segundo = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $resultadoCorrida->id)->where('chegada', 2)->first();
            $terceiro = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $resultadoCorrida->id)->where('chegada', 3)->first();
            $voltaRapida = PilotoEquipe::where('user_id', Auth::user()->id)->where('id', $resultadoCorrida->volta_rapida)->first();
            
            @endphp
                <tr @if($resultadoCorrida->flg_sprint == 'S') style="font-style:italic;" @endif>
                    <td>
                        @if($resultadoCorrida->flg_sprint != 'S')
                        {{$resultadoCorrida->ordem}}
                        @else 
                        Sprint
                        @endif
                    </td>
                    <td>
                        {{$resultadoCorrida->temporada->ano->ano}}
                    </td>
                    <td>{{$resultadoCorrida->pista->nome}}</td>
                    <td>
                        @if(isset($polePosition))
                        <span style="color:{{$polePosition->pilotoEquipe->equipe->des_cor}};">
                            {{$polePosition->pilotoEquipe->piloto->nomeCompleto()}}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(isset($primeiro))
                        <span style="color:{{$primeiro->pilotoEquipe->equipe->des_cor}};">
                            {{$primeiro->pilotoEquipe->piloto->nomeCompleto()}} 
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(isset($segundo))
                        <span style="color:{{$segundo->pilotoEquipe->equipe->des_cor}};">
                            {{$segundo->pilotoEquipe->piloto->nomeCompleto()}}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(isset($terceiro))
                        <span style="color:{{$terceiro->pilotoEquipe->equipe->des_cor}};">
                            {{$terceiro->pilotoEquipe->piloto->nomeCompleto()}}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(isset($voltaRapida))
                            <span style="color:{{$voltaRapida->equipe->des_cor}};">
                                {{$voltaRapida->piloto->nomeCompleto()}}
                            </span>
                        @else
                        -
                        @endif 
                    </td>
                    {{-- @if(isset($voltaRapida))
                        <td></td>
                    @else
                    -
                    @endif --}}
                </tr>
            @endforeach
        </table>
    </div>



        
    </div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
</script>

<script>
    urlclassificacaoGeralPorTemporada = "<?=route('ajax.classificacaoGeralPorTemporada')?>"
</script>

<script>
   $(document).ready(function () {

        $("#mudarTemporada").change(function (e) { 
        e.preventDefault();
        //console.log($("#mudarTemporada").val());

        temporada_id = $("#mudarTemporada").val();
        tabelaClassificacaoPilotos = $('#tabelaClassificacaoPilotos');
        tabelaClassificacaoEquipes = $('#tabelaClassificacaoEquipes');

        if(temporada_id == ""){
            tituloClassificacao = $('#tituloClassificacao').text('Classificação Geral');
            tabelaClassificacaoPilotos.html('')
            tabelaClassificacaoEquipes.html('')
            tabelaClassificacaoPilotos.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
            tabelaClassificacaoPilotos.append("<tr><td colspan='3'>Selecione uma Temporada</td></tr>");
            tabelaClassificacaoEquipes.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
            tabelaClassificacaoEquipes.append("<tr><td colspan='3'>Selecione uma Temporada</td></tr>");
        }
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "POST",
                url: urlclassificacaoGeralPorTemporada,
                data: {temporada_id: temporada_id},
                contentType: "application/x-www-form-urlencoded;charset=UTF-8",
                success: function (response) {
                    tituloClassificacao = $('#tituloClassificacao').text('Classificação Geral '+response.temporada.des_temporada);
                    tabelaClassificacaoPilotos.html('')
                    tabelaClassificacaoEquipes.html('')
                    if(response.resultadosPilotos.length > 0){
                        contPilotos = 1;
                        tabelaClassificacaoPilotos.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
                        response.resultadosPilotos.map(function(response){ 
                        tabelaClassificacaoPilotos.append("<tr class='remover'><td>"+contPilotos+"</td><td>"+response.nome+"</td><td>"+response.total+"</td></tr>");
                        contPilotos++
                        })
                    } else {
                        tabelaClassificacaoPilotos.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
                        tabelaClassificacaoPilotos.append("<tr><td colspan='3'>Sem Dados Cadastrados</td></tr>");
                    }

                    if(response.resultadosEquipes.length > 0){
                        contEquipes = 1;
                        tabelaClassificacaoEquipes.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
                        response.resultadosEquipes.map(function(response){ 
                        tabelaClassificacaoEquipes.append("<tr><td>"+contEquipes+"</td><td>"+response.nome+"</td><td>"+response.total+"</td></tr>");
                        contEquipes++
                        })
                    }else{
                        tabelaClassificacaoEquipes.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
                        tabelaClassificacaoEquipes.append("<tr><td colspan='3'>Sem Dados Cadastrados</td></tr>");
                    }
              
                },
                error:function(){
                    alert(error)
                }
            });

        });
   });

   
3
$(document).ready( function () {
    //$('#tabelaClassificacaoPilotos').DataTable();
} );
</script>