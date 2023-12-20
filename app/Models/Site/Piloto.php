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
        //devolve a temporada, posicao e qtd de pontos, usado para listar a temporada temporadas/classificacao/2
        $usuario = Auth::user()->id;

        $classificacao = DB::select('
                                SELECT *
                                FROM (
                                    SELECT 
                                        piloto_id,
                                        pilotoEquipe_id,
                                        nome,
                                        sobrenome,
                                        imagem,
                                        equipe,
                                        total,
                                        ROW_NUMBER() OVER(ORDER BY total DESC) AS posicao
                                    FROM (
                                        SELECT 
                                            piloto_id,
                                            piloto_equipes.id AS pilotoEquipe_id,
                                            pilotos.nome,
                                            pilotos.sobrenome,
                                            equipes.imagem,
                                            equipes.nome AS equipe,
                                            SUM(pontuacao) AS total
                                        FROM resultados
                                        JOIN piloto_equipes ON piloto_equipes.id = resultados.pilotoEquipe_id
                                        JOIN pilotos ON pilotos.id = piloto_equipes.piloto_id
                                        JOIN equipes ON equipes.id = piloto_equipes.equipe_id
                                        JOIN corridas ON corridas.id = resultados.corrida_id
                                        JOIN temporadas ON temporadas.id = corridas.temporada_id
                                        WHERE temporadas.id = '.$temporada_id.'
                                            AND resultados.user_id = '.$usuario.'
                                        GROUP BY piloto_equipes.piloto_id
                                    ) AS subquery
                                ) AS ranked_results
                                WHERE piloto_id = '.$piloto_id.'
                                ');

        return $classificacao != null ? $classificacao[0]->posicao : '-';

    }

}
