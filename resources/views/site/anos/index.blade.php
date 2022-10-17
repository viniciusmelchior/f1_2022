@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">
    @if(count($anos) > 0)
    <table class="table table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>Ano</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($anos as $key => $ano)
                <tr>
                    <td>{{$key+1}}</td>
                    <td>{{$ano->ano}}</td>
                    <td>{{$ano->flg_ativo}}</td>
                    <td>
                        <a href="{{route('anos.edit', [$ano->id])}}">Editar</a>
                        <a class="" href="{{route('anos.delete', [$ano->id])}}">Excluir</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhum dado cadastrado</p>
    @endif
    <a href="{{route('anos.create')}}" class="btn btn-primary">Adicionar</a>
  </div>
@endsection