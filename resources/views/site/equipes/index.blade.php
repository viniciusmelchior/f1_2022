@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">
    @if(count($equipes) > 0)
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
            @foreach ($equipes as $key => $equipe)
                <tr>
                    <td>{{$key+1}}</td>
                    <td style="color:{{$equipe->des_cor}};">{{$equipe->nome}}</td>
                    <td>{{$equipe->pais->des_nome}}</td>
                    <td>{{$equipe->flg_ativo}}</td>
                    <td>
                        <a href="{{route('equipes.edit', [$equipe->id])}}">Editar</a>
                        <a class="" href="{{route('equipes.delete', [$equipe->id])}}">Excluir</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhuma equipe cadastrada</p>
    @endif
    <a href="{{route('equipes.create')}}" class="btn btn-primary">Adicionar Equipe</a>
  </div>
@endsection