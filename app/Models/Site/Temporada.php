<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Temporada extends Model
{
    use HasFactory;

    protected $table = 'temporadas';

    public $timestamps = false;

    protected $fillable = ['des_temporada', 'user_id', 'ano_id', 'flg_finalizada','observacoes'];

    /*relacionamentos */
    public function ano(){
        return $this->belongsTo(Ano::class);
    }

    public function corrida(){
        return $this->hasMany(Corrida::class);
    }

    public function titulo(){
        return $this->hasOne(Titulo::class);
    }

    public function getClassificacao($usuario, $temporada){
        $cont = 1;
        $queryCountChegadaPilotos = '';
        $queryCountOrderByPilotos = '';
        $queryCountChegadaEquipes = '';
        $queryCountOrderByEquipes = '';

        while($cont <= 43){
            if($cont == 43){
                $queryCountChegadaPilotos .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = '.$cont.' and corridas.flg_sprint = "N") AS posicao_'.$cont.'';
                $queryCountChegadaEquipes .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = '.$cont.' and corridas.flg_sprint = "N") AS posicao_'.$cont.'';
            }else{
                $queryCountChegadaPilotos .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = '.$cont.' and corridas.flg_sprint = "N") AS posicao_'.$cont.',';
                $queryCountChegadaEquipes .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = '.$cont.' and corridas.flg_sprint = "N") AS posicao_'.$cont.',';
            }
            $queryCountOrderByPilotos .= ', posicao_'.$cont.' desc';
            $queryCountOrderByEquipes .= ', posicao_'.$cont.' desc';
            $cont++;
        }

        $resultadosPilotos = DB::select('
                            SELECT
                            piloto_id,
                            piloto_equipes.id AS pilotoEquipe_id,
                            pilotos.nome,
                            pilotos.sobrenome,
                            equipes.nome AS equipe,
                            equipes.imagem,
                            SUM(pontuacao) AS total,
                            '.$queryCountChegadaPilotos.'
                            FROM resultados
                            JOIN piloto_equipes ON piloto_equipes.id = resultados.pilotoEquipe_id
                            JOIN pilotos ON pilotos.id = piloto_equipes.piloto_id
                            JOIN equipes ON equipes.id = piloto_equipes.equipe_id
                            JOIN corridas ON corridas.id = resultados.corrida_id
                            JOIN temporadas ON temporadas.id = corridas.temporada_id
                            WHERE temporadas.id =  '.$temporada->id.'
                            AND resultados.user_id =  '.$usuario.'
                            GROUP BY piloto_equipes.piloto_id, piloto_equipes.id, pilotos.nome, pilotos.sobrenome, equipes.nome, equipes.imagem
                            ORDER BY total DESC '.$queryCountOrderByPilotos.';    
        ');

        $resultadosEquipes = DB::select('select equipe_id, equipes.nome as nome, equipes.imagem, sum(pontuacao) as total,'.$queryCountChegadaEquipes.' from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        where temporadas.id = '.$temporada->id.'
                                        and resultados.user_id = '.$usuario.'
                                        group by piloto_equipes.equipe_id
                                        order by total desc '.$queryCountOrderByEquipes);

                                        // dd($resultadosPilotos, $resultadosEquipes);

        return [
            'resultadoPilotos' => $resultadosPilotos,
            'resultadoEquipes' => $resultadosEquipes,
            'piloto_campeao' => $resultadosPilotos,
            'equipe_campea' => $resultadosEquipes
        ];
    }
}
