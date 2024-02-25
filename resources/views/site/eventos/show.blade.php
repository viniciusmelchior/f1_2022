@php 
 use App\Models\Site\Corrida;
 use App\Models\Site\Resultado;
 use App\Models\Site\Temporada;
use App\Models\Site\Titulo;
use App\Models\Site\PilotoEquipe;
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
    
    /* table, th, td {
        border: 1px solid black;
    } */

    table {
        outline: 1px solid black;
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
<div class="container mt-3 mb-3">
    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active">Resultados</li>
            <li class="breadcrumb-item active" aria-current="page">{{$resultadoCorridas[0]->pista->pais->des_nome}}</li>
        </ol>
    </nav>
   <div class="container">
    <div class="montaTabelaEquipes">
        <table class="mb-5 mt-5" id="tabelaClassificacaoEquipes">
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 5%;">Temporada</th>
                <th style="width: 15%;">Pista</th>
                <th>Pole Position</th>
                <th>Primeiro</th>
                <th>Segundo</th>
                <th>Terceiro</th>
                <th>Volta Rápida</th>
                <th>Visualizar</th>
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
                    <td>{{$resultadoCorrida->evento->des_nome}}
                        @if(isset($resultadoCorrida->condicao_id))
                            <i class="{{$resultadoCorrida->condicao->des_icone}}"></i>
                        @endif
                        @if($resultadoCorrida->qtd_safety_car > 0)
                            <i class="bi bi-car-front-fill mt-3"></i>
                        @endif
                    </td>
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
                    <td>
                        <a data-toggle="tooltip" data-placement="top" title="Visualizar" class="" href="{{route('resultados.show', [$resultadoCorrida->id])}}"><i class="bi bi-eye-fill" style="color: black;"></i></a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    <a href="{{route('paises.index')}}" class="btn btn-secondary ml-3 mb-3">Voltar</a>
   </div>
</div>
@endsection

