<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Piloto extends Model
{
    use HasFactory;

    protected $table = 'pilotos';

    public $timestamps = false;

    protected $fillable = ['nome', 'sobrenome','imagem', 'pais_id', 'user_id', 'flg_ativo'];

    public function nomeCompleto(){
        return $this->nome.' '.$this->sobrenome;
    }

    /**relacionamentos */

    public function pais(){
        return $this->belongsTo(Pais::class);
    }

    public function pilotoEquipe(){
        return $this->hasMany(PilotoEquipe::class);
    }

    public function equipeAtual(){
        return 'Williams';
    }

    static function getInfoCampeonato($temporada_id, $piloto_id){
        
        $usuario = Auth::user()->id;

        $temporada = Temporada::find($temporada_id);

        $posicaoPiloto = '-';

        $classificacaoPilotos = $temporada->getClassificacao($usuario, $temporada)['resultadoPilotos']; //recebe array da classificação ja montado (as chaves/posições começam no zero)
        foreach($classificacaoPilotos as $key => $item){
            if($item->piloto_id == $piloto_id){
                $posicaoPiloto = $key+1; //adiciona 1 na chave do array (que começa com zero) que ja vem ordenado do banco de dados
            }
        }

        return $posicaoPiloto;
    }
}
