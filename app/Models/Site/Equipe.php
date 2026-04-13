<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Equipe extends Model
{
    use HasFactory;

    protected $table = 'equipes';

    public $timestamps = false;

    protected $fillable = ['nome', 'pais_id', 'user_id', 'flg_ativo', 'des_cor','imagem'];

    public static function buscaEstatisticasListagemequipes(int $userId, int $temporadaId = 0){
        $sql = "
                        WITH EstatisticasEquipes AS (
                -- 1. Calculamos as posições reais dentro de cada corrida (quem venceu o GP, etc)
                SELECT 
                    pe.equipe_id,
                    r.id AS resultado_id,
                    r.flg_abandono,
                    ROW_NUMBER() OVER (PARTITION BY r.corrida_id ORDER BY r.chegada ASC) AS pos_chegada,
                    ROW_NUMBER() OVER (PARTITION BY r.corrida_id ORDER BY r.largada ASC) AS pos_largada
                FROM resultados r
                JOIN piloto_equipes pe ON r.pilotoEquipe_id = pe.id
                JOIN corridas c ON r.corrida_id = c.id
                WHERE r.user_id = :user_id
                AND c.temporada_id >= :temporada_id
                AND c.flg_sprint <> 'S'
            ),
            RankingEquipeFinal AS (
                -- 2. Agrupamos os resultados por EQUIPE para ter os totais somados de seus pilotos
                SELECT 
                    equipe_id,
                    COUNT(resultado_id) AS total_corridas_entradas,
                    SUM(CASE WHEN flg_abandono = 'S' THEN 1 ELSE 0 END) AS abandonos,
                    SUM(CASE WHEN pos_chegada = 1 THEN 1 ELSE 0 END) AS vitorias,
                    SUM(CASE WHEN pos_largada = 1 THEN 1 ELSE 0 END) AS pole_positions,
                    SUM(CASE WHEN pos_chegada <= 3 THEN 1 ELSE 0 END) AS podios
                FROM EstatisticasEquipes
                GROUP BY equipe_id
            )
            -- 3. Unimos com a tabela de equipes para garantir que todas apareçam (mesmo as sem histórico)
            SELECT 
                e.id AS id_da_equipe,
                e.nome AS equipe,
                e.flg_ativo,
                e.imagem AS imagem_equipe,
                pa.imagem AS imagem_pais,
                
                -- Tratamento de nulos para equipes sem resultados
                COALESCE(s.total_corridas_entradas, 0) AS total_corridas,
                COALESCE(s.abandonos, 0) AS abandonos,
                COALESCE(s.vitorias, 0) AS vitorias,
                COALESCE(s.pole_positions, 0) AS pole_positions,
                COALESCE(s.podios, 0) AS podios,
                
                -- Cálculos de porcentagem
                CAST(CASE WHEN COALESCE(s.total_corridas_entradas, 0) = 0 THEN 0 
                    ELSE (s.abandonos * 100.0 / s.total_corridas_entradas) END AS DECIMAL(10,2)) AS porcentagem_abandonos,
                
                CAST(CASE WHEN COALESCE(s.total_corridas_entradas, 0) = 0 THEN 0 
                    ELSE (s.vitorias * 100.0 / s.total_corridas_entradas) END AS DECIMAL(10,2)) AS aproveitamento_vitorias,
                    
                CAST(CASE WHEN COALESCE(s.total_corridas_entradas, 0) = 0 THEN 0 
                    ELSE (s.pole_positions * 100.0 / s.total_corridas_entradas) END AS DECIMAL(10,2)) AS aproveitamento_pole_positions,
                    
                CAST(CASE WHEN COALESCE(s.total_corridas_entradas, 0) = 0 THEN 0 
                    ELSE (s.podios * 100.0 / s.total_corridas_entradas) END AS DECIMAL(10,2)) AS aproveitamento_podios

            FROM equipes e
            JOIN paises pa ON e.pais_id = pa.id
            LEFT JOIN RankingEquipeFinal s ON e.id = s.equipe_id
            WHERE e.id <> 0
            and e.user_id = 3
            ORDER BY total_corridas DESC, vitorias DESC, equipe ASC;
        ";

        return DB::select($sql, [
            'user_id' => $userId,
            'temporada_id' => $temporadaId
        ]);
    }

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
