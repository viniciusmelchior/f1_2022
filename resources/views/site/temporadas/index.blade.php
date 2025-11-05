@php 
    use App\Models\Site\Temporada;
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
        <table class="custom-responsive-table" id="tabelaTemporadas">
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
                               @php $imagemPiloto = $temporada->titulo->pilotoEquipe->equipe->imagem; @endphp
                                <img src="{{asset('images/'.$imagemPiloto)}}" alt="" style="width: 25px; height:25px; vertical-align:middle;" class="img-logo-responsiva-piloto">
                                {{$temporada->titulo->pilotoEquipe->piloto->nomeCompleto()}}
                            @else
                                -
                            @endif
                        </td>
                        <td data-label="Camp. Construtores" style="text-align:left;">
                            @if(isset($temporada->titulo))
                            <img src="{{asset('images/'.$temporada->titulo->equipe->imagem)}}" alt="" style="width: 25px; height:25px; vertical-align:middle;" class="img-logo-responsiva-equipe">
                                {{$temporada->titulo->equipe->nome}}
                            @else
                                -
                            @endif
                        </td>
                        <td data-label="Status">@if($temporada->flg_finalizada == 'S')<i class="bi bi-check-square-fill"></i>@else Em Andamento @endif</td>
                        <td data-label="Ações">
                            <a class="custom-botao-coluna-acoes" data-toggle="tooltip" data-placement="top" title="Classificação" href="{{route('temporadas.classificacao', [$temporada->id])}}"><i class="bi bi-table"></i></a>
                            <a class="custom-botao-coluna-acoes" data-toggle="tooltip" data-placement="top" title="Resultados (posições)" href="{{route('temporadas.resultados', [$temporada->id])}}"><i class="bi bi-bar-chart-fill"></i></a>
                            <a class="custom-botao-coluna-acoes" data-toggle="tooltip" data-placement="top" title="Resultados (pontuação)" href="{{route('temporadas.resultados', [$temporada->id, 'porPontuacao'])}}"><i class="bi bi-bar-chart-fill text-warning"></i></a>
                            <a class="custom-botao-editar" data-toggle="tooltip" data-placement="top" title="Editar Temporada" href="{{route('temporadas.edit', [$temporada->id])}}"><i class="bi bi-pencil-fill"></i></a>
                            <a class="custom-botao-visualizar" data-toggle="tooltip" data-placement="top" title="Visualizar Corridas" href="{{route('corridas.index', [$temporada->id])}}"><i class="bi bi-eye-fill"></i></a>
                            <a class="custom-botao-adicionar" data-toggle="tooltip" data-placement="top" title="Adicionar Corridas" class="" href="{{route('corridas.adicionar', [$temporada->id])}}"><i class="bi bi-plus-circle-fill"></i></a>
                            <a class="custom-botao-excluir" data-toggle="tooltip" data-placement="top" title="Deletar" class="" href="{{route('temporadas.delete', [$temporada->id])}}"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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