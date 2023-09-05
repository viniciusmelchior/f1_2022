<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotoEquipe extends Model
{
    use HasFactory;

    protected $table = 'piloto_equipes';

    public $timestamps = false;

    protected $fillable = ['piloto_id', 'equipe_id', 'user_id', 'ano_id', 'flg_ativo','flg_super_corrida'];

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

    /**Função que acha qual posição determinado piloto chegou em tal corrida */

    /**recebe a corrida, temporada, piloto_equipe */
    public static function getResultadoPilotoEquipe($corrida, $pilotoEquipe){
        // dd($corrida, $pilotoEquipe);

        $resultado = Resultado::select('chegada','flg_abandono')
                            ->where('corrida_id', $corrida)
                            ->where('pilotoEquipe_id', $pilotoEquipe)
                            ->first();

        $chegada = '-';

        if(isset($resultado)){
            $chegada = $resultado['chegada'];
            if($resultado->flg_abandono == 'S'){
                $chegada = 'NC';
            }
        }

        return $chegada;

    }
}
