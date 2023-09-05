@php
    use App\Models\Site\PilotoEquipe;
@endphp

@extends('layouts.main')

@section('section')

<style>
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

    .hoverable:hover {
        background-color: white;
    }

    .text-upper{
        text-transform: uppercase;
    }

    .driver-surname{
        text-transform: uppercase;
        font-weight: bolder;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
    }

    .breadcrumb-item .breadcrumb-icon {
        margin-right: 5px;
    }

    @media (max-width: 769px){
        .ocultar-mobile{
            display: none;
        }

        .montaTabelaPilotos{
            margin-bottom: 50px;
        }
    }

    @media (min-width: 769px){
        
    }

    .table thead th.sticky-col,
    .table tbody td.sticky-col {
        position: sticky;
        left: 0;
        z-index: 1;
    }

</style>

<div class="container mt-3 mb-3">
    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">
                    <span class="breadcrumb-icon"><i class="fas fa-home"></i></span> Home
                </a>
            </li>
            <li class="breadcrumb-item active">Temporadas</li>
            <li class="breadcrumb-item active" aria-current="page">
                <span class="breadcrumb-icon"><i class="fas fa-calendar"></i></span> Resultados - {{$temporada->ano->ano}}
            </li>
        </ol>
    </nav>

    <div class="container">
        <div class="header-tabelas bg-dark text-light">Resultados</div>
        <div class="d-flex bg-dark text-light p-3" style="justify-content: space-around; flex-wrap: wrap;">
            <div class="montaTabelaPilotos table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th class="text-upper sticky-col hoverable">#</th>
                            <th class="text-upper w-100 sticky-col">Piloto</th>
                                @foreach ($corridas as $corrida)
                                    <th class="">
                                        {{-- <span style="display: inline-block; vertical-align: middle; white-space: nowrap;">{{$corrida->pista->nome}}</span> --}}
                                        <img src="{{asset('images/'.$corrida->pista->pais->imagem)}}" alt="" srcset="" style="width:35px; height: 25px;" data-toggle="tooltip" data-placement="top" title="{{$corrida->pista->nome}}">
                                    </th>
                                @endforeach
                            <th class="text-upper">Pontos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($resultadosPilotos) > 0)
                    <tbody id="TbodytabelaClassificacaoPilotos">
                        @foreach($resultadosPilotos as $key => $piloto) 
                            <tr>
                                <td style="" class="sticky-col">{{$key+1}}</td>
                                <td style="vertical-align: middle; white-space: nowrap; overflow: hidden;" class="w-100 sticky-col">
                                    <img src="{{ asset('images/' . $piloto->imagem) }}" alt="" style="width: 25px; height: 25px;">
                                    <span style="display: inline-block; vertical-align: middle;">{{ $piloto->nome }}</span>
                                    <span class="driver-surname" style="display: inline-block; vertical-align: middle;">{{ $piloto->sobrenome }}</span>
                                </td>
                                
                                @foreach ($corridas as $corrida)
                                    @php 
                                        $posicao = PilotoEquipe::getResultadoPilotoEquipe($corrida->id, $piloto->pilotoEquipe_id);
                                    @endphp
                                    <td class="text-center">
                                       @if($posicao)
                                        <div @if($corrida->volta_rapida == $piloto->pilotoEquipe_id) style="widh:100%; height:100%; background-color: purple; padding:0" @endif>
                                            <span @if($corrida->volta_rapida == $piloto->piloto_id) style="font-weight: bolder;" @endif>
                                                {{$posicao}}
                                            </span>
                                        </div>
                                       @else
                                            -
                                       @endif
                                    </td>
                                @endforeach
                                <td class="pontosPiloto text-center" style="">{{$piloto->total}}</td>
                            </tr>
                        @endforeach
                    </tbody> 
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                    </tbody>
                  </table>
            </div>
        </div>
    </div>
    <a href="{{route('temporadas.index')}}" class="btn btn-primary mt-3 bg-dark">Voltar</a>
</div>

@endsection