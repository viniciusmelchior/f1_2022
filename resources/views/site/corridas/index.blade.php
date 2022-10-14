@extends('layouts.main')

@section('section')
{{-- <h1>Temporada {{$temporada->ano->ano}}</h1> --}}
  <div class="container mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Temporada {{$temporada->ano->ano}}</li>
        </ol>
    </nav>
    
    <div class="left_table">
        @if(count($corridas) > 0)
        <table class="table" id="tabelaCorridas">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pista</th>
                    <th>Dificuldade IA</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($corridas as $key => $corrida)
                    <tr @if($corrida->flg_sprint == 'S') style="color:red;" @endif>
                        <td>@if($corrida->flg_sprint != 'S') {{$corrida->ordem}} @endif</td>
                        <td>{{$corrida->pista->nome}} @if($corrida->flg_sprint == 'S') - Sprint @endif</td>
                        <td>{{$corrida->dificuldade_ia}}</td>
                        <td class="d-flex" style="justify-content: space-around;">
                            <a data-toggle="tooltip" data-placement="top" title="Visualizar resultados" href="{{route('resultados.show', [$corrida->id])}}"><i class="bi bi-eye-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Inserir Resultados" class="" href="{{route('resultados.edit', [$corrida->id])}}"><i class="bi bi-plus-circle-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Deletar" class="" href="{{route('corridas.delete', [$corrida->id])}}"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p>Nenhuma corrida cadastrada</p>
        @endif
    </div>
    <a href="{{route('corridas.adicionar', [$temporada->id])}}" class="btn btn-primary">Adicionar Corridas</a>
  </div>
@endsection