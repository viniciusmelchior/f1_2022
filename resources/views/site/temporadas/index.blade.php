@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Temporadas</li>
        </ol>
    </nav>

    <div class="table-responsive">
        @if(count($temporadas) > 0)
        <table class="table" id="tabelaTemporadas">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descrição</th>
                    <th>Ano</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($temporadas as $key => $temporada)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$temporada->des_temporada}}</td>
                        <td>{{$temporada->ano->ano}}</td>
                        <td>@if($temporada->flg_finalizada == 'S')<i class="bi bi-check-square-fill"></i>@else Em Andamento @endif</td>
                        <td class="d-flex" style="justify-content: space-between;">
                            <a data-toggle="tooltip" data-placement="top" title="Classificação" href="{{route('temporadas.classificacao', [$temporada->id])}}"><i class="bi bi-table"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Editar Temporada" href="{{route('temporadas.edit', [$temporada->id])}}"><i class="bi bi-pencil-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Visualizar Corridas" href="{{route('corridas.index', [$temporada->id])}}"><i class="bi bi-eye-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Adicionar Corridas" class="" href="{{route('corridas.adicionar', [$temporada->id])}}"><i class="bi bi-plus-circle-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Deletar" class="" href="{{route('temporadas.delete', [$temporada->id])}}"><i class="bi bi-trash-fill"></i></a>
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
@endsection