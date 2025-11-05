@php 
    use App\Models\Site\Resultado;
@endphp

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
                <li class="breadcrumb-item active" aria-current="page">Listagem de Pilotos</li>
            </ol>
        </nav>

        <div class="card mt-3 mb-3 p-3">
            <div class="card-body">
            <label for="">Pesquisar</label>
            <input type="text" id="caixaBusca">
            </div>
        </div>

        <button id="toggleColunas" class="btn btn-outline-info mb-2">Esconder percentuais</button>

        @if(count($pilotos) > 0)
            <table class="custom-responsive-table" id="tabelaPilotos">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">#</th>
                        <th>Nome</th>
                        <th onclick="sortTable(2, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">Corridas</th>
                        <th onclick="sortTable(3, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">Vitórias</th>
                        <th onclick="sortTable(4, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">% de Vitórias</th>
                        <th onclick="sortTable(5, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">Poles</th>
                        <th onclick="sortTable(6, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">% de Poles</th>
                        <th onclick="sortTable(7, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">Podios</th>
                        <th onclick="sortTable(8, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">% de Podios</th>
                        <th onclick="sortTable(9, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">Abandonos</th>
                        <th onclick="sortTable(10, 'tabelaPilotos')" style="cursor: pointer; text-align: center;">% Abandonos</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pilotos as $key => $piloto)
                        <tr style="<?= $piloto->flg_ativo == 'N' ? "color:red;" : "" ?>">
                            <td data-label="#">{{$key+1}}</td>
                            <td data-label="Nome" style="vertical-align: middle; text-align:left;">
                                <img src="{{asset('images/'.$piloto->imagem)}}" style="width:25px; height:25px;">
                                <span style="display: inline-block; vertical-align: middle;">{{$piloto->nome}} {{$piloto->sobrenome}}</span>
                                <img src="{{asset('images/'.$piloto->pais->imagem)}}" style="width:25px; height:20px;">
                            </td>
                            <td data-label="Corridas" >{{$piloto->corridas}}</td>
                            <td data-label="Vitorias">{{$piloto->vitorias}}</td>
                            <td data-label="% de Vitorias">{{ $piloto->aproveitamentoVitorias }}%</td> {{--vem do attribute na model--}}
                            <td data-label="Poles">{{$piloto->poles}}</td>
                            <td data-label="% de Poles">{{ $piloto->aproveitamentoPoles }}%</td>
                            <td data-label="Podios">{{$piloto->podios}}</td>
                            <td data-label="% de Podios">{{ $piloto->aproveitamentoPodios }}%</td>
                            <td data-label="Abandonos">{{$piloto->abandonos}}</td>
                            <td data-label="% de Abandonos">{{ $piloto->aproveitamentoAbandonos }}%</td>
                            <td data-label="Status">{{$piloto->flg_ativo}}</td>
                            <td data-label="Ações">
                                <a href="{{route('pilotos.show', [$piloto->id])}}" class="custom-botao-visualizar"><i class="bi bi-eye-fill"></i></a>
                                <a href="{{route('pilotos.edit', [$piloto->id])}}" class="custom-botao-editar"><i class="bi bi-pencil-fill"></i></a>
                                <button type="button" class="deletePiloto btn btn-link p-0 custom-botao-excluir" value="{{$piloto->id}}"><i class="bi bi-trash-fill"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum piloto cadastrado</p>
        @endif
        </div>
        <a href="{{route('pilotos.create')}}" class="btn btn-primary">Adicionar Piloto</a>

 <!-- Modal exclsão -->
<form id="deleteForm" method="get" action="{{ route('pilotos.delete') }}">
    <input type="hidden" name="_method" value="DELETE">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Confirmar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Deseja confirmar a exclusão do piloto?</p>
                </div>
                <input type="hidden" name="piloto_id" id="piloto_id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-danger">Deletar</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="{{asset('js/app.js')}}"></script>
<script src="{{asset('js/pilotos/index.js')}}"></script>

@endsection