@extends('layouts.main')

@section('section')
    <div class="container mt-3 mb-3">
        <div>
            <h2>GP de {{$corrida->pista->nome}}</h2>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Piloto</th>
                    <th>Equipe</th>
                    <th>Largada</th>
                    <th>Chegada</th>
                    <th>Pontos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($model as $key => $item)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$item->pilotoEquipe->piloto->nome}} {{$item->pilotoEquipe->piloto->sobrenome}}</td>
                        <td>{{$item->pilotoEquipe->equipe->nome}}</td>
                        <td>{{$item->largada}}</td>
                        <td>{{$item->chegada}}</td>
                        <td>{{$item->pontuacao}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection