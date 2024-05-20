@php 
    use App\Models\Site\Pais;
    use App\Models\Site\Evento;
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
            <li class="breadcrumb-item active" aria-current="page">Listagem de Eventos</li>
        </ol>
    </nav>

    <div class="card mt-3 mb-3 p-3">
        <div class="card-body">
        <label for="">Pesquisar</label>
        <input type="text" id="caixaBusca">
        </div>
    </div>

    <div class="left_table">
        @if(count($eventos) > 0)
            <table class="table" style="width:80%;" id="tabelaEventos">
                <thead>
                    <tr>
                        <th class="text-nowrap">#</th>
                        <th style="text-align: left;">Nome</th>
                        <th style="text-align: left;">Ultimo Evento</th>
                        <th>Quantidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($eventos as $key => $evento)
                        <tr>
                            <td class="text-nowrap">{{$key+1}}</td>
                            <td style="text-align: left;" class="text-nowrap">
                                {{$evento->des_nome}}
                            </td>
                            <td style="text-align: left;">
                                {{Evento::getUltimoEvento($evento->id)['ultimoEvento']}}
                            </td>
                            {{-- <td style="text-align: center;">
                                @php 
                                    echo Pais::getQtdCorridas($pais->id) > 0 ? Pais::getQtdCorridas($pais->id) : '-' ;
                                @endphp
                            </td> --}}
                            <td style="text-align: center;">
                                @php 
                                    echo Evento::getQtdEventos($evento->id) > 0 ? Evento::getQtdEventos($evento->id) : '-' ;
                                @endphp
                            </td>
                            <td class="coluna_acoes">
                                <a href="{{route('eventos.show', [$evento->id])}}"><i class="bi bi-eye-fill"></i></a>
                                <a href="{{route('eventos.edit', [$evento->id])}}"><i class="bi bi-pencil-fill"></i></a>
                                <button type="button" class="deleteEvento btn btn-link p-0" value="{{$evento->id}}"><i class="bi bi-trash-fill"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum evento cadastrado</p>
        @endif
    </div>

    <a href="{{route('eventos.create')}}" class="btn btn-primary">Adicionar Evento</a>
  </div>

    <!-- Modal exclsão -->
<form id="deleteForm" method="get" action="{{ route('eventos.delete') }}">
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
                    <p>Deseja confirmar a exclusão do evento?</p>
                </div>
                <input type="hidden" name="evento_id" id="evento_id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-danger">Deletar</button>
                </div>
            </div>
        </div>
    </div>
</form>

  <script>
    let caixaBusca = document.getElementById('caixaBusca');
    let tabelaEventos = document.getElementById('tabelaEventos');

    caixaBusca.addEventListener("keyup",function(){
    var keyword = this.value;
    keyword = keyword.toUpperCase();
    
    var all_tr = tabelaEventos.getElementsByTagName("tr");

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

    //modal para excluir o país
    $(document).ready(function () {
        $('.deleteEvento').click(function (e) { 
            e.preventDefault();
            var evento_id = $(this).val();
            $('#evento_id').val(evento_id);
            $('#exampleModal').modal('show');
        });
    });

</script>  


@endsection