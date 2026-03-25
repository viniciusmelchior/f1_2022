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
            WITH ResultadosReordenados AS (
                SELECT 
                    e.nome AS equipe,
                    e.id AS id_da_equipe,
                    r.flg_abandono,
                    e.flg_ativo,
                    e.imagem as imagem_equipe,
                    pa.imagem as imagem_pais,
                    ROW_NUMBER() OVER (
                        PARTITION BY r.corrida_id 
                        ORDER BY r.chegada ASC
                    ) AS nova_posicao,
                     ROW_NUMBER() OVER (
                        PARTITION BY r.corrida_id 
                        ORDER BY r.largada ASC
                    ) AS nova_posicao_largada
                FROM resultados r
                JOIN corridas c ON r.corrida_id = c.id
                JOIN piloto_equipes pe ON pe.id = r.pilotoEquipe_id
                JOIN equipes e ON e.id = pe.equipe_id
                JOIN paises pa on e.pais_id = pa.id
                WHERE r.user_id = :user_id
                AND c.temporada_id >= :temporada_id 
                AND c.flg_sprint <> 'S'
            )
            SELECT 
                equipe,
                id_da_equipe,
                flg_ativo,
                imagem_equipe,
                imagem_pais,
                COUNT(*) AS total_corridas,
                SUM(CASE WHEN flg_abandono = 'S' THEN 1 ELSE 0 END) AS abandonos,
                CAST(SUM(CASE WHEN flg_abandono = 'S' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) AS DECIMAL(10,2)) AS porcentagem_abandonos,
                SUM(CASE WHEN nova_posicao = 1 THEN 1 ELSE 0 END) AS vitorias,
                SUM(CASE WHEN nova_posicao_largada = 1 THEN 1 ELSE 0 END) AS pole_positions,
                CAST(SUM(CASE WHEN nova_posicao = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*) AS DECIMAL(10,2)) AS aproveitamento_vitorias,
                CAST(SUM(CASE WHEN nova_posicao_largada = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*) AS DECIMAL(10,2)) AS aproveitamento_pole_positions,
                SUM(CASE WHEN nova_posicao <= 3 THEN 1 ELSE 0 END) AS podios,
                CAST(SUM(CASE WHEN nova_posicao <= 3 THEN 1 ELSE 0 END) * 100.0 / COUNT(*) AS DECIMAL(10,2)) AS aproveitamento_podios
            FROM ResultadosReordenados
            GROUP BY id_da_equipe
            ORDER BY total_corridas DESC
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
