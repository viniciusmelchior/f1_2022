@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">
    @if(count($condicaoClimaticas) > 0)
    <table class="table table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>Descrição</th>
                <th>Ícone</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($condicaoClimaticas as $key => $condicaoClimatica)
                <tr>
                    <td>{{$key+1}}</td>
                    <td>{{$condicaoClimatica->descricao}}</td>
                    <td><i class="{{$condicaoClimatica->des_icone}}"></i></td>
                    <td>
                        <a href="{{route('condicaoClimatica.edit', [$condicaoClimatica->id])}}">Editar</a>
                        <a class="" href="{{route('condicaoClimatica.delete', [$condicaoClimatica->id])}}">Excluir</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhum dado cadastrado</p>
    @endif
    <a href="{{route('condicaoClimatica.create')}}" class="btn btn-primary">Adicionar</a>
  </div>
@endsection