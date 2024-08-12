@php 
 use App\Models\Site\Corrida;
 use App\Models\Site\Resultado;
 use App\Models\Site\Temporada;
 use App\Models\Site\Titulo;
 use App\Models\Site\PilotoEquipe;
@endphp
@extends('layouts.main')

@section('section')
<style>

    td{
        white-space: nowrap;
    }

    th{
        white-space: nowrap;
    }

    h1{
        text-align: center;
    }
    
    /* table, th, td {
        border: 1px solid black;
    } */

    table {
        outline: 1px solid black;
    }
    
    table {
        border-collapse: collapse;
        margin: auto;
    }
    
    th, td{
        padding: 10px;
        /* text-align: center!important; */
        width: 190px;
    }

    .tabelaEstatisticas td{
    text-align: center!important;
}
    
    th{
        font-weight: bold;
    }
    
    
    tr:nth-child(even) {
        background-color: #dce6eb;
    }
    
    tr:hover:nth-child(1n + 2) {
        background-color: #a0200f;
        color: #fff;
    }
    
    .header-tabelas{
        padding: 15px;
        background-color: rgba(194, 26, 26, 0.993);
        text-align: center;
        font-size: 25px;
        font-weight: bolder;
        color: white;
    }
    </style>
<div class="container mt-3 mb-3">
    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active">Resultados</li>
            <li class="breadcrumb-item active" aria-current="page">{{$resultadoCorridas[0]->pista->pais->des_nome}}</li>
        </ol>
    </nav>
   <div class="container">

    <div class="header-tabelas m-3">Chegadas <span id="toggle_chegadas"><i class="bi bi-plus-circle"></i></span></div>
    <div class="d-flex d-none" id="div_chegadas">
        <div>
            <h1 class="descricao-tabela">Pilotos</h1>
            <div class="mt-3">
                <div style="display: flex; justify-content: center; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <label for="inicioPosicaoChegadasPilotos">Inicio</label>
                        <input type="number" name="inicioPosicaoChegadasPilotos" id="inicioPosicaoChegadasPilotos" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1">
                    </div>
                    <div style="display: flex; align-items: center; margin-left: 1rem;">
                        <label for="fimPosicaoChegadasPilotos">Fim</label>
                        <input type="number" name="fimPosicaoChegadasPilotos" id="fimPosicaoChegadasPilotos" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1">
                    </div>
                </div>
            </div>
            <table class="m-5 tabelaEstatisticas" id="tabelaChegadasPilotos">
                <tr>
                    <th>#</th>
                    <th>Piloto</th>
                    <th>Chegadas</th>
                </tr>
                @foreach($totalVitoriasPorPiloto as $total_vitorias_piloto)
                    <tr>
                        <td>#</td>
                        <td>{{$total_vitorias_piloto->piloto_nome_completo}}</td>
                        <td>{{$total_vitorias_piloto->vitorias}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div>
            <h1 class="descricao-tabela">Equipes</h1>
            <div class="mt-3">
                <div style="display: flex; justify-content: center; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <label for="inicioPosicaoChegadasPilotos">Inicio</label>
                        <input type="number" name="inicioPosicaoChegadasEquipes" id="inicioPosicaoChegadasEquipes" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1">
                    </div>
                    <div style="display: flex; align-items: center; margin-left: 1rem;">
                        <label for="fimPosicaoChegadasPilotos">Fim</label>
                        <input type="number" name="fimPosicaoChegadasEquipes" id="fimPosicaoChegadasEquipes" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1">
                    </div>
                </div>
            </div>
            <table class="m-5 tabelaEstatisticas" id="tabelaChegadasEquipes">
                <tr>
                    <th>#</th>
                    <th>Equipe</th>
                    <th>Chegadas</th>
                </tr>
                @foreach($totalVitoriasPorEquipe as $total_vitorias_equipe)
                    <tr>
                        <td>#</td>
                        <td>{{$total_vitorias_equipe->equipe_nome}}</td>
                        <td>{{$total_vitorias_equipe->vitorias}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
    <hr class="separador">

    <div class="header-tabelas m-3">Largadas <span id="toggle_largadas"><i class="bi bi-plus-circle"></i></span></div>
    <div class="d-flex d-none" id="div_largadas">
        <div>
            <h1 class="descricao-tabela">Pilotos</h1>
            <div class="mt-3">
                <div style="display: flex; justify-content: center; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <label for="inicioPosicaoLargadasPilotos">Inicio</label>
                        <input type="number" name="inicioPosicaoLargadasPilotos" id="inicioPosicaoLargadasPilotos" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1">
                    </div>
                    <div style="display: flex; align-items: center; margin-left: 1rem;">
                        <label for="fimPosicaoLargadasPilotos">Fim</label>
                        <input type="number" name="fimPosicaoLargadasPilotos" id="fimPosicaoLargadasPilotos" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1">
                    </div>
                </div>
            </div>
            <table class="m-5 tabelaEstatisticas" id="tabelaLargadasPilotos">
                <tr>
                    <th>#</th>
                    <th>Piloto</th>
                    <th>Largadas</th>
                </tr>
                @foreach($totalLargadasPorPiloto as $total_largadas_piloto)
                    <tr>
                        <td>#</td>
                        <td>{{$total_largadas_piloto->piloto_nome_completo}}</td>
                        <td>{{$total_largadas_piloto->largadas}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div>
            <h1 class="descricao-tabela">Equipes</h1>
            <div class="mt-3">
                <div style="display: flex; justify-content: center; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <label for="inicioPosicaoLargadasPilotos">Inicio</label>
                        <input type="number" name="inicioPosicaoLargadasEquipes" id="inicioPosicaoLargadasEquipes" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1">
                    </div>
                    <div style="display: flex; align-items: center; margin-left: 1rem;">
                        <label for="fimPosicaoLargadasPilotos">Fim</label>
                        <input type="number" name="fimPosicaoLargadasEquipes" id="fimPosicaoLargadasEquipes" style="width:35px; height:25px; text-align: center; margin-left:0.6rem;" value="1">
                    </div>
                </div>
            </div>
            <table class="m-5 tabelaEstatisticas" id="tabelaLargadasEquipes">
                <tr>
                    <th>#</th>
                    <th>Equipe</th>
                    <th>Largadas</th>
                </tr>
                @foreach($totalLargadasPorEquipe as $total_largadas_equipe)
                    <tr>
                        <td>#</td>
                        <td>{{$total_largadas_equipe->equipe_nome}}</td>
                        <td>{{$total_largadas_equipe->largadas}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
    <hr class="separador">

    <div class="header-tabelas m-3">Resultados <span id="toggle_resultados"><i class="bi bi-plus-circle"></i></span></div>
    <div class="d-flex" id="div_resultados">
        <div class="montaTabelaEquipes">
            <table class="mb-5 mt-2" id="tabelaClassificacaoEquipes">
                <tr>
                <th style="width: 5%;" class="text-nowrap">#</th>
                <th style="width: 5%;" class="text-nowrap">Temporada</th>
                <th style="width: 15%; text-align: left;" class="text-nowrap">Pista</th>
                <th style="text-align: left;" class="text-nowrap">Pole Position</th>
                <th style="text-align: left;" class="text-nowrap">Primeiro</th>
                <th style="text-align: left;" class="text-nowrap">Segundo</th>
                <th style="text-align: left;" class="text-nowrap">Terceiro</th>
                <th style="text-align: left;" class="text-nowrap">Volta Rápida</th>
                <th style="text-align: left;" class="text-nowrap">Visualizar</th>
                </tr>
                @foreach($resultadoCorridas as $key => $resultadoCorrida)
                @php 
    
                $primeiro = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $resultadoCorrida->id)->where('chegada', 1)->first();
                $polePosition = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $resultadoCorrida->id)->where('largada', 1)->first();
                $segundo = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $resultadoCorrida->id)->where('chegada', 2)->first();
                $terceiro = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $resultadoCorrida->id)->where('chegada', 3)->first();
                $voltaRapida = PilotoEquipe::where('user_id', Auth::user()->id)->where('id', $resultadoCorrida->volta_rapida)->first();
                
                @endphp
                    <tr @if($resultadoCorrida->flg_sprint == 'S') style="font-style:italic;" @endif>
                        <td style="text-align: center;" class="text-nowrap">
                            @if($resultadoCorrida->flg_sprint != 'S')
                            {{$resultadoCorrida->ordem}}
                            @else 
                            Sprint
                            @endif
                        </td>
                        <td style="text-align: center;">
                            {{ substr($resultadoCorrida->temporada->des_temporada, 0, strpos($resultadoCorrida->temporada->des_temporada, ' ')) }}
                        </td>
                        <td>{{$resultadoCorrida->pista->nome}}
                            @if(isset($resultadoCorrida->condicao_id))
                                <i class="{{$resultadoCorrida->condicao->des_icone}}"></i>
                            @endif
                            @if($resultadoCorrida->qtd_safety_car > 0)
                                <i class="bi bi-car-front-fill mt-3"></i>
                            @endif
                        </td>
                        <td style="text-align: left;" class="text-nowrap">
                            @if(isset($polePosition))
                                <span>
                                    <img src="{{asset('images/'.$polePosition->pilotoEquipe->equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                    {{$polePosition->pilotoEquipe->piloto->nomeCompleto()}}
                                </span>
                            @else
                            -
                            @endif
                        </td>
                        <td style="text-align: left;" class="text-nowrap">
                            @if(isset($primeiro))
                                <span>
                                    <img src="{{asset('images/'.$primeiro->pilotoEquipe->equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                    {{$primeiro->pilotoEquipe->piloto->nomeCompleto()}} 
                                </span>
                            @else
                            -
                            @endif
                        </td>
                        <td style="text-align: left;" class="text-nowrap">
                            @if(isset($segundo))
                            <span>
                                <img src="{{asset('images/'.$segundo->pilotoEquipe->equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                {{$segundo->pilotoEquipe->piloto->nomeCompleto()}}
                            </span>
                            @else
                            -
                            @endif
                        </td>
                        <td style="text-align: left;" class="text-nowrap">
                            @if(isset($terceiro))
                                <span>
                                    <img src="{{asset('images/'.$terceiro->pilotoEquipe->equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                    {{$terceiro->pilotoEquipe->piloto->nomeCompleto()}}
                                </span>
                            @else
                            -
                            @endif
                        </td>
                        <td style="text-align: left;" class="text-nowrap">
                            @if(isset($voltaRapida))
                            <span {{-- style="color:{{$voltaRapida->equipe->des_cor}};" --}}>
                                <img src="{{asset('images/'.$voltaRapida->equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                                {{$voltaRapida->piloto->nomeCompleto()}}
                            </span>
                            @else
                            -
                            @endif 
                        </td>
                        <td class="text-center">
                            <a data-toggle="tooltip" data-placement="top" title="Visualizar" class="" href="{{route('resultados.show', [$resultadoCorrida->id])}}"><i class="bi bi-eye-fill" style="color: black;"></i></a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
    <hr class="separador">

    <a href="{{route('paises.index')}}" class="btn btn-secondary ml-3 mb-3">Voltar</a>
   </div>

   <input type="hidden" name="pais_id" value="{{$resultadoCorridas[0]->pista->pais->id}}" id="pais_id">
</div>

<script src="{{asset('js/paises/index.js')}}"></script>

{{--ROTAS utilizadas nas requisições ajax feitas no arquivo paises/index.js--}}
<script>

    //Chegadas
    ajaxGetChegadasPilotos = "<?=route('paises.ajaxGetChegadasPilotos')?>"
    ajaxGetLargadasPilotos = "<?=route('paises.ajaxGetLargadasPilotos')?>"
    ajaxGetChegadasEquipes = "<?=route('paises.ajaxGetChegadasEquipes')?>"
    ajaxGetLargadasEquipes = "<?=route('paises.ajaxGetLargadasEquipes')?>"

    //Largadas
</script>

@endsection



