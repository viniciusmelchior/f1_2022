@php 
    use App\Models\Site\Resultado;
    use App\Models\Site\Temporada;
    use App\Models\Site\Titulo;
    use App\Models\Site\Corrida;
    use App\Models\Site\PilotoEquipe;
@endphp

@extends('layouts.main')

<style>
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

.tabelaEstatisticas th, td{
    /* border: 1px solid black; */
    border-collapse: collapse;
    margin: auto;
    padding: 10px;
    text-align: center!important;
    width: 190px;
}

.tabelaResultadosCorridas th, td{
    /* border: 1px solid black; */
    border-collapse: collapse;
    margin: auto;
    padding: 10px;
    text-align: center!important;
    width: 190px;
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
                    @foreach($totVitoriasPorPiloto as $key => $piloto)
                        <tr>
                            {{-- <td><a href=""><i class="bi bi-eye" style="color:black;"></i></a></td> --}}
                            <td>#</td>
                            <td>{{$piloto->nome}}</td>
                            <td>{{$piloto->vitorias}}</td>
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
                     @foreach($totVitoriasPorEquipe as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
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
                    @foreach($totPolesPorPiloto as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
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
                     @php 
                        $poleEquipes = Resultado::where('user_id', Auth::user()->id)->where('largada', 1)->get();
                        $poles = [];
                        foreach($poleEquipes as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($poles, $item->pilotoEquipe->equipe->nome);
                            }
                        }

                        $totPorEquipe= array_count_values($poles);
                        arsort($totPorEquipe);
                     @endphp
                     @foreach($totPorEquipe as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
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
                    @php 
                        $podiosPilotos = Resultado::where('user_id', Auth::user()->id)->where('chegada', '<=', 3)->get();
                        $podios = [];
                        foreach($podiosPilotos as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($podios, $item->pilotoEquipe->piloto->nomeCompleto());
                            }
                        }

                        $totPorPiloto = array_count_values($podios);
                        arsort($totPorPiloto);
                    @endphp
                    @foreach($totPorPiloto as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
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
                    @php 
                        $podiosEquipes = Resultado::where('user_id', Auth::user()->id)->where('chegada','<=', 3)->get();
                        $podios = [];
                        foreach($podiosEquipes as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($podios, $item->pilotoEquipe->equipe->nome);
                            }
                        }

                        $totPorEquipe= array_count_values($podios);
                        arsort($totPorEquipe);
                     @endphp
                     @foreach($totPorEquipe as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
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
                    @php 
                        $abandonos = Resultado::where('user_id', Auth::user()->id)->where('flg_abandono', 'S')->get();
                        $totAbandonos = [];
                        foreach($abandonos as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($totAbandonos, $item->pilotoEquipe->piloto->nomeCompleto());
                            }
                        }

                        $totPorPiloto = array_count_values($totAbandonos);
                        arsort($totPorPiloto);
                    @endphp
                    @foreach($totPorPiloto as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
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
                    @php 
                        $abandonoEquipes = Resultado::where('user_id', Auth::user()->id)->where('flg_abandono','S')->get();
                        $totAbandonosEquipes = [];
                        foreach($abandonoEquipes as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($totAbandonosEquipes, $item->pilotoEquipe->equipe->nome);
                            }
                        }

                        $totPorEquipe= array_count_values($totAbandonosEquipes);
                        arsort($totPorEquipe);
                     @endphp
                     @foreach($totPorEquipe as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
                        </tr>
                     @endforeach
                </table>
            </div>
        </div>

        <hr class="separador">

        <div class="header-tabelas m-3">Chegadas TOP 10 <span id="toggle_chegadastop10"><i class="bi bi-plus-circle"></i></span></div>

        <div class="d-flex d-none" id="div_chegadastop10">
            <div>
                <h1 class="descricao-tabela">Pilotos</h1>
                <table class="m-5 tabelaEstatisticas">
                    <tr>
                        <th>#</th>
                        <th>Equipe</th>
                        <th>Chegadas</th>
                    </tr>
                    @php 
                        $top10Pilotos = Resultado::where('user_id', Auth::user()->id)->where('chegada', '<=', 10)->get();
                        $top10pilotos = [];
                        foreach($top10Pilotos as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($top10pilotos, $item->pilotoEquipe->piloto->nomeCompleto());
                            }
                        }

                        $totPorPiloto = array_count_values($top10pilotos);
                        arsort($totPorPiloto);
                    @endphp
                    @foreach($totPorPiloto as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
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
                    @php 
                        $top10Equipes = Resultado::where('user_id', Auth::user()->id)->where('chegada','<=', 10)->get();
                        $top10 = [];
                        foreach($top10Equipes as $item){
                            if($item->corrida->flg_sprint == 'N'){
                                array_push($top10, $item->pilotoEquipe->equipe->nome);
                            }
                        }

                        $totPorEquipe= array_count_values($top10);
                        arsort($totPorEquipe);
                     @endphp
                     @foreach($totPorEquipe as $key => $value)
                        <tr>
                            <td>#</td>
                            <td>{{$key}}</td>
                            <td>{{$value}}</td>
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
                     @php 
                        $titulosPilotos = Titulo::where('user_id', Auth::user()->id)->get();
                        $titulos = [];
                        foreach($titulosPilotos as $item){
                            array_push($titulos, $item->pilotoEquipe->piloto->nomeCompleto());
                        }

                        $totPorPiloto= array_count_values($titulos);
                        arsort($totPorPiloto);
                     @endphp
                    @foreach($totPorPiloto as $key => $value)
                     <tr>
                         <td>#</td>
                         <td>{{$key}}</td>
                         <td>{{$value}}</td>
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
                     @php 
                        $titulosEquipes = Titulo::where('user_id', Auth::user()->id)->get();
                        $titulos = [];
                        foreach($titulosEquipes as $item){
                            array_push($titulos, $item->equipe->nome);
                        }

                        $totPorEquipe= array_count_values($titulos);
                        arsort($totPorEquipe);
                     @endphp
                      @foreach($totPorEquipe as $key => $value)
                      <tr>
                          <td>#</td>
                          <td>{{$key}}</td>
                          <td>{{$value}}</td>
                      </tr>
                   @endforeach
                </table>
            </div>
        </div>

    <hr class="separador">
    
    <div class="header-tabelas m-3">Classificaçao Histórica <span id="toggle_classificacao_historica"><i class="bi bi-plus-circle"></i></span></div>
    
    <div class="d-flex d-none" id="div_classificacao_historica">
        <div class="d-flex">
            <div class="montaTabelaPilotos">
                <table class="m-5 tabelaEstatisticas" id="">
                    <tr>
                        <th>Posição</th>
                        <th>Piloto</th>
                        <th>Pontos</th>
                    </tr>
                    <tr>
                        @foreach( $resultadosPilotosGeral as $key => $piloto )
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$piloto->nome}}</td>
                            <td>{{$piloto->total}}</td>
                        </tr>
                        @endforeach
                    </tr>
                </table>
            </div>
        
            <div class="montaTabelaEquipes">
                <table class="m-5 tabelaEstatisticas" id="">
                    <tr>
                        <th>Posição</th>
                        <th>Equipe</th>
                        <th>Pontos</th>
                    </tr>
                    @foreach( $resultadosEquipesGeral as $key => $equipe ) 
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$equipe->nome}}</td>
                            <td>{{$equipe->total}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div> 
    </div>                             


    <hr class="separador">

    <h1 id="tituloClassificacao" class="descricao-tabela">Classificação Geral</h1>
    <div class="">
        <select name="mudarTemporada" id="mudarTemporada" class="form-select mt-3" style="width: 30%; margin:0 auto;">
            <option value="" selected>Selecione uma Temporada</option>
            @foreach($temporadas as $temporada)
                <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
            @endforeach
        </select>
    </div>
    
    <div class="d-flex">
        <div class="montaTabelaPilotos">
            <table class="m-5 tabelaEstatisticas" id="tabelaClassificacaoPilotos">
                <tr>
                    <th>Posição</th>
                    <th>Equipe</th>
                    <th>Piloto</th>
                    <th>Pontos</th>
                </tr>
                <tr>
                    <td colspan="4">Selecione uma Temporada</td>
                </tr>
            </table>
        </div>
       
        <div class="montaTabelaEquipes">
            <table class="m-5 tabelaEstatisticas" id="tabelaClassificacaoEquipes">
                <tr>
                    <th>Posição</th>
                    <th>Equipe</th>
                    <th>Pontos</th>
                </tr>
                <tr>
                    <td colspan="3">Selecione uma Temporada</td>
                </tr>
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
                    <th style="width: 5%;">#</th>
                    <th style="width: 5%;">Temporada</th>
                    <th style="width: 15%;">Pista</th>
                    <th>Pole Position</th>
                    <th>Primeiro</th>
                    <th>Segundo</th>
                    <th>Terceiro</th>
                    <th>Volta Mais Rápida</th>
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
                    <td>
                        @if($resultadoCorrida->flg_sprint != 'S')
                        {{$resultadoCorrida->ordem}}
                        @else 
                        Sprint
                        @endif
                    </td>
                    <td>
                        {{$resultadoCorrida->temporada->ano->ano}}
                    </td>
                    <td>{{$resultadoCorrida->pista->nome}}
                        @if(isset($resultadoCorrida->condicao_id))
                            <i class="{{$resultadoCorrida->condicao->des_icone}}"></i>
                        @endif
                        @if($resultadoCorrida->qtd_safety_car > 0)
                            <i class="bi bi-car-front-fill mt-3"></i>
                        @endif
                        </td>
                    <td>
                        @if(isset($polePosition))
                        <span style="color:{{$polePosition->pilotoEquipe->equipe->des_cor}};">
                            {{$polePosition->pilotoEquipe->piloto->nomeCompleto()}}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(isset($primeiro))
                        <span style="color:{{$primeiro->pilotoEquipe->equipe->des_cor}};">
                            {{$primeiro->pilotoEquipe->piloto->nomeCompleto()}} 
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(isset($segundo))
                        <span style="color:{{$segundo->pilotoEquipe->equipe->des_cor}};">
                            {{$segundo->pilotoEquipe->piloto->nomeCompleto()}}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(isset($terceiro))
                        <span style="color:{{$terceiro->pilotoEquipe->equipe->des_cor}};">
                            {{$terceiro->pilotoEquipe->piloto->nomeCompleto()}}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(isset($voltaRapida))
                            <span style="color:{{$voltaRapida->equipe->des_cor}};">
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

    @php 
        // sleep(1);
        // $time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        // $tempoExecucao =  "Tempo de execução: ".$time;
        // dd($tempoExecucao);
    @endphp

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
</script>