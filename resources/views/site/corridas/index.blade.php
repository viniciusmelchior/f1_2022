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

  {{-- MELHORIA 20261110 (playoff): selo da fase de cada corrida --}}
  <style>
    .fase-po { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; font-style: normal; border: 1px solid transparent; }
    .fase-po.classificatoria { background-color: #eef0f3; color: #5c636a; border-color: #d3d6da; }
    .fase-po.oitavas { background-color: #e0f7f5; color: #087f74; border-color: #9fe3dc; }
    .fase-po.quartas { background-color: #c9f1ec; color: #066b62; border-color: #6fd6ca; }
    .fase-po.semi { background-color: #00d2be; color: #06332f; border-color: #00b3a2; }
    .fase-po.final { background-color: #f5c542; color: #3d2c00; border-color: #d9a91f; }
    .fase-po.fora, .fase-po.sprint { background: none; color: #8a8f96; border-color: #d3d6da; }
    /* MELHORIA 20261110: grade mais compacta para caber sem rolagem lateral em telas de notebook */
    #tabelaCorridas { font-size: .9rem; }
    #tabelaCorridas td, #tabelaCorridas th { padding: .45rem .4rem; }
    /* MELHORIA 20261110: botão "Ver chaveamento" na cor do playoff */
    .btn-chaveamento {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px; border-radius: 999px;
        background-color: #00d2be; color: #06332f !important; border: 1px solid #00b3a2;
        font-size: 0.78rem; font-weight: 700; letter-spacing: 0.3px; text-decoration: none !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        transition: background-color .15s, color .15s, box-shadow .15s, transform .15s;
    }
    .btn-chaveamento:hover, .btn-chaveamento:focus-visible {
        background-color: #15151e; color: #00d2be !important; border-color: #15151e;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2); transform: translateY(-1px); outline: none;
    }
    .btn-chaveamento .bi-arrow-right { transition: transform .15s; }
    .btn-chaveamento:hover .bi-arrow-right { transform: translateX(2px); }
    .resumo-po { display: inline-flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 10px; font-size: 0.85rem; color: #5c636a; }
  </style>

  {{-- MELHORIA 20261110: largura total (antes container fixo + tabela a 70% gerava rolagem lateral) --}}
  <div class="container-fluid px-4 mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Temporada {{$temporada->ano->ano}}@if($temporada->referencia) ({{$temporada->referencia}})@endif</li>
        </ol>
    </nav>
    
    @if(!empty($fasesPlayoff))
        <div class="resumo-po">
            <strong>Playoff:</strong>
            <span>{{ $temporada->playoffQtdClassificados() }} classificados · {{ $temporada->playoffQtdClassificatorias() }} {{ $temporada->playoffQtdClassificatorias() === 1 ? 'classificatória' : 'classificatórias' }} + {{ $temporada->playoffCorridasMataMata() }} de mata-mata ({{ $temporada->qtd_corridas }} corridas)</span>
            <a href="{{ route('temporadas.classificacao', [$temporada->id]) }}?aba=playoff" class="btn-chaveamento ms-1"><i class="bi bi-diagram-3"></i> Ver chaveamento <i class="bi bi-arrow-right"></i></a>
        </div>
    @endif

    <div class="table-responsive">
        @if(count($corridas) > 0)
        <table class="table align-middle" id="tabelaCorridas" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th>
                    @if(!empty($fasesPlayoff))<th style="text-align: left;">Fase</th>@endif
                    <th style="text-align: left;">Evento</th>
                    <th style="text-align: left;">Pista</th>
                    <th style="text-align: left;">Pole</th>
                    <th style="text-align: left;">Vencedor</th>
                    <th title="Abandonos">Aband.</th>
                    <th class="text-nowrap">Atualizado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($corridas as $key => $corrida)
                    <tr @if($corrida->flg_sprint == 'S') style="color:red; font-style: italic;" @endif>
                        {{-- <td>@if($corrida->flg_sprint != 'S') {{$corrida->ordem}} @endif</td> --}}
                        <td>{{$corrida->flg_sprint != 'S' ? $corrida->ordem : 'Sprint' }}</td>
                        @if(!empty($fasesPlayoff))
                            <td style="text-align: left;">
                                @if($corrida->flg_sprint == 'S')
                                    <span class="fase-po sprint" title="Sprints não contam para o playoff">Não conta</span>
                                @elseif(isset($fasesPlayoff[$corrida->id]))
                                    @php $fasePo = $fasesPlayoff[$corrida->id]; @endphp
                                    <span class="fase-po {{ $fasePo['chave'] }}">
                                        {{ $fasePo['nome'] }}@if($fasePo['n']) · {{ $fasePo['n'] }}/{{ $fasePo['total'] }}@endif
                                    </span>
                                @endif
                            </td>
                        @endif
                        <td style="text-align: left; min-width: 150px;">
                            @if (isset($corrida->evento))
                                <a href="{{route("eventos.show", [$corrida->evento->id])}}" style="color: inherit; text-decoration: inherit;">{{$corrida->evento->des_nome}}</a>
                            @else
                            -
                            @endif
                        </td>
                        <td style="text-align: left; min-width: 130px;">
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
                        @php $infoCorrida = Corrida::getInfoCorrida($corrida->id); @endphp
                        <td style="text-align: left;" class="text-nowrap">
                            @if($infoCorrida['polePositionEquipeImagem'])
                                <span style="width: 26px; height:26px;">
                                    <img src="{{asset('images/'.$infoCorrida['polePositionEquipeImagem'])}}" alt="" style="width: 26px; height:26px; padding: 3px; border-radius: 50%; object-fit: contain; vertical-align: middle; box-shadow: 0 0 0 1px rgba(0,0,0,.15); background-color: {{$infoCorrida['polePositionEquipeCor']}};">
                                </span>
                            @endif
                            <a href="{{route('pilotos.show', [$infoCorrida['polePositionId']])}}" style="color: inherit; text-decoration: inherit;">{{$infoCorrida['polePositionNome']}}</a>
                        </td>
                        <td style="text-align: left;" class="text-nowrap">
                            @if($infoCorrida['vencedorEquipeImagem'])
                                <span style="width: 26px; height:26px;">
                                    <img src="{{asset('images/'.$infoCorrida['vencedorEquipeImagem'])}}" alt="" style="width: 26px; height:26px; padding: 3px; border-radius: 50%; object-fit: contain; vertical-align: middle; box-shadow: 0 0 0 1px rgba(0,0,0,.15); background-color: {{$infoCorrida['vencedorEquipeCor']}};">
                                </span>
                            @endif
                            <a href="{{route('pilotos.show', [$infoCorrida['vencedorId']])}}" style="color: inherit; text-decoration: inherit;">{{$infoCorrida['vencedorNome']}}</a>
                        </td>
                        <td class="text-nowrap">{{$infoCorrida['totAbandonos']}}</td>
                        <td class="text-nowrap">
                            @if (isset($corrida->updated_at))
                                {{-- {{date('d/m/Y', strtotime($corrida->updated_at))}} às {{date('H:m:s', strtotime($corrida->updated_at))}} --}}
                                {{-- MELHORIA 20261110: só a data na grade (hora no tooltip) para caber sem rolagem lateral --}}
                                <span title="{{date('d/m/Y', strtotime($corrida->updated_at))}} às {{date('H:i:s', strtotime($corrida->updated_at))}}">{{date('d/m/Y', strtotime($corrida->updated_at))}}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <div class="d-inline-flex gap-1">
                                <a data-toggle="tooltip" data-placement="top" title="Visualizar resultados" class="btn btn-sm btn-outline-secondary" href="{{route('resultados.show', [$corrida->id])}}"><i class="bi bi-eye"></i></a>
                                {{-- MELHORIA 20261110 (resumo da corrida): manchetes, marcas históricas e curiosidades --}}
                                <a data-toggle="tooltip" data-placement="top" title="Resumo e estatísticas da corrida" class="btn btn-sm btn-outline-dark" href="{{route('corridas.resumo', [$corrida->id])}}"><i class="bi bi-newspaper"></i></a>
                                <a data-toggle="tooltip" data-placement="top" title="Inserir Resultados" class="btn btn-sm btn-outline-success" href="{{route('resultados.edit', [$corrida->id])}}"><i class="bi bi-clipboard-plus"></i></a>
                                <a data-toggle="tooltip" data-placement="top" title="Editar Corrida" class="btn btn-sm btn-outline-primary" href="{{route('corridas.edit', [$corrida->temporada->id,$corrida->id])}}"><i class="bi bi-pencil"></i></a>
                                <button type="button" data-toggle="tooltip" data-placement="top" title="Excluir Corrida" class="deleteCorrida btn btn-sm btn-outline-danger" value="{{$corrida->id}}"><i class="bi bi-trash"></i></button>
                            </div>
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

