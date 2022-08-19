<?php

namespace App\Exports;

use App\Invoice;
use App\Models\Site\Piloto;
use App\Models\Site\Resultado;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;

class PilotosExport implements FromView
{
    //construtor
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        //Dados do Piloto 
        $modelPiloto = Piloto::where('id', $this->id)
            ->where('user_id', Auth::user()->id)
            ->first();

        //total de corridas
        $resultados = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.flg_sprint', 'N')
            ->get();
        $totCorridas = 0;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                $totCorridas++;
            }
        }

        //total de vitorias
        $resultados =  Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.flg_sprint', 'N')
            ->where('chegada', 1)
            ->get();
        $totVitorias = 0;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                $totVitorias++;
            }
        }

        //total de poles
        $resultados =  Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.flg_sprint', 'N')
            ->where('largada', 1)
            ->get();
        $totPoles = 0;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                $totPoles++;
            }
        }

        //podios 
        $resultados = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.flg_sprint', 'N')
            ->where('chegada', '<=', 3)
            ->get();
        $totPodios = 0;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                $totPodios++;
            }
        }

        //chegadas no top 10 
        $resultados =  Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.flg_sprint', 'N')
            ->where('chegada', '<=', 10)
            ->get();
        $totTopTen = 0;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                $totTopTen++;
            }
        }

        //melhor posição de largada
        $resultados =  Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.flg_sprint', 'N')
            ->get();
        $melhorPosicaoLargada = 22;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                if ($resultado->largada <= $melhorPosicaoLargada) {
                    $melhorPosicaoLargada = $resultado->largada;
                }
            }
        }

        //pior posição de largada
        $resultados =  Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.flg_sprint', 'N')
            ->get();
        $piorPosicaoLargada = 0;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                if ($resultado->largada > $piorPosicaoLargada) {
                    $piorPosicaoLargada = $resultado->largada;
                }
            }
        }

        //melhor posição de chegada
        $resultados =  Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.flg_sprint', 'N')
            ->get();
        $melhorPosicaoChegada = 22;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                if ($resultado->chegada <= $melhorPosicaoChegada) {
                    $melhorPosicaoChegada = $resultado->chegada;
                }
            }
        }

        //pior posição de chegada 
        $resultados =  Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.flg_sprint', 'N')
            ->get();
        $piorPosicaoChegada = 0;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                if ($resultado->chegada > $piorPosicaoChegada) {
                    $piorPosicaoChegada = $resultado->chegada;
                }
            }
        }

        //Total de Pontos
        $resultados =  Resultado::where('user_id', Auth::user()->id)->get();
        $totPontos = 0;
        foreach ($resultados as $resultado) {
            if ($resultado->pilotoEquipe->piloto->id == $this->id) {
                $totPontos += $resultado->pontuacao;
            }
        }
        return view('site.pilotos.teste', compact('modelPiloto', 'totCorridas', 'totVitorias', 'totPontos', 'totPodios', 'totTopTen', 'piorPosicaoLargada', 'totPoles', 'melhorPosicaoLargada', 'melhorPosicaoChegada', 'piorPosicaoChegada'));
    }
}
