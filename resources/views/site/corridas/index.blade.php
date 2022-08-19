@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">
    @if(count($corridas) > 0)
    <table class="table table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>Pista</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($corridas as $key => $corrida)
                <tr @if($corrida->flg_sprint == 'S') style="color:red;" @endif>
                    <td>@if($corrida->flg_sprint != 'S') {{$corrida->ordem}} @endif</td>
                    <td>{{$corrida->pista->nome}} @if($corrida->flg_sprint == 'S') - Sprint @endif</td>
                    <td>
                        <a href="{{route('resultados.show', [$corrida->id])}}">Visualizar</a>
                        <a class="" href="{{route('resultados.edit', [$corrida->id])}}">Inserir Resultados</a>
                        <a class="" href="{{route('corridas.delete', [$corrida->id])}}">Excluir</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhuma corrida cadastrada</p>
    @endif
    <a href="{{route('corridas.create')}}" class="btn btn-primary">Adicionar Corridas</a>
  </div>
@endsection