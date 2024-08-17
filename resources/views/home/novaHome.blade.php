@php 
    use App\Models\Site\Resultado;
    use App\Models\Site\Temporada;
    use App\Models\Site\Titulo;
    use App\Models\Site\Corrida;
    use App\Models\Site\PilotoEquipe;
@endphp

@extends('layouts.main')

<style>

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
-webkit-appearance: none;
margin: 0;
}

/*HOME*/
.tabelaEstatisticas{
    border: 1px solid black;
    border-radius: 25px;
}

.tabelaResultadosCorridas{
    border: 1px solid black;
    border-radius:25px;
}

.tabelaEstatisticas th{
    font-weight: bold;
}

.tabelaEstatisticas th{
    border-collapse: collapse;
    margin: auto;
    padding: 10px;
    width: 190px;
}

.tabelaEstatisticas td{
    text-align: center!important;
    text-wrap: nowrap;
}

.tabelaResultadosCorridas th, td{
   
    border-collapse: collapse;
    padding: 10px;
    width: 190px;
}

.tabelaResultadosCorridas td{
     text-align: center;
}

.tabelaEstatisticas tbody tr:nth-child(odd){
    background-color: #dce6eb;
}

.tabelaEstatisticas tbody tr:hover {
    background-color: #a0200f;
    color: #fff;
}

.tabelaResultadosCorridas tbody tr:nth-child(odd) {
  background-color: #dce6eb;
}

.tabelaResultadosCorridas tbody tr:hover {
  background-color: #a0200f;
  color: #fff;
}

.current-page{
    background: #fff;
    color: black;
}

.header-tabelas{
    padding: 15px;
    background-color: rgba(194, 26, 26, 0.993);
    text-align: center;
    font-size: 25px;
    font-weight: bolder;
    color: white;
}

.descricao-tabela{
    text-align: center;
    text-transform: uppercase;
    font-size: 2rem;
}

.botaoPaginacao{
    color: white;
    background-color: black;
    padding: 2px 8px;
    border-radius: 2px;
    border: none;
}

.align-center {
        display: flex;
        align-items: center;
    }

    .align-center > div {
        margin-right: 1rem;
    }

    .align-center > div:last-child {
        margin-right: 0;
    }

</style>

@section('section')
    <div class="container">

        <div class="header-tabelas m-3">Chegadas <span id="toggle_chegadas"><i class="bi bi-plus-circle" id="icon_chegadas"></i></span></div>
        <div class="d-flex d-none" id="div_chegadas">
            <div>
                <h1 class="descricao-tabela">Pilotos</h1>
                <div class="mt-3">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <div style="display: flex; align-items: center;">
                            <label for="inicioPosicaoChegadasPilotos">Inicio</label>
                            <input type="number" name="inicioPosicaoChegadasPilotos" id="inicioChegadaPilotos" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1" onchange="buscaChegadasPilotos()">
                        </div>
                        <div style="display: flex; align-items: center; margin-left: 1rem;">
                            <label for="fimPosicaoChegadasPilotos">Fim</label>
                            <input type="number" name="fimPosicaoChegadasPilotos" id="fimChegadaPilotos" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1" onchange="buscaChegadasPilotos()">
                        </div>
                    </div>
                </div>
                <table class="m-5 tabelaEstatisticas" id="tabelaChegadasPilotos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Piloto</th>
                            <th>Chegadas</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyChegadaPilotos">

                    </tbody>
                </table>
            </div>
            <div>
                <h1 class="descricao-tabela">Equipes</h1>
                <div class="mt-3">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <div style="display: flex; align-items: center;">
                            <label for="inicioChegadaEquipes">Inicio</label>
                            <input type="number" name="inicioPosicaoChegadasEquipes" id="inicioChegadaEquipes" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1" onchange="buscaChegadasEquipes()">
                        </div>
                        <div style="display: flex; align-items: center; margin-left: 1rem;">
                            <label for="fimChegadaEquipes">Fim</label>
                            <input type="number" name="fimPosicaoChegadasEquipes" id="fimChegadaEquipes" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1" onchange="buscaChegadasEquipes()">
                        </div>
                    </div>
                </div>
                <table class="m-5 tabelaEstatisticas" id="tabelaChegadasEquipes">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Equipe</th>
                            <th>Chegadas</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyChegadaEquipes">

                    </tbody>
                </table>
            </div>
        </div>

        <hr class="separador">

        <div class="header-tabelas m-3">Largadas <span id="toggle_largadas"><i class="bi bi-plus-circle" id="icon_largadas"></i></span></div>
    <div class="d-flex d-none" id="div_largadas">
        <div>
            <h1 class="descricao-tabela">Pilotos</h1>
            <div class="mt-3">
                <div style="display: flex; justify-content: center; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <label for="inicioPosicaoLargadasPilotos">Inicio</label>
                        <input type="number" name="inicioPosicaoLargadasPilotos" id="inicioLargadaPilotos" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1" onchange="buscaLargadasPilotos()">
                    </div>
                    <div style="display: flex; align-items: center; margin-left: 1rem;">
                        <label for="fimPosicaoLargadasPilotos">Fim</label>
                        <input type="number" name="fimPosicaoLargadasPilotos" id="fimLargadaPilotos" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1" onchange="buscaLargadasPilotos()">
                    </div>
                </div>
            </div>
            <table class="m-5 tabelaEstatisticas" id="tabelaLargadasPilotos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Piloto</th>
                        <th>Largadas</th>
                    </tr>
                </thead>
               <tbody id="tbodyLargadaPilotos">

               </tbody>
            </table>
        </div>
        <div>
            <h1 class="descricao-tabela">Equipes</h1>
            <div class="mt-3">
                <div style="display: flex; justify-content: center; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <label for="inicioPosicaoLargadasPilotos">Inicio</label>
                        <input type="number" name="inicioPosicaoLargadasEquipes" id="inicioLargadaEquipes" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1" onchange="buscaLargadasEquipes()">
                    </div>
                    <div style="display: flex; align-items: center; margin-left: 1rem;">
                        <label for="fimPosicaoLargadasPilotos">Fim</label>
                        <input type="number" name="fimPosicaoLargadasEquipes" id="fimLargadaEquipes" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1" onchange="buscaLargadasEquipes()">
                    </div>
                </div>
            </div>
            <table class="m-5 tabelaEstatisticas" id="tabelaLargadasEquipes">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Equipe</th>
                        <th>Largadas</th>
                    </tr>
                </thead>
               <tbody id="tbodyLargadaEquipes">

               </tbody>
            </table>
        </div>
    </div>
    <hr class="separador">

        <div class="header-tabelas m-3">Títulos <span id="toggle_titulos"><i class="bi bi-plus-circle" id="icon_titulos"></i></span></div>

        <div class="d-flex d-none" id="div_titulos">
           
            <div>
                <h1 class="descricao-tabela">Pilotos</h1>
                <table class="m-5 tabelaEstatisticas">
                     <thead>
                        <tr>
                            <th>#</th>
                            <th>Piloto</th>
                            <th>Títulos</th>
                        </tr>
                     </thead>
                    @foreach($totalTitulosPorPiloto as $piloto_nome => $total_titulos_piloto)
                     <tr>
                         <td>#</td>
                         <td>{{$piloto_nome}}</td>
                         <td>{{$total_titulos_piloto}}</td>
                     </tr>
                  @endforeach
                </table>
            </div> 
            <div>
                <h1 class="descricao-tabela">Equipes</h1>
                <table class="m-5 tabelaEstatisticas">
                     <thead>
                        <tr>
                            <th>#</th>
                            <th>Equipe</th>
                            <th>Títulos</th>
                        </tr>
                     </thead>
                      @foreach($totalTitulosPorEquipe as $equipe_nome => $total_titulos_equipe)
                      <tr>
                          <td>#</td>
                          <td>{{$equipe_nome}}</td>
                          <td>{{$total_titulos_equipe}}</td>
                      </tr>
                   @endforeach
                </table>
            </div>
        </div>
    </div>
        <hr>

        <h1 id="" class="descricao-tabela">Resultados</h1>

       <div class="align-center">
            <div>
                <input type="text" name="" id="busca" placeholder="Busca" onkeyup="buscaResultadosCorrida()" class="form-control">
            </div>
            <div>
                <label for="">Resultados por página:</label>
            </div>
            <div class="my-3">
                <select name="" id="qtdResultados" onchange="buscaResultadosCorrida()" class="form-select">
                    <option value="3">3</option>
                    <option value="6">6</option>
                    <option value="9">9</option>
                    <option value="9" selected>10</option>
                    <option value="todos">Todos</option>
                </select>
            </div>
       </div>
        <table id="tabelaResultadosCorridas" class="tabelaResultadosCorridas">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-nowrap">#</th>
                    <th style="width: 5%;" class="text-nowrap">Temporada</th>
                    <th style="width: 15%; text-align:left;" class="text-nowrap">Pista</th>
                    <th style="text-align: left;" class="text-nowrap">Pole Position</th>
                    <th style="text-align: left;" class="text-nowrap">Primeiro</th>
                    <th style="text-align: left;" class="text-nowrap">Segundo</th>
                    <th style="text-align: left;" class="text-nowrap">Terceiro</th>
                    <th style="text-align: left;" class="text-nowrap">Volta Mais Rápida</th>
                </tr>
            </thead>
            <tbody id="tbodyResultadoCorridas">
            
            </tbody>
        </table>
        <div id="paginacao" class="d-flex gap-2 mt-2 mb-5 justify-content-end">
        {{-- <div id="paginacao" class="d-flex gap-2 mt-2 mb-5"> --}}

        </div>

        <input type="hidden" name="" id="url_busca" value="{{route('buscaResultadosCorrida')}}">

        <input type="hidden" name="" id="url_chegada_pilotos" value="{{route('url_chegada_pilotos')}}">
        <input type="hidden" name="" id="url_chegada_equipes" value="{{route('url_chegada_equipes')}}">
        <input type="hidden" name="" id="url_largada_pilotos" value="{{route('url_largada_pilotos')}}">
        <input type="hidden" name="" id="url_largada_equipes" value="{{route('url_largada_equipes')}}">
    
@endsection

<script>

    // //ao abrir a pagina ja busca resultados
    window.onload = function() {
        buscaResultadosCorrida();
        buscaChegadasPilotos();
        buscaChegadasEquipes();
        buscaLargadasPilotos();
        buscaLargadasEquipes();

        $('#toggle_titulos').click(function (e) { 
            e.preventDefault();
            $('#div_titulos').toggleClass('d-none');

            var icon = $('#icon_titulos');
            if (icon.hasClass('bi-plus-circle')) {
                icon.removeClass('bi-plus-circle').addClass('bi-dash-circle');
            } else {
                icon.removeClass('bi-dash-circle').addClass('bi-plus-circle');
            }
        });

        $('#toggle_chegadas').click(function (e) { 
            e.preventDefault();
            $('#div_chegadas').toggleClass('d-none');

            var icon = $('#icon_chegadas');
            if (icon.hasClass('bi-plus-circle')) {
                icon.removeClass('bi-plus-circle').addClass('bi-dash-circle');
            } else {
                icon.removeClass('bi-dash-circle').addClass('bi-plus-circle');
            }
        });

        $('#toggle_largadas').click(function (e) { 
            e.preventDefault();
            $('#div_largadas').toggleClass('d-none');

            var icon = $('#icon_largadas');
            if (icon.hasClass('bi-plus-circle')) {
                icon.removeClass('bi-plus-circle').addClass('bi-dash-circle');
            } else {
                icon.removeClass('bi-dash-circle').addClass('bi-plus-circle');
            }
        });
    };

    async function buscaResultadosCorrida(url = null){
        let busca = document.getElementById('busca').value
        let qtdResultados = document.getElementById('qtdResultados').value
        url = url ? url : document.getElementById('url_busca').value;
        const token = document.querySelector('meta[name="csrf-token"]').content

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                busca: busca,
                qtdResultados:qtdResultados
            })
        })

        const res = await req.json();

        //preencher TBODY da tabela
        preencherTbodyTabelaResultados(res.resultadosCorrida.data);
        preencherPaginacao(res.resultadosCorrida.links);

    }

    function preencherTbodyTabelaResultados(data){
      
        let tbody = document.querySelector('#tbodyResultadoCorridas')

        //se for dev usa uma url, se for produção usa outra
        // const urlImagens = 'http://127.0.0.1:8000/images/';
        const urlImagens = 'https://f1.vitorvasconcellos.com.br/images/';

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        data.forEach(element => {
            
            tbody.innerHTML += `<tr>
                                    <td>${element.ordem}</td>
                                    <td>${element.temporada}</td>
                                    <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.imagemPaisCorrida}" style="width: 25px; height:20px;">
                                        </span>${element.pista}
                                    </td>
                                    <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.equipePolePosition}" style="width: 25px; height:25px;">
                                        </span>${element.polePosition}
                                    </td>
                                   <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.equipePrimeiro}" style="width: 25px; height:25px;">
                                        </span>${element.primeiro}
                                    </td>
                                    <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.equipeSegundo}" style="width: 25px; height:25px;">
                                        </span>${element.segundo}
                                    </td>
                                    <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.equipeTerceiro}" style="width: 25px; height:25px;">
                                        </span>${element.terceiro}
                                    </td>
                                    <td style="text-align: left;" class="text-nowrap">
                                        <span>
                                            <img src="${urlImagens}${element.equipeVoltaRapida}" style="width: 25px; height:25px;">
                                        </span>${element.voltaRapida}
                                    </td>
                                </tr>`
        });
    }

    async function buscaChegadasPilotos(inicio = 1, fim = 1){
        inicio = document.getElementById('inicioChegadaPilotos').value
        fim = document.getElementById('fimChegadaPilotos').value
        url = document.getElementById('url_chegada_pilotos').value;
        const token = document.querySelector('meta[name="csrf-token"]').content

        if(inicio > fim){
            alert("Posição final não pode ser maior que a inicial")
            return
        }

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                inicio:inicio,
                fim:fim
            })
        })

        const res = await req.json();

        let tbody = document.querySelector('#tbodyChegadaPilotos')

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        res.totalVitoriasPorPiloto.forEach((piloto, index) => {
            
            tbody.innerHTML += `<tr>
                                    <td>${index+1}</td>
                                    <td>${piloto.piloto_nome_completo}</td>
                                    <td>${piloto.chegadas}</td>
                                </tr>`
        });
    }

    async function buscaChegadasEquipes(inicio = 1, fim = 1){
        inicio = document.getElementById('inicioChegadaEquipes').value
        fim = document.getElementById('fimChegadaEquipes').value
        url = document.getElementById('url_chegada_equipes').value;
        const token = document.querySelector('meta[name="csrf-token"]').content

        if(inicio > fim){
            alert("Posição final não pode ser maior que a inicial")
            return
        }

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                inicio:inicio,
                fim:fim
            })
        })

        const res = await req.json();
        
        let tbody = document.querySelector('#tbodyChegadaEquipes')

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        res.totalVitoriasPorEquipe.forEach((equipe, index) => {
            
            tbody.innerHTML += `<tr>
                                    <td>${index+1}</td>
                                    <td>${equipe.equipe_nome}</td>
                                    <td>${equipe.chegadas}</td>
                                </tr>`
        });
    }

    async function buscaLargadasEquipes(inicio = 1, fim = 1){
        inicio = document.getElementById('inicioLargadaEquipes').value
        fim = document.getElementById('fimLargadaEquipes').value
        url = document.getElementById('url_largada_equipes').value;
        const token = document.querySelector('meta[name="csrf-token"]').content

        if(inicio > fim){
            alert("Posição final não pode ser maior que a inicial")
            return
        }

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                inicio:inicio,
                fim:fim
            })
        })

        const res = await req.json();
        
        let tbody = document.querySelector('#tbodyLargadaEquipes')

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        res.totalLargadasPorEquipe.forEach((equipe, index) => {
            
            tbody.innerHTML += `<tr>
                                    <td>${index+1}</td>
                                    <td>${equipe.nome}</td>
                                    <td>${equipe.largadas}</td>
                                </tr>`
        });
    }

    async function buscaLargadasPilotos(inicio = 1, fim = 1){
        inicio = document.getElementById('inicioLargadaPilotos').value
        fim = document.getElementById('fimLargadaPilotos').value
        url = document.getElementById('url_largada_pilotos').value;
        const token = document.querySelector('meta[name="csrf-token"]').content

        if(inicio > fim){
            alert("Posição final não pode ser maior que a inicial")
            return
        }

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                inicio:inicio,
                fim:fim
            })
        })

        const res = await req.json();
        
        let tbody = document.querySelector('#tbodyLargadaPilotos')

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        res.totalLargadasPorPiloto.forEach((piloto, index) => {
            
            tbody.innerHTML += `<tr>
                                    <td>${index+1}</td>
                                    <td>${piloto.piloto_nome_completo}</td>
                                    <td>${piloto.largadas}</td>
                                </tr>`
        });
    }

    function preencherPaginacao(links){
        let paginacao = document.getElementById('paginacao');

        paginacao.innerHTML = '';

        links.forEach(link => {
            let paginaAtual = link.active ? 'bg-danger' : '';
            let ocultarBotao = link.url == null && link.label != '...' ? 'd-none' : '';

            if(link.label == "&laquo; Previous"){
                link.label = '<<'
            }else if (link.label == "Next &raquo;"){
                link.label = '>>'
            }

            paginacao.innerHTML += `<button class="${paginaAtual} ${ocultarBotao} botaoPaginacao" onclick="buscaResultadosCorrida('${link.url}')">${link.label}</button>`
        })

    }

</script>