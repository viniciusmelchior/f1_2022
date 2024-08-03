@extends('layouts.main')

@section('section')
    @if (session('status'))
        <div class="alert alert-success text-center">
            {{ session('status') }}
        </div>
    @endif
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
                <th>Carro</th>
                <th>Skin</th>
                <th>Status</th>
                <th>ações</th>
            </tr>
        </thead>
        <tbody id="">
            @foreach ($pilotoEquipes as $key => $pilotoEquipe)
                <tr style="color:{{$pilotoEquipe->equipe->des_cor}};">
                    <td>{{$key+1}}</td>
                    <td>{{$pilotoEquipe->ano->ano}}</td>
                    <td  class="text-nowrap">{{$pilotoEquipe->piloto->nome}} {{$pilotoEquipe->piloto->sobrenome}}</td>
                    <td  class="text-nowrap">{{$pilotoEquipe->equipe->nome}}</td>
                    <td>{{$pilotoEquipe->modelo_carro == null ? '-': $pilotoEquipe->modelo_carro}}</td>
                    <td class="text-nowrap">
                        {{isset($pilotoEquipe->skin) ? $pilotoEquipe->skin->skin : '-'}}
                    </td>
                    <td>{{$pilotoEquipe->flg_ativo}}</td>
                    <td class="d-flex space-around">
                        <a href="{{route('pilotoEquipe.edit', [$pilotoEquipe->id])}}"><i class="bi bi-pencil-fill"></i></a>
                        <button
                            type="button"
                            class="replicarDupla btn btn-link p-0"
                            value="{{$pilotoEquipe->id}}"
                            data-nomePiloto="{{$pilotoEquipe->piloto->nomeCompleto()}}"
                            data-nomeEquipe="{{$pilotoEquipe->equipe->nome}}"
                            >
                            <i class="bi bi-plus-circle-fill"></i>
                        </button>
                        <a class="" href="{{route('pilotoEquipe.delete', [$pilotoEquipe->id])}}"><i class="bi bi-trash-fill"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>Nenhum dado cadastrado</p>
    @endif
    <div class="left-table-pagination-wrapper">
        <div id="pagination"></div>
    </div>
    <a href="{{route('pilotoEquipe.create')}}" class="btn btn-primary">Adicionar</a>
    <a href="{{route('dashboard')}}" class="btn btn-secondary ml-3">Voltar</a>
  </div>

 <!-- Modal replicar dupla de pilotos -->
 <form id="replicarPilotoEquipe" method="POST" action="{{route('pilotoEquipe.replicarPilotoEquipe')}}">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Replicar: <span id="nomePiloto"></span> / <span id="nomeEquipe"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- <p>Selecione a temporada</p> --}}
                    <div class="mb-3">
                        <label for="ano_id" class="form-label">Temporada/Ano</label>
                        <select name="ano_id" id="selectReplicarPiloto" class="form-control" required>
                            @foreach($anos as $ano)
                                <option value="{{$ano->id}}" @if(isset($model) && $model->ano_id == $ano->id) selected @endif>{{$ano->ano}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="pilotoEquipe_id" id="pilotoEquipe_id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="{{asset('js/pilotoEquipes/index.js')}}"></script>

<script>
      $(document).ready(function () {
        $('.replicarDupla').click(function (e) { 
            e.preventDefault();
            var nomePiloto = $(this).get(0).dataset.nomepiloto; //nome do piloto colocado na linha tabela
            var nomeEquipe = $(this).get(0).dataset.nomeequipe; //nome da equipe colocado na linha tabela

            //atribui o nome do piloto e da equipe nos spans de identificação dos dados do modal
            $('#nomePiloto').text(nomePiloto);
            $('#nomeEquipe').text(nomeEquipe);

            var pilotoEquipe_id = $(this).val();
            $('#pilotoEquipe_id').val(pilotoEquipe_id);
            $('#exampleModal').modal('show');
        });
    });
</script>

@endsection