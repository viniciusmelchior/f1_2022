@php 
    use App\Models\Site\Pista;
@endphp

@extends('layouts.main')

@section('section')

    @if (session('error'))
    <div class="alert alert-danger text-center">
        {{ session('error') }}
    </div>
    @endif

  <div class="container-fluid mt-3 mb-3">
    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listagem de Pistas</li>
        </ol>
    </nav>

    <div class="card mt-3 mb-3 p-3">
        <div class="card-body">
        <label for="">Pesquisar</label>
        <input type="text" id="caixaBusca">
        </div>
    </div>

    <div>
        <button class="btn btn-secondary my-3 adicionar_autor" id="adicionar_autor">Adicionar Autor</button>
    </div>

    @if(count($pistas) > 0)
    <div class="table">
        <table class="table" id="tabelaPilotos">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="text-align: left;">Nome</th>
                    <th  style="text-align: left;">País</th>
                    <th style="text-align: left;">Continente</th>
                    <th>Qtd Carros</th>
                    <th>Corridas</th>
                    <th>Sprints</th>
                    {{-- <th>Tamanho</th> --}}
                    <th>Autor</th>
                    <th>DRS</th>
                    <th>Tipo</th>
                    {{-- 
                    <th>RARE</th>
                    <th>Pit Stop</th> --}}
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pistas as $key => $pista)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td style="text-align: left;">
                            <span style="width: 30px; height:20px;">
                                <img src="{{asset('images/'.$pista->pais->imagem)}}" alt="" srcset="" style="width: 30px; height:20px;">
                            </span>
                            {{$pista->nome}}
                        </td>
                        <td style="text-align: left;">{{$pista->pais->des_nome}}</td>
                        <td style="text-align: left;">{{isset($pista->pais->continente->nome) ? $pista->pais->continente->nome : '-' }}</td>
                        <td>{{$pista->qtd_carros != null ? $pista->qtd_carros : '30'}}</td>
                        <td>
                            @php 
                                echo Pista::getQtdCorridas($pista->id) > 0 ? Pista::getQtdCorridas($pista->id) : '-' ;
                            @endphp
                        </td>
                        <td>
                            @php 
                                echo Pista::getQtdCorridasSprints($pista->id) > 0 ? Pista::getQtdCorridasSprints($pista->id) : '-' ;
                            @endphp
                        </td>
                        {{-- <td>{{$pista->tamanho_km != null ? $pista->tamanho_km : '4100'}}</td> --}}
                        <td>
                            {{ $pista->autor != null ? $pista->autor->nome : '-' }}
                        </td>
                        <td>
                            {{ $pista->drs != null ? $pista->drs : '-' }}
                        </td>
                        <td>
                            {{ $pista->tipo != null ? $pista->tipo : '-' }}
                        </td>
                         {{--
                        <td>Não</td>
                        <td>Sim</td> --}}
                        
                        <td class="d-flex" style="justify-content: space-between;">
                            <a data-toggle="tooltip" data-placement="top" title="Visualizar" class="" href="{{route('pistas.show', [$pista->id])}}"><i class="bi bi-eye-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Editar" class="" href="{{route('pistas.edit', [$pista->id])}}"><i class="bi bi-pencil-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="excluir" class="" href="{{route('pistas.delete', [$pista->id])}}"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <p>Nenhuma pista cadastrada</p>
    @endif
    <a href="{{route('pistas.create')}}" class="btn btn-primary">Adicionar pista</a>
    <a href="{{route('dashboard')}}" class="btn btn-secondary">Voltar</a>
  </div>

    <!-- Modal exclsão -->
<form id="deleteForm" method="get" action="{{ route('pistas.adicionarAutor') }}">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Adicionar Autor</h5>
                </div>
                <div class="modal-body">
                    <input type="text" name="autor" id="autor" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </div>
    </div>
</form>

  <script>
    let caixaBusca = document.getElementById('caixaBusca');
    let tabelaPilotos = document.getElementById('tabelaPilotos');

    caixaBusca.addEventListener("keyup",function(){
    var keyword = this.value;
    keyword = keyword.toUpperCase();
    
    var all_tr = tabelaPilotos.getElementsByTagName("tr");

    for(var i=0; i<all_tr.length; i++){
        var all_columns = all_tr[i].getElementsByTagName("td");
        for(j=0;j<all_columns.length; j++){
            if(all_columns[j]){
                var column_value = all_columns[j].textContent || all_columns[j].innerText;
                column_value = column_value.toUpperCase();
                if(column_value.indexOf(keyword) > -1){
                    all_tr[i].style.display = ""; // show
                    break;
                }else{
                    all_tr[i].style.display = "none"; // hide
                }
            }
        }
    }
    })

    $(document).ready(function () {
        $('.adicionar_autor').click(function (e) { 
            e.preventDefault();
            $('#exampleModal').modal('show');
        });
    });

</script>
@endsection