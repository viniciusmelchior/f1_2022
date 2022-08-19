@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">
    @if(count($temporadas) > 0)
    <table class="table table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>Descrição</th>
                <th>Ano</th>
                <th>ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($temporadas as $key => $temporada)
                <tr>
                    <td>{{$key+1}}</td>
                    <td>{{$temporada->des_temporada}}</td>
                    <td>{{$temporada->ano->ano}}</td>
                    <td>
                        <a href="{{route('temporadas.edit', [$temporada->id])}}">Classificação</a>
                        <a href="{{route('temporadas.edit', [$temporada->id])}}">Editar</a>
                        <a class="" href="{{route('temporadas.delete', [$temporada->id])}}">Excluir</a>
                        <a href="{{route('corridas.index', [$temporada->id])}}">Visualizar Corridas</a>
                        <a class="" href="{{route('corridas.adicionar', [$temporada->id])}}">Adicionar Corridas</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhuma pista cadastrada</p>
    @endif
    <a href="{{route('temporadas.create')}}" class="btn btn-primary">Adicionar temporada</a>
  </div>
@endsection