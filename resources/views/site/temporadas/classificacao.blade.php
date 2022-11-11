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
                <table class="m-5" id="tabelaClassificacaoPilotos">
                    <tr>
                        <th>Posição</th>
                        <th>Piloto</th>
                        <th>Pontos</th>
                    </tr>
                    @if(count($resultadosPilotos) > 0)
                        @foreach($resultadosPilotos as $key => $piloto) 
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$piloto->nome}}</td>
                                <td>{{$piloto->total}}</td>
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
                <table class="m-5" id="tabelaClassificacaoEquipes">
                    <tr>
                        <th>Posição</th>
                        <th>Equipe</th>
                        <th>Pontos</th>
                    </tr>
                    @if(count($resultadosEquipes) > 0)
                        @foreach($resultadosEquipes as $key => $equipe) 
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$equipe->nome}}</td>
                                <td>{{$equipe->total}}</td>
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