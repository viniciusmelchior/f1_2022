<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
    use HasFactory;

    protected $table = 'resultados';

    public $timestamps = false;

    protected $fillable = ['corrida_id','pilotoEquipe_id', 'largada','chegada','flg_abandono','pontuacao','pontuacao_classica','pontuacao_personalizada','pontuacao_invertida', 'user_id'];

    /**Relacionamentos */

    public function pilotoEquipe(){
        return $this->belongsTo(PilotoEquipe::class, 'pilotoEquipe_id', 'id');
    }

    public function corrida(){
        return $this->belongsTo(Corrida::class);
    }

    public static function podio($posicaoChegada){

        $podio = false;
        if($posicaoChegada >= 1 && $posicaoChegada <=3){
            $podio = true;
        }

        return $podio;
    }

    public static function topTen($posicaoChegada){

        $topTen = false;
        if($posicaoChegada >= 1 && $posicaoChegada <=10){
            $topTen = true;
        }

        return $topTen;
    }

    //usado para mostrar todas as corridas que o piloto/equipe completou (chegada diferente de nula)
    public static function chegadaValida($posicaoChegada){

        $chegada = false;
        
        if($posicaoChegada >= 1){
            $chegada = true;
        }

        return $chegada;
    }

}
