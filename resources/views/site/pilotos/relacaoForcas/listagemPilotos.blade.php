@php 
    use App\Models\Site\Resultado;
    use App\Models\Site\ForcaPiloto;
@endphp

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
            <li class="breadcrumb-item active" aria-current="page">Relação de Forças</li>
            <li class="breadcrumb-item active" aria-current="page">Listagem de Pilotos</li>
        </ol>
    </nav>

    <div class="card mt-3 mb-3 p-3">
        <div class="card-body">
        <label for="">Pesquisar</label>
        <input type="text" id="caixaBusca">
        </div>
    </div>

    <div class="left_table">
        @if(count($pilotos) > 0)
            <table class="table" id="tabelaPilotos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Ballast</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pilotos as $key => $piloto)
                        <tr style="<?= $piloto->flg_ativo == 'N' ? "color:red;" : "" ?>">
                            <td>{{$key+1}}</td>
                            <td>{{$piloto->piloto->nome}} {{$piloto->piloto->sobrenome}}</td>
                            <td>
                                {{ForcaPiloto::getForcaPiloto($temporada->ano->id, $piloto->piloto->id)}}
                            </td>
                            <td class="coluna_acoes">
                                <a href="{{route('pilotos.relacaoForcas.edit', [$temporada->ano->id, $piloto->piloto->id])}}"><i class="bi bi-pencil-fill"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum piloto cadastrado</p>
        @endif
    </div>
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

    $(document).ready(function () {
        $('.deletePiloto').click(function (e) { 
            e.preventDefault();
            var piloto_id = $(this).val();
            $('#piloto_id').val(piloto_id);
            $('#exampleModal').modal('show');
        });
    });

</script>

@endsection
