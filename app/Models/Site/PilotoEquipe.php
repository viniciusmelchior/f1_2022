<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class PilotoEquipe extends Model
{
    use HasFactory;

    protected $table = 'piloto_equipes';

    public $timestamps = false;

    protected $fillable = ['piloto_id', 'equipe_id', 'modelo_carro', 'user_id', 'ano_id', 'flg_ativo','flg_super_corrida'];

    /**relacionamentos */

    public function ano(){
        return $this->belongsTo(Ano::class);
    }

    public function piloto(){
        return $this->belongsTo(Piloto::class);
    }

    public function equipe(){
        return $this->belongsTo(Equipe::class);
    }

    public function resultado(){
        return $this->hasMany(Resultado::class);
    }

    public function skin(){
        return $this->belongsTo(Skin::class);
    }

    /**Função que acha qual posição determinado piloto chegou em tal corrida */

    /**recebe a corrida, temporada, piloto_equipe */
    public static function getResultadoPilotoEquipe($corrida, $piloto_id, $buscaPorAbandonos = false){

        // $resultado = Resultado::select('chegada','flg_abandono')
        $resultado = Resultado::join('piloto_equipes', 'resultados.pilotoEquipe_id', 'piloto_equipes.id')->select('*')
                            ->where('corrida_id', $corrida)
                            // ->where('pilotoEquipe_id', $pilotoEquipe)
                            ->where('piloto_equipes.piloto_id', $piloto_id)
                            // ->first();
                            ->first();

                            // dd($resultado);

        $chegada = '-';
        $backgroundAbandonos = '';

        if(isset($resultado)){
            $chegada = $resultado['chegada'];
            if($resultado->flg_abandono == 'S'){
                // $chegada = 'NC';
                $backgroundAbandonos = '#b81414';
            }
        }

        if(Route::current()->parameter('porPontuacao')){
            if(isset($resultado)){
                $chegada = $resultado['pontuacao'];
            }
        }

        if($buscaPorAbandonos == false){
            return $chegada;
        }

        return [$chegada, $backgroundAbandonos];

    }

    public static function getCarros() {

        return $carros = [
            'rss_formula_hybrid_2023',
            'rss_formula_hybrid_2022_s',
            'vrc_formula_alpha_2023',
            'rss_formula_hybrid_2021'
        ];

    }
}
