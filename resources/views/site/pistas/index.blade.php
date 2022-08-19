@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">
    @if(count($pistas) > 0)
    <table class="table table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>País</th>
                <th>Status</th>
                <th>ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pistas as $key => $pista)
                <tr>
                    <td>{{$key+1}}</td>
                    <td>{{$pista->nome}}</td>
                    <td>{{$pista->pais->des_nome}}</td>
                    <td>{{$pista->flg_ativo}}</td>
                    <td>
                        <a href="{{route('pistas.edit', [$pista->id])}}">Editar</a>
                        <a class="" href="{{route('pistas.delete', [$pista->id])}}">Excluir</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhuma pista cadastrada</p>
    @endif
    <a href="{{route('pistas.create')}}" class="btn btn-primary">Adicionar pista</a>
  </div>
@endsection