@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">
    @if(count($paises) > 0)
    <table class="table table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($paises as $key => $pais)
                <tr>
                    <td>{{$key+1}}</td>
                    <td>{{$pais->des_nome}}</td>
                    <td>
                        <a href="{{route('paises.edit', [$pais->id])}}">Editar</a>
                        <a class="" href="{{route('paises.delete', [$pais->id])}}">Excluir</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhum país cadastrado</p>
    @endif
    <a href="{{route('paises.create')}}" class="btn btn-primary">Adicionar País</a>
  </div>
@endsection