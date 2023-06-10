@extends('layouts.main')

<style>
    .info-winner{
        /* background-color: red; */
        padding: 0.8rem;
        line-height: 10px;
        justify-content: space-between;
    }

    .info-winner{
        color: #fff;
        align-items: center;
    }

    .info-winner-name{
       font-size: 16px;
    }

    .info-winner-surname{
        text-transform: uppercase;
        font-size: 26px;
    }

    .info-winner-team-logo img{
        width: 45px;
        height: 45px;
    }

    .first-position{
        text-transform: uppercase;
        font-size: 36px;
        text-align: center;
        
    }

    .st-position{
        text-transform: uppercase;
        text-align: center;
    }

    .ia-difficulty{
        /* background-color: green; */
        padding: 0.8rem;
        line-height: 16px;
        text-transform: uppercase;
        color: #fff;
        font-size: 14px;
        margin-left: 8px;
    }

    .ia-difficulty span{
        font-weight: bolder;
        color: #fff;
    }

    .safety-car-qtd{
        /* background-color: blue; */
        padding: 0.8rem;
        line-height: 16px;
        text-transform: uppercase;
        color: #fff;
        font-size: 14px;
        margin-left: 8px;
    }

    .safety-car-qtd span{
        font-weight: bolder;
        color: #fff;
    }

    /* formatacao da tabela  */
    .table-container {
        max-height: 700px;
        overflow-y: scroll;
    }

    .race-title{
        text-transform: uppercase;
        padding: 10px;
        margin-bottom: 10px;
    }

    .driver-surname{
        text-transform: uppercase;
        font-weight: bolder;
    }

    .text-upper{
        text-transform: uppercase;
    }

    .team-name{
        color: rgb(212, 212, 212);
    }

    @media (max-width: 769px){
        .race-title{
            display: none;
        }

        .race-title-mobile{
            display: block;
        }

        .card-vencedor{
            margin-bottom: 60px;
        }

        .ocultar-mobile{
            display: none;
        }
    }

    @media (min-width: 769px){
        
        .race-title-mobile{
            display: none;
        }

    }

</style>

@section('section')
<div class="container mt-3 mb-3">
    <div class="row">
        <div class="col-md-12">
            <div class="bg-dark rounded p-2" style="height:auto;">
        <div class="container">
            <div class="row align-items-start">
              <div class="col-md-3">
                  <div class="race-title-mobile text-light p-3">
                      <h2>{{$corrida->pista->nome}} {{$descEvento}} {{$corrida->temporada->ano->ano}} <span> - Classificação Final</span></h2>
                  </div>
                <div class="card bg-dark border-white card-vencedor">
                    <img src="{{asset('images/'.$vencedor->pilotoEquipe->piloto->imagem)}}" alt="" srcset="">
                    <div class="info-winner mt-2 d-flex">
                        <div>
                            <p class="info-winner-name">{{ $vencedor->pilotoEquipe->piloto->nome }}</p>
                            <span class="info-winner-surname">{{ $vencedor->pilotoEquipe->piloto->sobrenome }}</span>
                        </div>
                        <div class="info-winner-team-logo">
                            <img src="{{asset('images/'.$vencedor->pilotoEquipe->equipe->imagem)}}">
                        </div>
                    </div>
                    <div class="mt-3 p-2 ia-difficulty">
                        <p>Dificuldade IA:  <span>{{$corrida->dificuldade_ia}}</span></p>
                     </div>
                    <div>
                        <div class="mb p-2 safety-car-qtd">
                            <p>Quantidade de Safety Car:  <span>{{$corrida->qtd_safety_car}}</span></p>
                        </div>
                    </div>
                    <div>
                        <div class="p-2 safety-car-qtd">
                            <p>Clima: <i class="{{$corrida->condicao->des_icone}}"></i></p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $model->links() }}
                    </div>
                </div>
              </div>
              <div class="col-md-9 bg-dark text-light">
                <div class="race-title">
                    <h2>{{$corrida->pista->nome}} {{$descEvento}} {{$corrida->temporada->ano->ano}} <span> - Classificação Final</span></h2>
                </div>
                <div class="table-responsive">
                    <table class="table text-light table-container">
                        <thead>
                            <tr>
                                <th class="text-start text-upper">#</th>
                                <th class="text-start text-upper">Piloto</th>
                                <th class="text-start text-upper">Equipe</th>
                                <th class="text-start text-upper">Largada</th>
                                <th class="text-start text-upper">Pontos</th>
                            </tr>
                        </thead>
                        <tbody class="text-left">
                            @foreach($model as $key => $item)
                                <tr <?= $item->flg_abandono == 'S' ? "class='trAbandono'" : "" ?>>
                                    <td><?= $item->flg_abandono == 'S' ? "DNF" : $item->chegada ?></td>
                                    <td>
                                        <img src="{{asset('images/'.$item->pilotoEquipe->piloto->pais->imagem)}}" alt="" style="width:35px; height: 25px;" class="ocultar-mobile">
                                        <span style="display: inline-block; vertical-align: middle;">{{$item->pilotoEquipe->piloto->nome}}</span>
                                        <span class="driver-surname" style="display: inline-block; vertical-align: middle;">{{$item->pilotoEquipe->piloto->sobrenome}}</span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <img src="{{asset('images/'.$item->pilotoEquipe->equipe->imagem)}}" style="width:25px; height: 25px;" class="ocultar-mobile">
                                        <span class="text-upper team-name" style="display: inline-block; vertical-align: middle;">
                                        {{$item->pilotoEquipe->equipe->nome}}
                                        </span>
                                    </td>
                                    <td>{{$item->largada}}</td>
                                    <td>+{{$item->pontuacao}}</td>
                                </tr>
                            @endforeach
                            @if($corrida->flg_sprint == 'N')
                                <tr>
                                    <td colspan="5">
                                        <span style="color:rgb(107, 34, 175); font-weight:bold; margin-right:1rem;">Volta Mais rápida</span>
                                        <span>{{$voltaRapida->piloto->nome}}</span>
                                        <span style="text-transform: uppercase; font-weight:bold;margin-right:1rem">{{$voltaRapida->piloto->sobrenome}}</span>
                                        <span style="margin-right: 1rem;">{{$voltaRapida->equipe->nome}}</span>
                                        <span>(+1 ponto)</span>
                                        </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
              </div>
            </div>
          </div>
         </div>
        </div>
    </div>

    <div class="row">
       <div class="col-md-12">
        <div class="mb-3 mt-2">
            <textarea readonly class="form-control bg-dark text-light text-left" name="observacoes" id="observacoes" rows="5">
                @if($corrida->observacoes)
                    {{$corrida->observacoes}}
                @endif
            </textarea>
        </div>

        @if(isset($corrida->updated_at))
            <div class="mb-3 mt-3 fst-italic">
                <span>Atualizado em {{date('d/m/Y', strtotime($corrida->updated_at))}} às {{date('H:i:s', strtotime($corrida->updated_at))}}</span>
            </div>
        @endif
        
        <a href="{{route('temporadas.index')}}" class="btn btn-dark ml-3">Voltar</a>
       </div>
    </div>
</div>
@endsection