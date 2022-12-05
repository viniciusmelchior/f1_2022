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

}
