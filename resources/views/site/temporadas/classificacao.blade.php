@extends('layouts.main')

@section('section')

<style>
   /*  table, th, td {
        border: 1px solid black;
    } */

    /* table {
        border-collapse: collapse;
        margin: auto;
    } */

   /*  th, td{
        padding: 6px;
        text-align: center!important;
        width: 190px;
    } */

   /*  th{
        font-weight: bold;
    } */

    /* tr:nth-child(even) {
        background-color: #dce6eb;
    } */

    /* tr:hover:nth-child(1n + 2) {
        background-color: #a0200f;
        color: #fff;
    } */

    .header-tabelas{
        padding-top: 1rem;
        padding-bottom: 1rem;
        /* background-color: rgba(194, 26, 26, 0.993); */
        text-align: center;
        font-size: 25px;
        font-weight: bolder;
        margin-bottom: 1rem;
        /* color: white; */
    }

    .text-upper{
        text-transform: uppercase;
    }

    .driver-surname{
        text-transform: uppercase;
        font-weight: bolder;
    }

</style>

  <div class="container mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active">Temporadas</li>
            <li class="breadcrumb-item active" aria-current="page">Classificação Geral - {{$temporada->ano->ano}}</li>
        </ol>
    </nav>

    <div class="container">
        <div class="header-tabelas bg-dark text-light">Pontuação Normal</div>
        <div class="d-flex bg-dark text-light p-3" style="justify-content: space-around;">
            <div class="montaTabelaPilotos">
                <table class="table text-light" id="tabelaClassificacaoPilotos">
                    <thead>
                        <tr>
                            <th class="text-upper">#</th>
                            <th class="text-upper">Piloto</th>
                            <th class="text-upper">Pontos</th>
                            <th class="text-upper">Diferença</th>
                        </tr>
                    </thead>
                    @if(count($resultadosPilotos) > 0)
                        @foreach($resultadosPilotos as $key => $piloto) 
                        <tbody>
                            <tr>
                                <td style="">{{$key+1}}</td>
                                <td style="vertical-align: middle;">
                                    <img src="{{asset('images/'.$piloto->imagem)}}" alt="" style="width: 25px; height:25px;">
                                    <span style="display: inline-block; vertical-align: middle;">{{$piloto->nome}}</span>
                                    <span class="driver-surname" style="display: inline-block; vertical-align: middle;">{{$piloto->sobrenome}}</span>
                                </td>
                                <td class="pontosPiloto" style="">{{$piloto->total}}</td>
                                <td class="diferencaPontosPiloto" style=""></td>
                            </tr>
                        </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
           
            <div class="montaTabelaEquipes">
                <table class="table text-light" id="tabelaClassificacaoEquipes" style="">
                    <thead>
                        <tr>
                            <th class="text-upper">#</th>
                            <th class="text-upper">Equipe</th>
                            <th class="text-upper">Pontos</th>
                            <th class="text-upper">Diferença</th>
                        </tr>
                    </thead>
                    @if(count($resultadosEquipes) > 0)
                        @foreach($resultadosEquipes as $key => $equipe) 
                            <tbody>
                                <tr>
                                    <td style="">{{$key+1}}</td>
                                    <td style="vertical-align: middle;">
                                        <img src="{{asset('images/'.$equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                        <span style="display: inline-block; vertical-align: middle;">{{$equipe->nome}}</span>
                                    </td>
                                    <td class="pontosEquipe" style="">{{$equipe->total}}</td>
                                    <td class="diferencaPontosEquipe"></td>
                                </tr>
                            </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        <hr>
        <div class="header-tabelas bg-dark text-light">Pontuação Clássica</div>
        <div class="d-flex bg-dark text-light p-3" style="justify-content: space-around;">
            <div class="montaTabelaPilotos">
                <table class="table text-light" id="tabelaClassificacaoPilotos">
                    <thead>
                        <tr>
                            <th class=" text-upper">#</th>
                            <th class="text-upper">Piloto</th>
                            <th class="text-upper">Pontos</th>
                        </tr>
                    </thead>
                    @if(count($resultadosPilotosClassico) > 0)
                        @foreach($resultadosPilotosClassico as $key => $piloto) 
                        <tbody>
                            <tr>
                                <td style="">{{$key+1}}</td>
                                <td style="vertical-align: middle;">
                                    <img src="{{asset('images/'.$piloto->imagem)}}" alt="" style="width: 25px; height:25px;">
                                    <span style="display: inline-block; vertical-align: middle;">{{$piloto->nome}}</span>
                                    <span class="driver-surname" style="display: inline-block; vertical-align: middle;">{{$piloto->sobrenome}}</span>
                                </td>
                                <td>{{$piloto->total}}</td>
                            </tr>
                        </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
           
            <div class="montaTabelaEquipes">
                <table class="table text-light" id="tabelaClassificacaoEquipes" style="">
                    <thead>
                        <tr>
                            <th class="text-upper">#</th>
                            <th class="text-upper">Equipe</th>
                            <th class="text-upper">Pontos</th>
                        </tr>
                    </thead>
                    @if(count($resultadosEquipesClassico) > 0)
                        @foreach($resultadosEquipesClassico as $key => $equipe) 
                            <tbody>
                                <tr>
                                    <td style="">{{$key+1}}</td>
                                    <td style="vertical-align: middle;">
                                        <img src="{{asset('images/'.$equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                        <span style="display: inline-block; vertical-align: middle;">{{$equipe->nome}}</span>
                                    </td>
                                    <td>{{$equipe->total}}</td>
                                </tr>
                            </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        <hr>
        <div class="header-tabelas bg-dark text-light">Pontuação Invertida</div>
        <div class="d-flex bg-dark text-light p-3" style="justify-content: space-around;">
            <div class="montaTabelaPilotos">
                <table class="table text-light" id="tabelaClassificacaoPilotos">
                    <thead>
                        <tr>
                            <th class=" text-upper">#</th>
                            <th class="text-upper">Piloto</th>
                            <th class="text-upper">Pontos</th>
                        </tr>
                    </thead>
                    @if(count($resultadosPilotosInvertida) > 0)
                        @foreach($resultadosPilotosInvertida as $key => $piloto) 
                        <tbody>
                            <tr>
                                <td style="">{{$key+1}}</td>
                                <td style="vertical-align: middle;">
                                    <img src="{{asset('images/'.$piloto->imagem)}}" alt="" style="width: 25px; height:25px;">
                                    <span style="display: inline-block; vertical-align: middle;">{{$piloto->nome}}</span>
                                    <span class="driver-surname" style="display: inline-block; vertical-align: middle;">{{$piloto->sobrenome}}</span>
                                </td>
                                <td>{{$piloto->total}}</td>
                            </tr>
                        </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
           
            <div class="montaTabelaEquipes">
                <table class="table text-light" id="tabelaClassificacaoEquipes" style="">
                    <thead>
                        <tr>
                            <th class="text-upper">#</th>
                            <th class="text-upper">Equipe</th>
                            <th class="text-upper">Pontos</th>
                        </tr>
                    </thead>
                    @if(count($resultadosEquipesInvertida) > 0)
                        @foreach($resultadosEquipesInvertida as $key => $equipe) 
                            <tbody>
                                <tr>
                                    <td style="">{{$key+1}}</td>
                                    <td style="vertical-align: middle;">
                                        <img src="{{asset('images/'.$equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                        <span style="display: inline-block; vertical-align: middle;">{{$equipe->nome}}</span>
                                    </td>
                                    <td>{{$equipe->total}}</td>
                                </tr>
                            </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        <hr>
        <div class="header-tabelas bg-dark text-light">Pontuação Alternativa</div>
        <div class="d-flex bg-dark text-light p-3" style="justify-content: space-around;">
            <div class="montaTabelaPilotos">
                <table class="table text-light" id="tabelaClassificacaoPilotos">
                    <thead>
                        <tr>
                            <th class=" text-upper">#</th>
                            <th class="text-upper">Piloto</th>
                            <th class="text-upper">Pontos</th>
                        </tr>
                    </thead>
                    @if(count($resultadosPilotosAlternativa) > 0)
                        @foreach($resultadosPilotosAlternativa as $key => $piloto) 
                        <tbody>
                            <tr>
                                <td style="">{{$key+1}}</td>
                                <td style="vertical-align: middle;">
                                    <img src="{{asset('images/'.$piloto->imagem)}}" alt="" style="width: 25px; height:25px;">
                                    <span style="display: inline-block; vertical-align: middle;">{{$piloto->nome}}</span>
                                    <span class="driver-surname" style="display: inline-block; vertical-align: middle;">{{$piloto->sobrenome}}</span>
                                </td>
                                <td>{{$piloto->total}}</td>
                            </tr>
                        </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
           
            <div class="montaTabelaEquipes">
                <table class="table text-light" id="tabelaClassificacaoEquipes" style="">
                    <thead>
                        <tr>
                            <th class="text-upper">#</th>
                            <th class="text-upper">Equipe</th>
                            <th class="text-upper">Pontos</th>
                        </tr>
                    </thead>
                    @if(count($resultadosEquipesAlternativa) > 0)
                        @foreach($resultadosEquipesAlternativa as $key => $equipe) 
                            <tbody>
                                <tr>
                                    <td style="">{{$key+1}}</td>
                                    <td style="vertical-align: middle;">
                                        <img src="{{asset('images/'.$equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                        <span style="display: inline-block; vertical-align: middle;">{{$equipe->nome}}</span>
                                    </td>
                                    <td>{{$equipe->total}}</td>
                                </tr>
                            </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <a href="{{route('temporadas.index')}}" class="btn btn-primary mt-3 bg-dark">Voltar</a>
  </div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
</script>

<script>
    $(document).ready(function () {
        tdPontuacaoPilotos = document.querySelectorAll('.pontosPiloto');
        tdDiferencaPilotos = document.querySelectorAll('.diferencaPontosPiloto')
        tdPontuacaoEquipes = document.querySelectorAll('.pontosEquipe');
        tdDiferencaEquipes = document.querySelectorAll('.diferencaPontosEquipe')
        
        contadorPilotos = tdPontuacaoPilotos.length-2
        contadorEquipes = tdPontuacaoEquipes.length-2
        //pega o penultimo e soma adiciona no ultimo. Por isso tem ser o total -2()
        for(let i = 0; i < tdPontuacaoPilotos.length; i = i + 1 ) {
            if(i <= contadorPilotos){
                diferencaPilotos = parseInt(tdPontuacaoPilotos[0].innerText) - parseInt(tdPontuacaoPilotos[i+1].innerText ) 
                tdDiferencaPilotos[i+1].innerText = ` - ${diferencaPilotos}`;
            }
        }
        
        for(let j = 0; j < tdPontuacaoEquipes.length; j = j + 1 ) {
            if(j <= contadorEquipes){
                diferencaEquipes = parseInt(tdPontuacaoEquipes[0].innerText) - parseInt(tdPontuacaoEquipes[j+1].innerText ) 
                tdDiferencaEquipes[j+1].innerText = ` - ${diferencaEquipes}`;
            }
        }
    });
</script>