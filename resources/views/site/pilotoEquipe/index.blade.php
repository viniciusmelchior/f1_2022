@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Relação entre Pilotos e Equipes</li>
        </ol>
    </nav>

    <div class="card mt-3 mb-3 p-3">
        <div class="card-body">
        <label for="">Pesquisar</label>
        <input type="text" id="caixaBusca">
        </div>
    </div>
    
    @if(count($pilotoEquipes)> 0)
    <table class="table tabelaPaginada" id="pilotoEquipesTable">
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
        <tbody id="">
            @foreach ($pilotoEquipes as $key => $pilotoEquipe)
                <tr style="color:{{$pilotoEquipe->equipe->des_cor}};">
                    <td>{{$key+1}}</td>
                    <td>{{$pilotoEquipe->ano->ano}}</td>
                    <td>{{$pilotoEquipe->piloto->nome}} {{$pilotoEquipe->piloto->sobrenome}}</td>
                    <td>{{$pilotoEquipe->equipe->nome}}</td>
                    <td>{{$pilotoEquipe->flg_ativo}}</td>
                    <td>
                        <a href="{{route('pilotoEquipe.edit', [$pilotoEquipe->id])}}"><i class="bi bi-pencil-fill"></i></a>
                        <a class="" href="{{route('pilotoEquipe.delete', [$pilotoEquipe->id])}}"><i class="bi bi-trash-fill"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhum dado cadastrado</p>
    @endif
    <div id="pagination"></div>
    <a href="{{route('pilotoEquipe.create')}}" class="btn btn-primary">Adicionar</a>
    <a href="{{route('dashboard')}}" class="btn btn-secondary ml-3">Voltar</a>
  </div>

<script src="{{asset('js/pilotoEquipes/index.js')}}"></script>

@endsection