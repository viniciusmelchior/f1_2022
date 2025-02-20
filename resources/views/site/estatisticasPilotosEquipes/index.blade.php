@php 
    use App\Models\Site\Resultado;
@endphp

@extends('layouts.main')

@section('section')

<style>
    .header-tabelas{
        padding: 15px;
        background-color: rgba(194, 26, 26, 0.993);
        text-align: center;
        font-size: 25px;
        font-weight: bolder;
        color: white;
    }

    ul li{
        list-style: none;
    }

</style>

    <div class="container mt-3 mb-3">

        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Estatisticas dos pilotos e equipes</li>
            </ol>
        </nav>

        {{--formulário inicial--}}

        {{-- <form action="" method="POST"> --}}
            {{--tipo de consulta (se é por largada ou chegada)--}}
            <div class="row m-1 mb-3">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label for="tipoConsulta" class="col-sm-2 col-form-label">Tipo</label>
                        <div class="col-sm-10">
                            <select name="tipoConsulta" id="tipoConsulta" class="form-control">
                                <option value="chegada">Chegada</option>
                                <option value="largada">Largada</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{--filtro de consulta por temporada--}}
            <div class="row m-1 mb-3">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label for="temporada" class="col-sm-2 col-form-label">Temporada</label>
                        <div class="col-sm-10">
                            <select name="temporada" id="temporada" class="form-control">
                                <option value="">Todas</option>
                                @foreach($temporadas as $temporada)
                                    <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{--pilotos que devem ser ignorados na busca--}}
            <div class="row m-1 mb-3">
                <div class="col-md-6">
                    <div class="form-group row d-flex align-items-center">
                        <label for="Pilotos" class="col-sm-2 col-form-label">Pilotos Ignorados</label>
                        <div class="col-sm-10">
                            <select name="pilotos_ignorados[]" id="pilotos_ignorados" class="form-select" multiple style="height: auto">
                                <option value="0" onclick="limpaCamposPilotos()">Nenhum</option>
                                @foreach($pilotos as $piloto)
                                    <option value="{{$piloto->id}}">{{$piloto->nomeCompleto()}}</option>
                                 @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{--equipes que devem ser ignoradas na busca--}}
            <div class="row m-1 mb-3">
                <div class="col-md-6">
                    <div class="form-group row d-flex align-items-center">
                        <label for="equipes_ignoradas" class="col-sm-2 col-form-label">Equipes Ignorados</label>
                        <div class="col-sm-10">
                            <select name="equipes_ignoradas[]" id="equipes_ignoradas" class="form-select" multiple style="height: auto">
                                <option value="0" onclick="limpaCamposEquipes()">Nenhuma</option>
                                @foreach($equipes as $equipe)
                                    <option value="{{$equipe->id}}">{{$equipe->nome}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            

            {{--limite de resultados a buscar (top do SQL)--}}
            <div class="row m-1 mb-3">
                <div class="col-md-6">
                    <div class="form-group row d-flex align-items-center">
                        <label for="limite" class="col-sm-2 col-form-label">Posição Máxima</label>
                        <div class="col-sm-2">
                            <input type="number" value="1" min="1" name="limite" id="limite" class="form-control">
                        </div>
                    </div>
                </div>
            </div>  
            
            <button class="btn btn-primary m-3" id="botaoPesquisa" onclick="buscarDados()">Pesquisar</button>

        {{-- </form> --}}
        
        <hr class="separador">
        
        <div class="header-tabelas m-3">
            <span id="descricaoPagina">Pilotos</span> <span id="toggle_pilotos"><i class="bi bi-plus-circle" id="icon_pilotos"></i></span>
        </div>

        <div class="d-none" id="div_pilotos">
            {{-- <div class="accordion m-3" id="accordionExample2">
                <div class="">
                  <div class="" id="headingOne">
                    <h5 class="mb-0">
                      <p class="" type="button" data-toggle="collapse" data-target="#collapseOne2" aria-expanded="false" aria-controls="collapseOne2">
                        <span>1. </span> Verstappen => <span class="text-bold">150</span>
                      </p>
                    </h5>
                  </div>
                    <div id="collapseOne2" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample2">
                        <div class="d-flex">
                            <div class="card m-3 w-100">
                            <div class="card-header text-center" id="headingOne">
                                Equipes
                            </div>
                            <div class="card-body">
                                 <ul>
                                    <li><span>1.</span> GP do Brasil => <span>8</span></li>
                                    <li><span>1.</span> GP do Brasil => <span>8</span></li>
                                    <li><span>1.</span> GP do Brasil => <span>8</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card m-3 w-100">
                            <div class="card-header text-center" id="headingOne">
                                Eventos
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><span>1.</span> GP do Brasil => <span>8</span></li>
                                    <li><span>1.</span> GP do Brasil => <span>8</span></li>
                                    <li><span>1.</span> GP do Brasil => <span>8</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card m-3 w-100">
                            <div class="card-header text-center" id="headingOne">
                                Pistas
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><span>1.</span> GP do Brasil => <span>8</span></li>
                                    <li><span>1.</span> GP do Brasil => <span>8</span></li>
                                    <li><span>1.</span> GP do Brasil => <span>8</span></li>
                                </ul>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>

        <hr class="separador">

        <div class="header-tabelas m-3">
            Equipes <span id="toggle_equipes"><i class="bi bi-plus-circle" id="icon_equipes"></i></span>
        </div>

        <div class="d-none" id="div_equipes">
           {{--preenchido via javascript--}}
        </div>
    </div>
    
    <input type="hidden" name="url_busca" id="url_busca" value="{{route('estatisticas.pilotos.equipes.buscar')}}">

    <script>
        window.onload = function() {
            $('#toggle_pilotos').click(function (e) { 
                e.preventDefault();
                $('#div_pilotos').toggleClass('d-none');
    
                var icon = $('#icon_pilotos');
                if (icon.hasClass('bi-plus-circle')) {
                    icon.removeClass('bi-plus-circle').addClass('bi-dash-circle');
                } else {
                    icon.removeClass('bi-dash-circle').addClass('bi-plus-circle');
                }
            });
    
            $('#toggle_equipes').click(function (e) { 
                e.preventDefault();
                $('#div_equipes').toggleClass('d-none');
    
                var icon = $('#icon_equipes');
                if (icon.hasClass('bi-plus-circle')) {
                    icon.removeClass('bi-plus-circle').addClass('bi-dash-circle');
                } else {
                    icon.removeClass('bi-dash-circle').addClass('bi-plus-circle');
                }
            });
        }

        function limpaCamposPilotos() {
            let select = document.getElementById("pilotos_ignorados");
            let nenhumaOption = document.getElementById("nenhuma");

            // Se "Nenhuma" for selecionada, desmarcar todas as outras opções
            if (nenhumaOption.selected) {
                for (let option of select.options) {
                    if (option !== nenhumaOption) {
                        option.selected = false;
                    }
                }
            }
        }

        document.getElementById("pilotos_ignorados").addEventListener("change", function () {
            let select = this;
            let nenhumaOption = document.getElementById("nenhuma");

            // Se qualquer outra opção for selecionada, desmarcar "Nenhuma"
            if (nenhumaOption.selected && select.selectedOptions.length > 1) {
                nenhumaOption.selected = false;
            }
        });
        

        function limpaCamposEquipes() {
            let select = document.getElementById("equipes_ignoradas");
            let nenhumaOption = document.getElementById("nenhuma");

            // Se "Nenhuma" for selecionada, desmarcar todas as outras opções
            if (nenhumaOption.selected) {
                for (let option of select.options) {
                    if (option !== nenhumaOption) {
                        option.selected = false;
                    }
                }
            }
        }

        document.getElementById("equipes_ignoradas").addEventListener("change", function () {
            let select = this;
            let nenhumaOption = document.getElementById("nenhuma");

            // Se qualquer outra opção for selecionada, desmarcar "Nenhuma"
            if (nenhumaOption.selected && select.selectedOptions.length > 1) {
                nenhumaOption.selected = false;
            }
        });

        //Função que busca os dados da tela ao clicar no botão de pesquisa 

        async function buscarDados(){

            //coleta os dados
            let tipo = document.getElementById('tipoConsulta').value
            let temporada = document.getElementById('temporada').value
            let selectPilotosIgnorados = document.getElementById('pilotos_ignorados');
            let pilotos_ignorados = Array.from(selectPilotosIgnorados.selectedOptions).map(option => option.value);
            let selectEquipesIgnoradas = document.getElementById('equipes_ignoradas');
            let equipes_ignoradas = Array.from(selectEquipesIgnoradas.selectedOptions).map(option => option.value);
            let limite =  document.getElementById('limite').value
            let descricaoPagina = document.getElementById('descricaoPagina')
            

            // console.log(tipo, temporada, pilotos_ignorados, equipes_ignoradas, limite)

            //fazer a requisição no backend
            const token = document.querySelector('meta[name="csrf-token"]').content
            const url = document.getElementById('url_busca').value;

            const req = await fetch(url, {
                method: 'POST',
                headers: {
                    'content-type' : 'application/json',
                    'x-csrf-token' : token
                },
                body: JSON.stringify({
                    tipo: tipo,
                    temporada: temporada,
                    pilotos_ignorados: pilotos_ignorados,
                    equipes_ignoradas: equipes_ignoradas,
                    limite: limite
                })
            })

            const res = await req.json();
                descricaoPagina.innerText = res.descricaoPagina
                criarTabelaEstatisticasPilotos(res)
                criarTabelaEstatisticasEquipes(res)
        }

        function criarTabelaEstatisticasPilotos(res){
            divPilotos = document.getElementById('div_pilotos');
            divPilotos.innerHTML = '';

            res.dadosPiloto.forEach((dadoPiloto,key) => {
                divPilotos.innerHTML += `<div class="accordion m-3" id="accordionExample_${key+1}">
                <div class="">
                  <div class="" id="headingOne_${key+1}">
                    <h5 class="mb-0">
                      <p class="" type="button" data-toggle="collapse" data-target="#collapseOne_${key+1}" aria-expanded="false" aria-controls="collapseOne_${key+1}">
                        <span>${key+1}. </span> ${dadoPiloto.nome_piloto} => <span class="text-bold">${dadoPiloto.quantidade}</span>
                      </p>
                    </h5>
                  </div>
                    <div id="collapseOne_${key+1}" class="collapse" aria-labelledby="headingOne_${key+1}" data-parent="#accordionExample_${key+1}">
                        <div class="d-flex">
                            <div class="card m-3 w-100">
                            <div class="card-header text-center" id="headingOne_${key+1}">
                                Equipes
                            </div>
                            <div class="card-body">
                                ${
                                    dadoPiloto.equipes.map((equipe, keyEquipe) => `
                                       <ul>
                                            <li><span>${keyEquipe+1}.</span> ${equipe.equipe_nome} <span> => ${equipe.quantidade}</span></li>
                                        </ul>
                                    `).join('')
                                }
                            </div>
                        </div>
                        <div class="card m-3 w-100">
                            <div class="card-header text-center" id="headingOne_${key+1}">
                                Eventos
                            </div>
                            <div class="card-body text-left">
                                ${
                                    dadoPiloto.eventos.map((evento, keyEvento) => `
                                       <ul>
                                            <li><span>${keyEvento+1}.</span> ${evento.evento} <span> => ${evento.quantidade}</span></li>
                                        </ul>
                                    `).join('')
                                }
                            </div>
                        </div>
                        <div class="card m-3 w-100">
                            <div class="card-header text-center" id="headingOne_${key+1}">
                                Pistas
                            </div>
                            <div class="card-body">
                                ${
                                    dadoPiloto.pistas.map((pista, keyPista) => `
                                       <ul>
                                            <li><span>${keyPista+1}.</span> ${pista.pista} <span> => ${pista.quantidade}</span></li>
                                        </ul>
                                    `).join('')
                                }
                            </div>
                        </div>
                        <div class="card m-3 w-100">
                            <div class="card-header text-center" id="headingOne_${key+1}">
                                Países
                            </div>
                            <div class="card-body">
                                ${
                                    dadoPiloto.paises.map((pais, keyPais) => `
                                       <ul>
                                            <li><span>${keyPais+1}.</span> ${pais.pais} <span> => ${pais.quantidade}</span></li>
                                        </ul>
                                    `).join('')
                                }
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>`;
            });
        }

        function criarTabelaEstatisticasEquipes(res){
            divEquipes = document.getElementById('div_equipes');
            divEquipes.innerHTML = '';
            console.log(res.dadosEquipe)

            res.dadosEquipe.forEach((dadoEquipe,key) => {
                divEquipes.innerHTML += `<div class="accordion m-3" id="accordionExample_equipes_${key+1}">
                <div class="">
                  <div class="" id="headingOne_equipes_${key+1}">
                    <h5 class="mb-0">
                      <p class="" type="button" data-toggle="collapse" data-target="#collapseOne_equipes_${key+1}" aria-expanded="false" aria-controls="collapseOne_equipes_${key+1}">
                         <span>${key+1}. </span> ${dadoEquipe.equipe_nome} => <span class="text-bold">${dadoEquipe.quantidade_equipe}</span>
                      </p>
                    </h5>
                  </div>
                    <div id="collapseOne_equipes_${key+1}" class="collapse" aria-labelledby="headingOne_equipes_${key+1}" data-parent="#accordionExample_equipes_${key+1}">
                        <div class="d-flex">
                            <div class="card m-3 w-100">
                            <div class="card-header text-center" id="headingOne_equipes_${key+1}">
                                Pilotos
                            </div>
                                <div class="card-body">
                                    ${
                                        dadoEquipe.pilotos.map((piloto, keyPiloto) => `
                                        <ul>
                                                <li><span>${keyPiloto+1}.</span> ${piloto.nome_piloto} <span> => ${piloto.quantidade}</span></li>
                                            </ul>
                                        `).join('')
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>`;
            });

        }
        
   </script>
@endsection

