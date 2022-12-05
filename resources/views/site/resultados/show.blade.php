@extends('layouts.main')

<style>
    .TrvoltaRapida{
        color: purple
    }

    .trAbandono{
        color: red;
        font-style: italic;
    }
</style>

@section('section')
    <div class="container mt-3 mb-3">
        <div>
            <h2>GP de {{$corrida->pista->nome}} - {{$corrida->temporada->ano->ano}}</h2>
        </div>
        <div class="mb-3">
           Dificuldade IA:  {{$corrida->dificuldade_ia}}
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
                    <tr <?= $item->flg_abandono == 'S' ? "class='trAbandono'" : "" ?>>
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
        <div class="mb-3">
            Quantidade de Safety Car:  {{$corrida->qtd_safety_car}}
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" name="observacoes" id="observacoes" rows="5">
                @if($corrida->observacoes)
                    {{$corrida->observacoes}}
                @endif
            </textarea>
        </div>
    </div>
@endsection