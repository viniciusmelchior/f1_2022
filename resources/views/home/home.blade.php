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
    /* border: 1px solid black; */
    border-collapse: collapse;
    margin: auto;
    padding: 10px;
    width: 190px;
}

.tabelaEstatisticas td{
    text-align: center!important;
}

.tabelaResultadosCorridas th, td{
    /* border: 1px solid black; */
    border-collapse: collapse;
    /* margin: auto; */
    padding: 10px;
    /* text-align: center; */
    width: 190px;
}

.tabelaResultadosCorridas td{
     text-align: center;
}

.tabelaEstatisticas tr:nth-child(even){
    background-color: #dce6eb;
}

.tabelaEstatisticas tr:hover:nth-child(1n + 2) {
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
</style>

@section('section')
    <div class="container">
        <div class="header-tabelas m-3">Vitórias <span id="toggle_vitorias"><i class="bi bi-plus-circle"></i></span></div>
        <div class="d-flex" id="div_vitorias">
            <div>
                <div class="">
                    <h1 class="descricao-tabela">Pilotos</h1>
                    <select name="vitoriasPilotosPorTemporada" id="vitoriasPilotosPorTemporada" class="form-select mt-3" style="width: 50%; margin:0 auto;">
                        <option value="" selected id="selectTemporadaVitoriasPiloto">Selecione uma Temporada</option>
                        @foreach($temporadas as $temporada)
                            <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                        @endforeach
                    </select>
                </div>
               <table class="m-5 tabelaEstatisticas" id="tabelaVitoriasPilotos">
                    <tr>
                        <th>#</th>
                        <th>Piloto</th>
                        <th>Vitórias</th>
                    </tr>
                    @foreach($totalVitoriasPorPiloto as $piloto_nome => $total_vitorias_piloto)
                        <tr>
                            <td>#</td>
                            <td>{{$piloto_nome}}</td>
                            <td>{{$total_vitorias_piloto}}</td>
                        </tr>
                    @endforeach
               </table>
            </div>

            <div>
                <h1 class="descricao-tabela">Equipes</h1>
                <select name="vitoriasEquipesPorTemporada" id="vitoriasEquipesPorTemporada" class="form-select mt-3" style="width: 50%; margin:0 auto;">
                    <option value="" selected id="selectTemporadaVitoriasEquipes">Selecione uma Temporada</option>
                    @foreach($temporadas as $temporada)
                        <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                    @endforeach
                </select>
                <table class="m-5 tabelaEstatisticas" id="tabelaVitoriasEquipes">
                     <tr>
                         <th>#</th>
                         <th>Equipe</th>
                         <th>Vitórias</th>
                     </tr>
                     @foreach($totalVitoriasPorEquipe as $equipe_nome => $total_vitorias_equipe)
                        <tr>
                            <td>#</td>
                            <td>{{$equipe_nome}}</td>
                            <td>{{$total_vitorias_equipe}}</td>
                        </tr>
                     @endforeach
                </table>
             </div>
        </div>

        <hr class="separador">

        <div class="header-tabelas m-3">Pole Positions <span id="toggle_poles"><i class="bi bi-plus-circle"></i></span></div>

        <div class="d-flex" id="div_poles">
            <div>
               <h1 class="descricao-tabela">Pilotos</h1>

               <select name="PolesPilotosPorTemporada" id="PolesPilotosPorTemporada" class="form-select mt-3" style="width: 50%; margin:0 auto;">
                    <option value="" selected id="selectTemporadaPolesPilotos">Selecione uma Temporada</option>
                @foreach($temporadas as $temporada)
                    <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                @endforeach
                </select>
               <table class="m-5 tabelaEstatisticas" id="tabelaPolesPilotos">
                    <tr>
                        <th>#</th>
                        <th>Piloto</th>
                        <th>Poles</th>
                    </tr>
                    @foreach($totalPolePositionsPorPiloto as $piloto_nome => $total_pole_positions_piloto)
                        <tr>
                            <td>#</td>
                            <td>{{$piloto_nome}}</td>
                            <td>{{$total_pole_positions_piloto}}</td>
                        </tr>
                    @endforeach
               </table>
            </div>

            <div>
                <h1 class="descricao-tabela">Equipes</h1>

                <select name="PolesEquipesPorTemporada" id="PolesEquipesPorTemporada" class="form-select mt-3" style="width: 50%; margin:0 auto;">
                    <option value="" selected id="selectTemporadaPolesEquipes">Selecione uma Temporada</option>
                @foreach($temporadas as $temporada)
                    <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                @endforeach
                </select>

                <table class="m-5 tabelaEstatisticas" id="tabelaPolesEquipes">
                     <tr>
                         <th>#</th>
                         <th>Piloto</th>
                         <th>Poles</th>
                     </tr>
                     @foreach($totalPolePositionsPorEquipe as $equipe_nome => $total_pole_positions_equipe)
                        <tr>
                            <td>#</td>
                            <td>{{$equipe_nome}}</td>
                            <td>{{$total_pole_positions_equipe}}</td>
                        </tr>
                     @endforeach
                </table>
             </div>
        </div>

        <hr class="separador">

        <div class="header-tabelas m-3">Podios <span id="toggle_podios"><i class="bi bi-plus-circle"></i></span></div>

        <div class="d-flex d-none" id="div_podios">
            <div>
                <h1 class="descricao-tabela">Pilotos</h1>

                <select name="podiosPilotosPorTemporada" id="podiosPilotosPorTemporada" class="form-select mt-3" style="width: 50%; margin:0 auto;">
                    <option value="" selected id="selectTemporadaPodiosPilotos">Selecione uma Temporada</option>
                @foreach($temporadas as $temporada)
                    <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                @endforeach
                </select>

                <table class="m-5 tabelaEstatisticas" id="tabelaPodiosPilotos">
                    <tr>
                        <th>#</th>
                        <th>Piloto</th>
                        <th>Podios</th>
                    </tr>
                    @foreach($totalPodiosPorPiloto as $piloto_nome => $total_podios_piloto)
                        <tr>
                            <td>#</td>
                            <td>{{$piloto_nome}}</td>
                            <td>{{$total_podios_piloto}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div>
                <h1 class="descricao-tabela">Equipes</h1>

                <select name="podiosEquipesPorTemporada" id="podiosEquipesPorTemporada" class="form-select mt-3" style="width: 50%; margin:0 auto;">
                    <option value="" selected id="selectTemporadaPodiosEquipes">Selecione uma Temporada</option>
                    @foreach($temporadas as $temporada)
                        <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                    @endforeach
                </select>

                <table class="m-5 tabelaEstatisticas" id="tabelaPodiosEquipes">
                    <tr>
                        <th>#</th>
                        <th>Equipe</th>
                        <th>Podios</th>
                    </tr>
                     @foreach($totalPodiosPorEquipe as $equipe_nome => $total_podios_equipe)
                        <tr>
                            <td>#</td>
                            <td>{{$equipe_nome}}</td>
                            <td>{{$total_podios_equipe}}</td>
                        </tr>
                     @endforeach
                </table>
            </div>
        </div>

        <hr class="separador">

        <div class="header-tabelas m-3">Abandonos <span id="toggle_abandonos"><i class="bi bi-plus-circle"></i></span></div>

        <div class="d-flex d-none" id="div_abandonos">
            <div>
                <h1 class="descricao-tabela">Pilotos</h1>
                <table class="m-5 tabelaEstatisticas">
                    <tr>
                        <th>#</th>
                        <th>Equipe</th>
                        <th>Chegadas</th>
                    </tr>
                    @foreach($totalAbandonosPorPiloto as $piloto_nome => $total_abandonos_piloto)
                        <tr>
                            <td>#</td>
                            <td>{{$piloto_nome}}</td>
                            <td>{{$total_abandonos_piloto}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div>
                <h1 class="descricao-tabela">Equipes</h1>
                <table class="m-5 tabelaEstatisticas">
                    <tr>
                        <th>#</th>
                        <th>Equipe</th>
                        <th>Chegadas</th>
                    </tr>
                     @foreach($totalAbandonosPorEquipe as $equipe_nome => $total_abandonos_equipe)
                        <tr>
                            <td>#</td>
                            <td>{{$equipe_nome}}</td>
                            <td>{{$total_abandonos_equipe}}</td>
                        </tr>
                     @endforeach
                </table>
            </div>
        </div>

        <hr class="separador">

        <div class="header-tabelas m-3">Chegadas <span id="toggle_chegadastop10"><i class="bi bi-plus-circle"></i></span></div>

        <div class="d-flex d-none" id="div_chegadastop10">
            <div>
                <h1 class="descricao-tabela">Pilotos</h1>

                <div>
                    <div>
                        <select name="chegadasPilotosPorTemporada" id="chegadasPilotosPorTemporada" class="form-select mt-3" style="width: 50%; margin:0 auto;">
                            <option value="" selected id="selectTemporadaChegadasPilotos">Selecione uma Temporada</option>
                        @foreach($temporadas as $temporada)
                            <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                        @endforeach
                        </select>
                    </div>
                
                    <div class="mt-3">
                        <div style="display: flex; justify-content: center; align-items: center;">
                            <div>
                                <label for="">Inicio</label>
                                <input type="number" name="inicioPosicaoChegadasPilotos" id="inicioPosicaoChegadasPilotos" style="width:30px; height:30px; text-align: center;" value="1">
                            </div>
                            <div>
                                <label for="" style="margin-left: 1rem;">Fim</label>
                                <input type="number" name="fimPosicaoChegadasPilotos" id="fimPosicaoChegadasPilotos" style="width:30px; height:30px; text-align: center;" value="10">
                            </div>
                        </div>
                    </div>
                </div>
                
                <table class="m-5 tabelaEstatisticas" id="tabelaChegadasPilotos">
                    <tr>
                        <th>#</th>
                        <th>Equipe</th>
                        <th>Chegadas</th>
                    </tr>
                    @foreach($totalTop10PorPiloto as $piloto_nome => $total_chegadas_piloto)
                        <tr>
                            <td>#</td>
                            <td>{{$piloto_nome}}</td>
                            <td>{{$total_chegadas_piloto}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div>
                <h1 class="descricao-tabela">Equipes</h1>

                <div>
                    <div>
                        <select name="chegadasEquipesPorTemporada" id="chegadasEquipesPorTemporada" class="form-select mt-3" style="width: 50%; margin:0 auto;">
                            <option value="" selected id="selectTemporadaChegadasEquipes">Selecione uma Temporada</option>
                        @foreach($temporadas as $temporada)
                            <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                        @endforeach
                        </select>
                    </div>
                
                    <div class="mt-3">
                        <div style="display: flex; justify-content: center; align-items: center;">
                            <div>
                                <label for="">Inicio</label>
                                <input type="number" name="inicioPosicaoChegadasEquipes" id="inicioPosicaoChegadasEquipes" style="width:30px; height:30px; text-align: center;" value="1">
                            </div>
                            <div>
                                <label for="" style="margin-left: 1rem;">Fim</label>
                                <input type="number" name="fimPosicaoChegadasEquipes" id="fimPosicaoChegadasEquipes" style="width:30px; height:30px; text-align: center;" value="10">
                            </div>
                        </div>
                    </div>
                </div>

                <table class="m-5 tabelaEstatisticas" id="tabelaChegadasEquipes">
                    <tr>
                        <th>#</th>
                        <th>Equipe</th>
                        <th>Chegadas</th>
                    </tr>
                     @foreach($totalTop10PorEquipe as $equipe_nome => $total_chegadas_equipe)
                        <tr>
                            <td>#</td>
                            <td>{{$equipe_nome}}</td>
                            <td>{{$total_chegadas_equipe}}</td>
                        </tr>
                     @endforeach
                </table>
            </div>
        </div>

        <hr class="separador">

        <div class="header-tabelas m-3">Títulos <span id="toggle_titulos"><i class="bi bi-plus-circle"></i></span></div>

        <div class="d-flex" id="div_titulos">
           
            <div>
                <h1 class="descricao-tabela">Pilotos</h1>
                <table class="m-5 tabelaEstatisticas">
                     <tr>
                         <th>#</th>
                         <th>Equipe</th>
                         <th>Títulos</th>
                     </tr>
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
                     <tr>
                         <th>#</th>
                         <th>Piloto</th>
                         <th>Títulos</th>
                     </tr>
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
        
    @php 

    $resultadoCorridas = Corrida::whereHas('resultado', function($query){
       $query->where('user_id', Auth::user()->id)->orderBy('temporada_id','DESC')->orderBy('ordem','DESC');
    })->get();

    $resultadoCorridas = $resultadoCorridas->where('flg_sprint', "<>", 'S');
    $resultadoCorridas = $resultadoCorridas->sortByDesc('ordem');
    $resultadoCorridas = $resultadoCorridas->sortByDesc('temporada_id');
    @endphp

    <hr>

    <h1 id="" class="descricao-tabela">Resultados</h1>
   
    <div class="montaTabelaEquipes">
        <table class="mt-5 tabelaResultadosCorridas" id="tabelaResultadoCorridas">
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
            <tbody>
            @foreach($resultadoCorridas as $key => $resultadoCorrida)
            @php 
    
            $resultado = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $resultadoCorrida->id)->get();

            $primeiro = $resultado->where('chegada', 1)->first();
            $polePosition = $resultado->where('largada', 1)->first();
            $segundo = $resultado->where('chegada', 2)->first();
            $terceiro = $resultado->where('chegada', 3)->first();
            $voltaRapida = PilotoEquipe::where('user_id', Auth::user()->id)->where('id', $resultadoCorrida->volta_rapida)->first();
            
            @endphp
                <tr @if($resultadoCorrida->flg_sprint == 'S') style="font-style:italic;" @endif>
                    <td class="text-nowrap">
                        @if($resultadoCorrida->flg_sprint != 'S')
                        {{$resultadoCorrida->ordem}}
                        @else 
                        Sprint
                        @endif
                    </td>
                    <td>
                        {{$resultadoCorrida->temporada->ano->ano}}
                    </td>
                    <td style="text-align: left;" class="text-nowrap">
                         <img src="{{asset('images/'.$resultadoCorrida->pista->pais->imagem)}}" alt="" style="width: 25px; height:20px;">
                        {{-- <a href="{{route('resultados.show',[$resultadoCorrida->id])}}" style="text-decoration: none; color:black;">{{$resultadoCorrida->pista->nome}}</a> --}}
                        <a href="{{route('resultados.show',[$resultadoCorrida->id])}}" style="text-decoration: none; color:black;" title="{{ $resultadoCorrida->pista->nome }}">
                            @if (isset($resultadoCorrida->evento->des_nome))
                                {{$resultadoCorrida->evento->des_nome}}
                            @else
                                {{ $resultadoCorrida->pista->nome }}
                            @endif
                        </a>
                        {{-- @if(isset($resultadoCorrida->condicao_id))
                            <i class="{{$resultadoCorrida->condicao->des_icone}}"></i>
                        @endif --}}
                        @if($resultadoCorrida->qtd_safety_car > 0)
                            <i class="bi bi-car-front-fill mt-3"></i>
                        @endif
                        </td>
                    <td style="text-align: left;" class="text-nowrap">
                        @if(isset($polePosition))
                        <span {{-- style="color:{{$polePosition->pilotoEquipe->equipe->des_cor}};" --}}>
                            <img src="{{asset('images/'.$polePosition->pilotoEquipe->equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                            {{$polePosition->pilotoEquipe->piloto->nomeCompleto()}}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td style="text-align: left;" class="text-nowrap">
                        @if(isset($primeiro))
                        <span {{-- style="color:{{$primeiro->pilotoEquipe->equipe->des_cor}};" --}}>
                            <img src="{{asset('images/'.$primeiro->pilotoEquipe->equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                            {{$primeiro->pilotoEquipe->piloto->nomeCompleto()}} 
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td style="text-align: left;" class="text-nowrap">
                        @if(isset($segundo))
                        <span {{-- style="color:{{$segundo->pilotoEquipe->equipe->des_cor}};" --}}>
                            <img src="{{asset('images/'.$segundo->pilotoEquipe->equipe->imagem)}}" alt="" style="width: 25px; height:25px;">
                            {{$segundo->pilotoEquipe->piloto->nomeCompleto()}}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td style="text-align: left;" class="text-nowrap">
                        @if(isset($terceiro))
                        <span {{-- style="color:{{$terceiro->pilotoEquipe->equipe->des_cor}};" --}}>
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
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="full-table-pagination-wrapper">
        <div id="pagination" class="pagination"></div>
    </div>

    </div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
</script>

<script src="{{asset('js/home/index.js')}}"></script>

{{-- URL's utilizadas para as chamadas AJAX no arquivo Javascript--}}
<script>
    urlclassificacaoGeralPorTemporada = "<?=route('ajax.classificacaoGeralPorTemporada')?>"
    ajaxGetVitoriasPilotoPorTemporada = "<?=route('ajax.ajaxGetVitoriasPilotoPorTemporada')?>"
    ajaxGetVitoriasEquipesPorTemporada = "<?=route('ajax.ajaxGetVitoriasEquipesPorTemporada')?>"
    ajaxGetPolesPilotosPorTemporada = "<?=route('ajax.ajaxGetPolesPilotosPorTemporada')?>"
    ajaxGetPolesEquipesPorTemporada = "<?=route('ajax.ajaxGetPolesEquipesPorTemporada')?>"
    ajaxGetPodiosPilotoPorTemporada = "<?=route('ajax.ajaxGetPodiosPilotoPorTemporada')?>"
    ajaxGetPodiosEquipesPorTemporada = "<?=route('ajax.ajaxGetPodiosEquipesPorTemporada')?>"
    ajaxGetChegadasPilotosPorTemporada = "<?=route('ajax.ajaxGetChegadasPilotosPorTemporada')?>"
    ajaxGetChegadasEquipesPorTemporada = "<?=route('ajax.ajaxGetChegadasEquipesPorTemporada')?>"
</script>