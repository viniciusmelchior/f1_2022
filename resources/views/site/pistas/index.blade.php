@extends('layouts.main')

@section('section')
  <div class="container mt-3 mb-3">
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

    @if(count($pistas) > 0)
    <div class="table">
        <table class="table" id="tabelaPilotos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>País</th>
                    <th>Qtd Carros</th>
                    <th>Tamanho</th>
                    <th>Qtd Voltas</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pistas as $key => $pista)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$pista->nome}}</td>
                        <td>{{$pista->pais->des_nome}}</td>
                        <td>{{$pista->qtd_carros != null ? $pista->qtd_carros : '-'}}</td>
                        <td>{{$pista->tamanho_km != null ? $pista->tamanho_km : '-'}}</td>
                        <td>{{$pista->qtd_voltas != null ? $pista->qtd_voltas : '-'}}</td>
                        <td>{{$pista->flg_ativo}}</td>
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

   /*  $(document).ready(function () {
        $('.deletePiloto').click(function (e) { 
            e.preventDefault();
            var piloto_id = $(this).val();
            $('#piloto_id').val(piloto_id);
            $('#exampleModal').modal('show');
        });
    }); */

</script>
@endsection