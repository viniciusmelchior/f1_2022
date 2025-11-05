@php 
    use App\Models\Site\Temporada;
@endphp

@extends('layouts.main')

@section('section')

    @if (session('error'))
        <div class="alert alert-danger text-center">
            {{ session('error') }}
        </div>
    @endif

  <div class="container mt-3 mb-3">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Temporadas</li>
        </ol>
    </nav>

     <div class="card mt-3 mb-3 p-3">
        <div class="card-body">
        <label for="">Pesquisar</label>
        <input type="text" id="caixaBusca">
        </div>
    </div>

    <div class="table-responsive">
        @if(count($temporadas) > 0)
        <table class="table" id="tabelaTemporadas">
            <thead>
                <tr>
                    <th>#</th>
                    <th onclick="sortTable(1)" style="cursor: pointer;">Ano</th>
                    <th>Descrição</th>
                    <th onclick="sortTable(3)" style="cursor: pointer;">Referência</th>
                    <th style="width: 20%; text-align:left;">Camp. Piloto</th>
                    <th style="width: 15%; text-align:left;">Camp. Construtores</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($temporadas as $key => $temporada)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$temporada->ano->ano}}</td>
                        <td>{{$temporada->des_temporada}}
                            @isset($temporada->observacoes)
                                <i class="bi bi-info-circle text-primary" data-toggle="tooltip" data-placement="top" title="{{$temporada->observacoes}}">  
                            @endisset
                        </td>
                        <td>
                            {{$temporada->referencia ?? '-'}}
                        </td>
                        <td style="width: 15%; text-align:left;">
                            @if(isset($temporada->titulo))
                               @php $imagemPiloto = $temporada->titulo->pilotoEquipe->equipe->imagem; @endphp
                                <img src="{{asset('images/'.$imagemPiloto)}}" alt="" style="width: 25px; height:25px;">
                                {{$temporada->titulo->pilotoEquipe->piloto->nomeCompleto()}}
                            @else
                                -
                            @endif
                        </td>
                        <td style="width: 15%; text-align:left;">
                            @if(isset($temporada->titulo))
                            <img src="{{asset('images/'.$temporada->titulo->equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                {{$temporada->titulo->equipe->nome}}
                            @else
                                -
                            @endif
                        </td>
                        <td>@if($temporada->flg_finalizada == 'S')<i class="bi bi-check-square-fill"></i>@else Em Andamento @endif</td>
                        <td class="d-flex" style="justify-content: space-between;">
                            <a data-toggle="tooltip" data-placement="top" title="Classificação" href="{{route('temporadas.classificacao', [$temporada->id])}}"><i class="bi bi-table"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Resultados (posições)" href="{{route('temporadas.resultados', [$temporada->id])}}"><i class="bi bi-bar-chart-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Resultados (pontuação)" href="{{route('temporadas.resultados', [$temporada->id, 'porPontuacao'])}}"><i class="bi bi-bar-chart-fill text-warning"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Editar Temporada" href="{{route('temporadas.edit', [$temporada->id])}}"><i class="bi bi-pencil-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Visualizar Corridas" href="{{route('corridas.index', [$temporada->id])}}"><i class="bi bi-eye-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Adicionar Corridas" class="" href="{{route('corridas.adicionar', [$temporada->id])}}"><i class="bi bi-plus-circle-fill"></i></a>
                            <a data-toggle="tooltip" data-placement="top" title="Deletar" class="" href="{{route('temporadas.delete', [$temporada->id])}}"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p>Nenhuma temporada cadastrada</p>
        @endif
    </div>
    <a href="{{route('temporadas.create')}}" class="btn btn-dark">Adicionar temporada</a>
    <a href="{{route('dashboard')}}" class="btn btn-danger ml-3">Voltar</a>
  </div>

  <script>
    let caixaBusca = document.getElementById('caixaBusca');
    let tabelaTemporadas = document.getElementById('tabelaTemporadas');

    caixaBusca.addEventListener("keyup",function(){
        var keyword = this.value;
        keyword = keyword.toUpperCase();
        
        var all_tr = tabelaTemporadas.getElementsByTagName("tr");

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

    function sortTable(n) {
        var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
        table = document.getElementById("tabelaTemporadas");
        switching = true;
        // Define a direção de ordenação inicial
        dir = "asc"; 
        // Realiza o loop até que nenhuma troca seja feita
        while (switching) {
            switching = false;
            rows = table.rows;
            // Loop por todas as linhas da tabela (exceto o cabeçalho)
            for (i = 1; i < (rows.length - 1); i++) {
                shouldSwitch = false;
                // Obtém os dois elementos que serão comparados
                x = rows[i].getElementsByTagName("TD")[n];
                y = rows[i + 1].getElementsByTagName("TD")[n];
                // Verifica se as duas linhas devem ser trocadas de acordo com a direção ascendente ou descendente
                if (dir == "asc") {
                    if (Number(x.innerHTML) > Number(y.innerHTML)) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (dir == "desc") {
                    if (Number(x.innerHTML) < Number(y.innerHTML)) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }
            if (shouldSwitch) {
                // Se uma troca deve ser feita, realiza a troca e marca que uma troca foi feita
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                // Cada vez que uma troca é feita, incrementa a contagem de trocas
                switchcount++; 
            } else {
                // Se nenhuma troca foi feita e a direção é "asc", define a direção como "desc" e reinicia o loop
                if (switchcount == 0 && dir == "asc") {
                    dir = "desc";
                    switching = true;
                }
            }
        }
    }

</script>
@endsection