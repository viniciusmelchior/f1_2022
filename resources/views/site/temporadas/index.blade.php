@php
    use App\Models\Site\Temporada;
    use App\Models\Site\Equipe;
@endphp

@extends('layouts.main')

@section('section')

    @if (session('error'))
        <div class="alert alert-danger text-center">
            {{ session('error') }}
        </div>
    @endif

  <div class="mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Temporadas</li>
        </ol>
    </nav>

     <div class="card mt-3 mb-3 p-3">
        <div class="card-body">
        <label for="">Pesquisar</label>
        <input type="text" id="caixaBusca">
        </div>
    </div>

        @if(count($temporadas) > 0)
        <div class="table-responsive">
        <table class="custom-responsive-table align-middle" id="tabelaTemporadas">
            <thead>
                <tr>
                    <th>#</th>
                    <th onclick="sortTable(1, 'tabelaTemporadas')" style="cursor: pointer; text-align:center;">Ano</th>
                    <th style="text-align:center;">Descrição</th>
                    <th onclick="sortTable(3, 'tabelaTemporadas')" style="cursor: pointer; text-align:center;">Referência</th>
                    <th style="width: 20%; text-align:left;">Camp. Piloto</th>
                    <th style="width: 15%; text-align:left;">Camp. Construtores</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($temporadas as $key => $temporada)
                    <tr>
                        <td data-label="#">{{$key+1}}</td>
                        <td data-label="Ano">{{$temporada->ano->ano}}</td>
                        <td data-label="Temporada">{{$temporada->des_temporada}}
                            @isset($temporada->observacoes)
                                <i class="bi bi-info-circle text-primary" data-toggle="tooltip" data-placement="top" title="{{$temporada->observacoes}}">  
                            @endisset
                        </td>
                        <td data-label="Referência">
                            {{$temporada->referencia ?? '-'}}
                        </td>
                        <td data-label="Camp. Piloto" style="text-align:left;">
                            @if(isset($temporada->titulo))
                                @php $equipePiloto = $temporada->titulo->pilotoEquipe->equipe; @endphp
                                <span>
                                    @if($equipePiloto->imagem)
                                        <img src="{{asset('images/'.$equipePiloto->imagem)}}" alt="" style="width: 26px; height:26px; padding: 3px; border-radius: 50%; object-fit: contain; vertical-align: middle; box-shadow: 0 0 0 1px rgba(0,0,0,.15); background-color: {{Equipe::corFundoIcone($equipePiloto->des_cor, $equipePiloto->imagem)}};">
                                    @endif
                                    {{$temporada->titulo->pilotoEquipe->piloto->nomeCompleto()}}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td data-label="Camp. Construtores" style="text-align:left;">
                            @if(isset($temporada->titulo))
                                @php $equipeCampea = $temporada->titulo->equipe; @endphp
                                <span>
                                    @if($equipeCampea->imagem)
                                        <img src="{{asset('images/'.$equipeCampea->imagem)}}" alt="" style="width: 26px; height:26px; padding: 3px; border-radius: 50%; object-fit: contain; vertical-align: middle; box-shadow: 0 0 0 1px rgba(0,0,0,.15); background-color: {{Equipe::corFundoIcone($equipeCampea->des_cor, $equipeCampea->imagem)}};">
                                    @endif
                                    {{$equipeCampea->nome}}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td data-label="Status">@if($temporada->flg_finalizada == 'S')<i class="bi bi-check-square-fill"></i>@else Em Andamento @endif</td>
                        <td data-label="Ações" class="text-nowrap">
                            <div class="d-inline-flex gap-1">
                                <a class="btn btn-sm btn-outline-warning" data-toggle="tooltip" data-placement="top" title="Classificação" href="{{route('temporadas.classificacao', [$temporada->id])}}"><i class="bi bi-trophy"></i></a>
                                <a class="btn btn-sm btn-outline-info" data-toggle="tooltip" data-placement="top" title="Resultados (posições)" href="{{route('temporadas.resultados', [$temporada->id])}}"><i class="bi bi-list-ol"></i></a>
                                <a class="btn btn-sm" style="--bs-btn-color: #6f42c1; --bs-btn-border-color: #6f42c1; --bs-btn-hover-color: #fff; --bs-btn-hover-bg: #6f42c1; --bs-btn-hover-border-color: #6f42c1; --bs-btn-active-color: #fff; --bs-btn-active-bg: #6f42c1; --bs-btn-active-border-color: #6f42c1;" data-toggle="tooltip" data-placement="top" title="Resultados (pontuação)" href="{{route('temporadas.resultados', [$temporada->id, 'porPontuacao'])}}"><i class="bi bi-bar-chart"></i></a>
                                <a class="btn btn-sm" style="--bs-btn-color: #fd7e14; --bs-btn-border-color: #fd7e14; --bs-btn-hover-color: #fff; --bs-btn-hover-bg: #fd7e14; --bs-btn-hover-border-color: #fd7e14; --bs-btn-active-color: #fff; --bs-btn-active-bg: #fd7e14; --bs-btn-active-border-color: #fd7e14;" data-toggle="tooltip" data-placement="top" title="Visualizar Corridas" href="{{route('corridas.index', [$temporada->id])}}"><i class="bi bi-eye"></i></a>
                                <a class="btn btn-sm btn-outline-success" data-toggle="tooltip" data-placement="top" title="Adicionar Corridas" href="{{route('corridas.adicionar', [$temporada->id])}}"><i class="bi bi-plus-lg"></i></a>
                                <a class="btn btn-sm btn-outline-primary" data-toggle="tooltip" data-placement="top" title="Editar Temporada" href="{{route('temporadas.edit', [$temporada->id])}}"><i class="bi bi-pencil"></i></a>
                                <a class="btn btn-sm btn-outline-danger" data-toggle="tooltip" data-placement="top" title="Deletar" href="{{route('temporadas.delete', [$temporada->id])}}"><i class="bi bi-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
            <p>Nenhuma temporada cadastrada</p>
        @endif
    </div>
    <a href="{{route('temporadas.create')}}" class="btn btn-dark">Adicionar temporada</a>
    <a href="{{route('dashboard')}}" class="btn btn-danger ml-3">Voltar</a>
  </div>

<script src="{{asset('js/app.js')}}"></script>
<script src="{{asset('js/temporadas/index.js')}}"></script>
@endsection