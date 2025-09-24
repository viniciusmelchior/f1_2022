@php 
    use App\Models\Site\Corrida;
@endphp

@extends('layouts.main')

@section('section')
    @if (session('status'))
        <div class="alert alert-success text-center">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger text-center">
            {{ session('error') }}
        </div>
    @endif

  <div class="container mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Temporada {{$temporada->ano->ano}}</li>
        </ol>
    </nav>
    
    <div class="table">
        @if(count($corridas) > 0)
        <table class="table" id="tabelaCorridas" style="width:70%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="text-align: left;">Evento</th>
                    <th style="text-align: left;">Pista</th>
                    <th>Pole</th>
                    <th>Vencedor</th>
                    <th>Abandonos</th>
                    <th class="text-nowrap">Atualizado Em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($corridas as $key => $corrida)
                    <tr @if($corrida->flg_sprint == 'S') style="color:red; font-style: italic;" @endif>
                        {{-- <td>@if($corrida->flg_sprint != 'S') {{$corrida->ordem}} @endif</td> --}}
                        <td>{{$corrida->flg_sprint != 'S' ? $corrida->ordem : 'Sprint' }}</td>
                        <td style="text-align: left;" class="text-nowrap">
                            @if (isset($corrida->evento))
                                <a href="{{route("eventos.show", [$corrida->evento->id])}}" style="color: inherit; text-decoration: inherit;">{{$corrida->evento->des_nome}}</a>
                            @else
                            -
                            @endif
                        </td>
                        <td style="text-align: left;" class="text-nowrap">
                            <span style="width: 30px; height:20px;">
                                <img src="{{asset('images/'.$corrida->pista->pais->imagem)}}" alt="" srcset="" style="width: 30px; height:20px;">
                            </span>
                            <a href="{{route("pistas.show", [$corrida->pista->id])}}" style="color: inherit; text-decoration: inherit;">{{$corrida->pista->nome}}</a>
                           {{--  @if($corrida->flg_super_corrida == 'S')
                                - Super Corrida
                            @endif --}}
                        </td>
                        {{-- <td>
                            {{$corrida->pista->qtd_voltas != null ? $corrida->pista->qtd_voltas : '-'}}
                        </td> --}}
                        {{-- <td>{{$corrida->dificuldade_ia}}</td> --}}
                        {{-- {{dd(Corrida::getInfoCorrida($corrida->id))}} --}}
                        <td class="text-nowrap"><a href="{{route('pilotos.show', [Corrida::getInfoCorrida($corrida->id)['polePositionId']])}}" style="color: inherit; text-decoration: inherit;">{{Corrida::getInfoCorrida($corrida->id)['polePositionNome']}}</a></td>
                        <td class="text-nowrap"><a href="{{route('pilotos.show', [Corrida::getInfoCorrida($corrida->id)['vencedorId']])}}" style="color: inherit; text-decoration: inherit;">{{Corrida::getInfoCorrida($corrida->id)['vencedorNome']}}</a></td>
                        <td class="text-nowrap">{{Corrida::getInfoCorrida($corrida->id)['totAbandonos']}}</td>
                        <td class="text-nowrap">
                            @if (isset($corrida->updated_at))
                                {{-- {{date('d/m/Y', strtotime($corrida->updated_at))}} às {{date('H:m:s', strtotime($corrida->updated_at))}} --}}
                                {{date('d/m/Y', strtotime($corrida->updated_at))}} às {{date('H:i:s', strtotime($corrida->updated_at))}}
                            @else
                                -
                            @endif
                        </td>
                        <td class="d-flex" style="justify-content: space-around;">
                            <a data-toggle="tooltip" data-placement="top" title="Visualizar resultados" href="{{route('resultados.show', [$corrida->id])}}"><i class="bi bi-eye-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Inserir Resultados" class="" href="{{route('resultados.edit', [$corrida->id])}}"><i class="bi bi-plus-circle-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Editar Corrida" class="" href="{{route('corridas.edit', [$corrida->temporada->id,$corrida->id])}}"><i class="bi bi-pencil-fill"></i></a>
                            <button type="button" class="deleteCorrida btn btn-link p-0" value="{{$corrida->id}}"><i class="bi bi-trash-fill"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p>Nenhuma corrida cadastrada</p>
        @endif
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" name="observacoes" id="observacoes" rows="10" readonly>
                @if($temporada->observacoes)
                    {{$temporada->observacoes}}
                @endif
            </textarea>
        </div>
    </div>
    <a href="{{route('corridas.adicionar', [$temporada->id])}}" class="btn btn-primary">Adicionar Corridas</a>
    <a href="{{route('temporadas.index')}}" class="btn btn-secondary ml-3">Voltar</a>
  </div>

 <!-- Modal exclsão de Corridas -->
 <form id="deleteForm" method="get" action="{{ route('corridas.delete') }}">
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
                    <p>Deseja confirmar a exclusão da corrida?</p>
                </div>
                <input type="hidden" name="corrida_id" id="corrida_id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Não</button>
                    <button type="submit" class="btn btn-danger">Sim</button>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function () {
       $('.deleteCorrida').click(function (e) { 
           e.preventDefault();
           var corrida_id = $(this).val();
           $('#corrida_id').val(corrida_id);
           $('#exampleModal').modal('show');
       });
   });
</script>
@endsection

