@php 
     use App\Models\Site\Piloto;
@endphp

@extends('layouts.main')

@section('section')

<style>
    .header-tabelas{
        padding-top: 1rem;
        padding-bottom: 1rem;
        /* background-color: rgba(194, 26, 26, 0.993); */
        text-align: center;
        font-size: 25px;
        font-weight: bolder;
        margin-bottom: 1rem;
        /* color: white; */
    }

    .text-upper{
        text-transform: uppercase;
    }

    .driver-surname{
        text-transform: uppercase;
        font-weight: bolder;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
    }

    .breadcrumb-item .breadcrumb-icon {
        margin-right: 5px;
    }

    @media (max-width: 769px){
        .ocultar-mobile{
            display: none;
        }

        .montaTabelaPilotos{
            margin-bottom: 50px;
        }
    }

    @media (min-width: 769px){
        
    }



</style>

  <div class="container mt-3 mb-3">

    <input type="hidden" id="temporada_id" name="temporada_id" value="{{$temporada->id}}">
    <input type="hidden" id="total_corridas" name="total_corridas" value="{{$totalCorridas}}">

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">
                    <span class="breadcrumb-icon"><i class="fas fa-home"></i></span> Home
                </a>
            </li>
            <li class="breadcrumb-item active">Temporadas</li>
            <li class="breadcrumb-item active" aria-current="page">
                <span class="breadcrumb-icon"><i class="fas fa-calendar"></i></span> Classificação Geral - {{$temporada->ano->ano}}
            </li>
        </ol>
    </nav>

    <div class="container">
        <div class="header-tabelas bg-dark text-light">Pontuação Normal</div>
        <div>Anterior
            <i class="bi bi-arrow-left-square" id="corrida_anterior" data-id="{{$corridaAtual->ordem}}" onclick="corridaAnterior(this)"></i>
            <i class="bi bi-arrow-right-square" id="proxima_corrida" data-id="{{$corridaAtual->ordem}}" onclick="proximaCorrida(this)"></i>
            Próxima
        </div>
        <div class="py-3">Após GP <span id="nome_corrida">{{$corridaAtual->pista->nome}}</span><span> (Etapa <span id="ordem_corrida">{{$corridaAtual->ordem}}</span> de {{$totalCorridas}})</span></div>
        <div class="d-flex bg-dark text-light p-3" style="justify-content: space-around; flex-wrap: wrap;">
            <div class="montaTabelaPilotos">
                <table class="table text-light" id="tabelaClassificacaoPilotos">
                    <thead>
                        <tr>
                            <th class="text-upper">#</th>
                            <th class="text-upper">Piloto</th>
                            <th class="text-upper">Pontos</th>
                            <th class="text-upper ocultar-mobile">Diferença</th>
                        </tr>
                    </thead>
                    @if(count($resultadosPilotos) > 0)
                    <tbody id="TbodytabelaClassificacaoPilotos">
                        @foreach($resultadosPilotos as $key => $piloto) 
                            <tr>
                                <td style="">{{$key+1}}</td>
                                <td style="vertical-align: middle;">
                                    @php 
                                        $equipeAtual = Piloto::equipeAtual($temporada->ano->id, $piloto->piloto_id);
                                    @endphp 
                                    <img src="{{asset('images/'.$equipeAtual->imagem)}}" alt="" style="width: 25px; height:25px;">
                                    <span style="display: inline-block; vertical-align: middle;">{{$piloto->nome}}</span>
                                    <span class="driver-surname" style="display: inline-block; vertical-align: middle;">{{$piloto->sobrenome}}</span>
                                </td>
                                <td class="pontosPiloto" style="">{{$piloto->total}}</td>
                                <td class="diferencaPontosPiloto ocultar-mobile" style=""></td>
                            </tr>
                        @endforeach
                    </tbody> 
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
        
            <div class="montaTabelaEquipes">
                <table class="table text-light" id="tabelaClassificacaoEquipes" style="">
                    <thead>
                        <tr>
                            <th class="text-upper">#</th>
                            <th class="text-upper">Equipe</th>
                            <th class="text-upper">Pontos</th>
                            <th class="text-upper ocultar-mobile">Diferença</th>
                        </tr>
                    </thead>
                    @if(count($resultadosEquipes) > 0)
                        @foreach($resultadosEquipes as $key => $equipe) 
                            <tbody>
                                <tr>
                                    <td style="">{{$key+1}}</td>
                                    <td style="vertical-align: middle;">
                                        <img src="{{asset('images/'.$equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                        <span style="display: inline-block; vertical-align: middle;">{{$equipe->nome}}</span>
                                    </td>
                                    <td class="pontosEquipe" style="">{{$equipe->total}}</td>
                                    <td class="diferencaPontosEquipe ocultar-mobile"></td>
                                </tr>
                            </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        <hr>
        <div class="header-tabelas bg-dark text-light">Pontuação Sprint</div>
        <div class="d-flex bg-dark text-light p-3" style="justify-content: space-around; flex-wrap: wrap;">
            <div class="montaTabelaPilotos">
                <table class="table text-light" id="tabelaClassificacaoPilotos">
                    <thead>
                        <tr>
                            <th class=" text-upper">#</th>
                            <th class="text-upper">Piloto</th>
                            <th class="text-upper">Pontos</th>
                        </tr>
                    </thead>
                    
                    @if(count($resultadosPilotosAlternativa) > 0)
                        @foreach($resultadosPilotosAlternativa as $key => $piloto) 
                        <tbody>
                            <tr>
                                <td style="">{{$key+1}}</td>
                                <td style="vertical-align: middle;">
                                    @php 
                                        $equipeAtual = Piloto::equipeAtual($temporada->ano->id, $piloto->piloto_id);
                                    @endphp 
                                    <img src="{{asset('images/'.$equipeAtual->imagem)}}" alt="" style="width: 25px; height:25px;">
                                    <span style="display: inline-block; vertical-align: middle;">{{$piloto->nome}}</span>
                                    <span class="driver-surname" style="display: inline-block; vertical-align: middle;">{{$piloto->sobrenome}}</span>
                                </td>
                                <td>{{$piloto->total}}</td>
                            </tr>
                        </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
           
            <div class="montaTabelaEquipes">
                <table class="table text-light" id="tabelaClassificacaoEquipes" style="">
                    <thead>
                        <tr>
                            <th class="text-upper">#</th>
                            <th class="text-upper">Equipe</th>
                            <th class="text-upper">Pontos</th>
                        </tr>
                    </thead>
                    @if(count($resultadosEquipesAlternativa) > 0)
                        @foreach($resultadosEquipesAlternativa as $key => $equipe) 
                            <tbody>
                                <tr>
                                    <td style="">{{$key+1}}</td>
                                    <td style="vertical-align: middle;">
                                        <img src="{{asset('images/'.$equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                        <span style="display: inline-block; vertical-align: middle;">{{$equipe->nome}}</span>
                                    </td>
                                    <td>{{$equipe->total}}</td>
                                </tr>
                            </tbody> 
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="3">Sem dados registrados</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <a href="{{route('temporadas.index')}}" class="btn btn-primary mt-3 bg-dark">Voltar</a>
  </div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
</script>

<script>
    urlgetClassificacaoAposCorrida = "<?=route('fetch.getClassificacaoAposCorrida')?>"
</script>

<script>
    $(document).ready(function () {
        calculaDiferencasPontuacao()
    });

    function calculaDiferencasPontuacao(){
        tdPontuacaoPilotos = document.querySelectorAll('.pontosPiloto');
        tdDiferencaPilotos = document.querySelectorAll('.diferencaPontosPiloto')
        tdPontuacaoEquipes = document.querySelectorAll('.pontosEquipe');
        tdDiferencaEquipes = document.querySelectorAll('.diferencaPontosEquipe')
        
        contadorPilotos = tdPontuacaoPilotos.length-2
        contadorEquipes = tdPontuacaoEquipes.length-2
        //pega o penultimo e soma adiciona no ultimo. Por isso tem ser o total -2()
        for(let i = 0; i < tdPontuacaoPilotos.length; i = i + 1 ) {
            if(i <= contadorPilotos){
                diferencaPilotos = parseInt(tdPontuacaoPilotos[0].innerText) - parseInt(tdPontuacaoPilotos[i+1].innerText ) 
                tdDiferencaPilotos[i+1].innerText = ` - ${diferencaPilotos}`;
            }
        }
        
        for(let j = 0; j < tdPontuacaoEquipes.length; j = j + 1 ) {
            if(j <= contadorEquipes){
                diferencaEquipes = parseInt(tdPontuacaoEquipes[0].innerText) - parseInt(tdPontuacaoEquipes[j+1].innerText ) 
                tdDiferencaEquipes[j+1].innerText = ` - ${diferencaEquipes}`;
            }
        }
    }

    function corridaAnterior(elemento){
        botaoVoltarCorrida = document.getElementById("corrida_anterior");
        botaoAvancarCorrida = document.getElementById("proxima_corrida");
        temporada_id = document.getElementById("temporada_id").value;
        nome_corrida = document.getElementById("nome_corrida");
        ordemCorrida = document.getElementById("ordem_corrida");
        tabelaClassificacaoPilotos = document.getElementById("TbodytabelaClassificacaoPilotos");

        var corrida_id = elemento.getAttribute("data-id");
        corrida_id--;

        if(corrida_id < 1){
            alert("primeira corrida ja está selecionada")
            return
        }

        //montagem da tabela
        tabelaClassificacaoPilotos.innerHTML = "";

         // como queremos saber o resultado da corrida anterior, diminuimos um numero
        console.log(corrida_id)

        //atualizar o data id
        botaoVoltarCorrida.setAttribute("data-id", corrida_id);
        botaoAvancarCorrida.setAttribute("data-id", corrida_id);

        var dados = {
            corrida_id: corrida_id,
            temporada_id: temporada_id
        };

        // Configurações da requisição
        var url = urlgetClassificacaoAposCorrida; // Substitua pela URL da sua API
        var opcoes = {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(dados)
        };

        // Realiza a requisição usando fetch
        fetch(url, opcoes)
            .then(response => response.json())
            .then(data => {
                
                data.resultadosPilotos.forEach((element, index) => {
                    var assetPath = "{{ asset('images/') }}" + "/"+element.imagem;

                    tabelaClassificacaoPilotos.innerHTML += `<tr>
                    <td>${index+1}</td>
                    <td style="vertical-align: middle;">
                        <img src="${assetPath}" style="width: 25px; height:25px;">
                        <span style="display: inline-block; vertical-align: middle;">${element.nome}</span>
                        <span class="driver-surname" style="display: inline-block; vertical-align: middle;">${element.sobrenome}</span>
                    </td>
                    <td class="pontosPiloto">${element.total}</td>
                    <td class="diferencaPontosPiloto ocultar-mobile">0</td>
                    </tr>`
                });
                nome_corrida.innerText =  data.corridaAtual
                ordemCorrida.innerText = data.ordemCorrida
                console.log(data.resultadosPilotos, data.corridaAtual) 
                calculaDiferencasPontuacao()
            })
            .catch(error => {
                console.error("Ocorreu um erro:", error);
            });
        }

    //inicio função pontuação proxima corrida
    function proximaCorrida(elemento){
        botaoVoltarCorrida = document.getElementById("corrida_anterior");
        botaoAvancarCorrida = document.getElementById("proxima_corrida");
        temporada_id = document.getElementById("temporada_id").value;
        total_corridas = document.getElementById("total_corridas").value;
        nome_corrida = document.getElementById("nome_corrida");
        ordemCorrida = document.getElementById("ordem_corrida");
        tabelaClassificacaoPilotos = document.getElementById("TbodytabelaClassificacaoPilotos");

        var corrida_id = elemento.getAttribute("data-id");
        corrida_id++; // como queremos saber o resultado da proxima corrida, aumentamos um numero

        if(corrida_id > total_corridas){
            alert("última corrida ja está selecionada")
            return
        }

        //montagem da tabela
        tabelaClassificacaoPilotos.innerHTML = "";

        console.log(corrida_id)

        //atualizar o data id
        botaoVoltarCorrida.setAttribute("data-id", corrida_id);
        botaoAvancarCorrida.setAttribute("data-id", corrida_id);

        var dados = {
            corrida_id: corrida_id,
            temporada_id: temporada_id
        };

        // Configurações da requisição
        var url = urlgetClassificacaoAposCorrida; // Substitua pela URL da sua API
        var opcoes = {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(dados)
        };

        // Realiza a requisição usando fetch
        fetch(url, opcoes)
            .then(response => response.json())
            .then(data => {
                
                data.resultadosPilotos.forEach((element, index) => {
                    var assetPath = "{{ asset('images/') }}" + "/"+element.imagem;

                    tabelaClassificacaoPilotos.innerHTML += `<tr>
                    <td>${index+1}</td>
                    <td style="vertical-align: middle;">
                        <img src="${assetPath}" style="width: 25px; height:25px;">
                        <span style="display: inline-block; vertical-align: middle;">${element.nome}</span>
                        <span class="driver-surname" style="display: inline-block; vertical-align: middle;">${element.sobrenome}</span>
                    </td>
                    <td class="pontosPiloto">${element.total}</td>
                    <td class="diferencaPontosPiloto ocultar-mobile">0</td>
                    </tr>`
                });
                nome_corrida.innerText =  data.corridaAtual
                ordemCorrida.innerText = data.ordemCorrida
                console.log(data.resultadosPilotos, data.corridaAtual) 
                calculaDiferencasPontuacao()
            })
            .catch(error => {
                console.error("Ocorreu um erro:", error);
            });
    }
    

</script>