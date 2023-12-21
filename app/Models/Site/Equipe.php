<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Equipe extends Model
{
    use HasFactory;

    protected $table = 'equipes';

    public $timestamps = false;

    protected $fillable = ['nome', 'pais_id', 'user_id', 'flg_ativo', 'des_cor','imagem'];

    /**relacionamentos */
    public function pais(){
        return $this->belongsTo(Pais::class);
    }

    public function pilotoEquipe(){
        return $this->hasMany(PilotoEquipe::class);
    }

    static function getInfoCampeonato($temporada_id, $equipe_id){

        $usuario = Auth::user()->id;

        $temporada = Temporada::find($temporada_id);

        $posicaoEquipe = '-';
        $totalPontos = 0;

        $classificacaoPilotos = $temporada->getClassificacao($usuario, $temporada)['resultadoEquipes']; //recebe array da classificação ja montado (as chaves/posições começam no zero)
        foreach($classificacaoPilotos as $key => $item){
            if($item->equipe_id == $equipe_id){
                $totalPontos = $item->total;
                $posicaoEquipe = $key+1; //adiciona 1 na chave do array (que começa com zero) que ja vem ordenado do banco de dados
            }
        }

        return [
            'posicaoEquipe' => $posicaoEquipe,
            'totalPontos' => $posicaoEquipe != '-' ? $totalPontos : '-'
        ];
    }
}
