@extends('layouts.main')

@section('section')
    @if (session('status'))
    <div class="alert alert-success text-center">
        {{ session('status') }}
    </div>
    @endif

  <div class="mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listagem de Equipes</li>
        </ol>
    </nav>

    <div class="card mt-3 mb-3 p-3">
        <div class="card-body">
        <label for="">Pesquisar</label>
        <input type="text" id="caixaBusca">
        </div>
    </div>

    <button id="toggleColunas" class="btn btn-outline-info mb-2">Esconder percentuais</button>

    @if(count($equipes) > 0)
    <table class="custom-responsive-table" id="tabelaEquipes">
            <thead>
                <tr>
                    <th onclick="sortTable(0, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">#</th>
                    <th>Nome</th>
                    <th onclick="sortTable(2, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">Corridas</th>
                    <th onclick="sortTable(3, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">Vitórias</th>
                    <th onclick="sortTable(4, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">% de Vitórias</th>
                    <th onclick="sortTable(5, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">Poles</th>
                    <th onclick="sortTable(6, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">% de Poles</th>
                    <th onclick="sortTable(7, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">Podios</th>
                    <th onclick="sortTable(8, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">% de Podios</th>
                    <th onclick="sortTable(9, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">Abandonos</th>
                    <th onclick="sortTable(10, 'tabelaEquipes')" style="cursor: pointer; text-align: center;">% Abandonos</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
        <tbody>
            @foreach ($equipes as $key => $equipe)
                <tr style="<?= $equipe->flg_ativo == 'N' ? "color:red;" : "" ?>">
                    <td data-label="#">{{$key+1}}</td>
                    <td data-label="Nome" style="vertical-align: middle; text-align:left;">
                        <img src="{{asset('images/'.$equipe->imagem_equipe)}}" style="width:25px; height:25px;">
                        <span style="display: inline-block; vertical-align: middle;">{{$equipe->equipe}}</span>
                        <img src="{{asset('images/'.$equipe->imagem_pais)}}" style="width:25px; height:20px;">
                    </td>
                     <td data-label="Corridas" >{{$equipe->total_corridas}}</td>
                    <td data-label="Vitorias">{{$equipe->vitorias}}</td>
                    <td data-label="% de Vitorias">{{ $equipe->aproveitamento_vitorias }}%</td>
                    <td data-label="Poles">{{$equipe->pole_positions}}</td>
                    <td data-label="% de Poles">{{ $equipe->aproveitamento_pole_positions }}%</td>
                    <td data-label="Podios">{{$equipe->podios}}</td>
                    <td data-label="% de Podios">{{ $equipe->aproveitamento_podios }}%</td>
                    <td data-label="Abandonos">{{$equipe->abandonos}}</td>
                    <td data-label="% de Abandonos">{{ $equipe->porcentagem_abandonos }}%</td>
                    <td data-label="Status">{{$equipe->flg_ativo}}</td>
                    <td data-label="Ações" class="coluna_acoes">
                        <a href="{{route('equipes.show', [$equipe->id_da_equipe])}}" class="custom-botao-visualizar"><i class="bi bi-eye-fill"></i></a>
                        <a href="{{route('equipes.edit', [$equipe->id_da_equipe])}}" class="custom-botao-editar"><i class="bi bi-pencil-fill"></i></a>
                        <a class="text-danger" href="{{route('equipes.delete', [$equipe->id_da_equipe])}}"><i class="bi bi-trash-fill"></i></a>
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

<script src="{{asset('js/app.js')}}"></script>
<script src="{{asset('js/equipes/index.js')}}"></script>
@endsection
