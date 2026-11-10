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

    protected $fillable = ['des_temporada', 'user_id', 'ano_id', 'flg_finalizada','observacoes', 'referencia', 'flg_playoff', 'qtd_corridas', 'playoff_classificados'];

    // MELHORIA 20261110 (playoff): 3 corridas por fase; 8 classificados (quartas, semi, final) ou 16 (+ oitavas)
    public const PLAYOFF_CORRIDAS_POR_FASE = 3;

    public static function playoffCorridasMataMataPara($classificados){
        return ((int) $classificados === 16 ? 4 : 3) * self::PLAYOFF_CORRIDAS_POR_FASE;
    }

    public function playoffQtdClassificados(){
        return (int) $this->playoff_classificados === 16 ? 16 : 8;
    }

    public function playoffCorridasMataMata(){
        return self::playoffCorridasMataMataPara($this->playoffQtdClassificados());
    }

    public function playoffQtdClassificatorias(){
        return (int) $this->qtd_corridas - $this->playoffCorridasMataMata();
    }

    public function temPlayoff(){
        return $this->flg_playoff === 'S' && $this->playoffQtdClassificatorias() >= 1;
    }

    public function tituloPlayoff(){
        return $this->hasOne(TituloPlayoff::class);
    }

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
        // Values are concatenated into the SQL below: only integers get through.
        $temporadaId = (int) $temporada->id;
        $usuarioId = (int) $usuario;
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
                            WHERE temporadas.id =  '.$temporadaId.'
                            AND resultados.user_id =  '.$usuarioId.'
                            AND (corridas.flg_sprint IS NULL OR corridas.flg_sprint <> "S") -- MELHORIA 20261110: sprints não somam
                            GROUP BY piloto_equipes.piloto_id, piloto_equipes.id, pilotos.nome, pilotos.sobrenome, equipes.nome, equipes.imagem
                            ORDER BY total DESC '.$queryCountOrderByPilotos.';    
        ');

        $resultadosEquipes = DB::select('select equipe_id, equipes.nome as nome, equipes.imagem, sum(pontuacao) as total,'.$queryCountChegadaEquipes.' from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        where temporadas.id = '.$temporadaId.'
                                        and resultados.user_id = '.$usuarioId.'
                                        and (corridas.flg_sprint is null or corridas.flg_sprint <> "S")
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
