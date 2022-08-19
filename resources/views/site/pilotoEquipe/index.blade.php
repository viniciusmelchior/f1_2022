@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">
    @if(count($pilotoEquipes) > 0)
    <table class="table table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>Ano</th>
                <th>Piloto</th>
                <th>Equipe</th>
                <th>Status</th>
                <th>ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pilotoEquipes as $key => $pilotoEquipe)
                <tr style="color:{{$pilotoEquipe->equipe->des_cor}};">
                    <td>{{$key+1}}</td>
                    <td>{{$pilotoEquipe->ano->ano}}</td>
                    <td>{{$pilotoEquipe->piloto->nome}} {{$pilotoEquipe->piloto->sobrenome}}</td>
                    <td>{{$pilotoEquipe->equipe->nome}}</td>
                    <td>{{$pilotoEquipe->flg_ativo}}</td>
                    <td>
                        <a href="{{route('pilotoEquipe.edit', [$pilotoEquipe->id])}}">Editar</a>
                        <a class="" href="{{route('pilotoEquipe.delete', [$pilotoEquipe->id])}}">Excluir</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhum dado cadastrado</p>
    @endif
    <a href="{{route('pilotoEquipe.create')}}" class="btn btn-primary">Adicionar</a>
  </div>
@endsection