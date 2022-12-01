@extends('layouts.main')

@section('section')

<style>
    table, th, td {
        border: 1px solid black;
    }

    table {
        border-collapse: collapse;
        margin: auto;
    }

    th, td{
        padding: 6px;
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

  <div class="container mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active">Temporadas</li>
            <li class="breadcrumb-item active" aria-current="page">Classificação Geral - {{$temporada->ano->ano}}</li>
        </ol>
    </nav>
    <div class="container">
        {{-- <h1 id="tituloClassificacao">Classificação Geral - {{$temporada->ano->ano}}</h1> --}}

        <div class="d-flex">
            <div class="montaTabelaPilotos">
                <table class="m-5" id="tabelaClassificacaoPilotos" style="">
                    <tr>
                        <th style="width:5%">Posição</th>
                        <th>Piloto</th>
                        <th style="width:5%">Pontos</th>
                        <th style="width:5%">Diferença</th>
                    </tr>
                    @if(count($resultadosPilotos) > 0)
                        @foreach($resultadosPilotos as $key => $piloto) 
                            <tr>
                                <td style="width:5%">{{$key+1}}</td>
                                <td>{{$piloto->nome}}</td>
                                <td class="pontosPiloto" style="width:5%">{{$piloto->total}}</td>
                                <td class="diferencaPontosPiloto" style="width:5%"></td>
                            </tr>
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
           
            <div class="montaTabelaEquipes">
                <table class="m-5" id="tabelaClassificacaoEquipes" style="">
                    <tr>
                        <th style="width:5%">Posição</th>
                        <th>Equipe</th>
                        <th style="width:5%">Pontos</th>
                        <th style="width:5%">Diferença</th>
                    </tr>
                    @if(count($resultadosEquipes) > 0)
                        @foreach($resultadosEquipes as $key => $equipe) 
                            <tr>
                                <td style="width:5%">{{$key+1}}</td>
                                <td>{{$equipe->nome}}</td>
                                <td class="pontosEquipe" style="width:5%">{{$equipe->total}}</td>
                                <td class="diferencaPontosEquipe" style="width:5%"></td>
                            </tr>
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
    <a href="{{route('temporadas.index')}}" class="btn btn-primary">Voltar</a>
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