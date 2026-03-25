<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Piloto extends Model
{
    use HasFactory;

    protected $appends = ['corridas','vitorias', 'poles', 'podios', 'abandonos', 'aproveitamentoVitorias', 'aproveitamentoPodios', 'aproveitamentoPoles', 'aproveitamentoAbandonos'];

    protected $table = 'pilotos';

    public $timestamps = false;

    protected $fillable = ['nome', 'sobrenome','imagem', 'pais_id', 'user_id', 'flg_ativo'];

    //Função que retorna a listagem dos pilotos e as estatísticas básicas deles
    public static function buscaEstatisticasListagemPilotos(int $userId, int $temporadaId = 0)
    {
        $sql = "
            WITH ResultadosReordenados AS (
                SELECT 
                    p.id AS id_do_piloto,
                    CONCAT(p.nome, ' ', p.sobrenome) AS piloto,
                    e.nome AS equipe,
                    e.id AS id_da_equipe,
                    r.flg_abandono,
                    p.flg_ativo,
                    p.imagem as imagem_piloto,
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
                JOIN pilotos p ON p.id = pe.piloto_id
                JOIN equipes e ON e.id = pe.equipe_id
                JOIN paises pa on p.pais_id = pa.id
                WHERE r.user_id = :user_id
                AND c.temporada_id >= :temporada_id
                AND p.id <> 0 
                AND c.flg_sprint <> 'S'
            )
            SELECT 
                id_do_piloto,
                piloto,
                equipe,
                id_da_equipe,
                flg_ativo,
                imagem_piloto,
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
            GROUP BY id_do_piloto
            ORDER BY total_corridas DESC
        ";

        return DB::select($sql, [
            'user_id' => $userId,
            'temporada_id' => $temporadaId
        ]);
    }

    public function nomeCompleto(){
        return $this->nome.' '.$this->sobrenome;
    }

    public function getCorridasAttribute()
    {
        return $this->attributes['corridas'] ?? 0;
    }

    public function getVitoriasAttribute()
    {
        return $this->attributes['vitorias'] ?? 0;
    }

    public function getPolesAttribute()
    {
        return $this->attributes['poles'] ?? 0;
    }

    public function getPodiosAttribute()
    {
         return $this->attributes['podios'] ?? 0;
    }

    public function getAbandonosAttribute()
    {
         return $this->attributes['abandonos'] ?? 0;
    }

    public function getAproveitamentoVitoriasAttribute()
    {
        if (($this->corridas ?? 0) > 0) {
            return round($this->vitorias / $this->corridas * 100, 1);
        }
        return 0;
    }

    public function getAproveitamentoPolesAttribute()
    {
        if (($this->corridas ?? 0) > 0) {
            return round($this->poles / $this->corridas * 100, 1);
        }
        return 0;
    }

    public function getAproveitamentoPodiosAttribute()
    {
        if (($this->corridas ?? 0) > 0) {
            return round($this->podios / $this->corridas * 100, 1);
        }
        return 0;
    }

    public function getAproveitamentoAbandonosAttribute()
    {
        if (($this->corridas ?? 0) > 0) {
            return round($this->abandonos / $this->corridas * 100, 1);
        }
        return 0;
    }

    /**relacionamentos */

    public function pais(){
        return $this->belongsTo(Pais::class);
    }

    public function pilotoEquipe(){
        return $this->hasMany(PilotoEquipe::class);
    }

    public static function equipeAtual($ano_id, $piloto_id){
        
        $equipe = PilotoEquipe::join('equipes', 'piloto_equipes.equipe_id', 'equipes.id')->where('ano_id', $ano_id)
                            ->where('piloto_id', $piloto_id)
                            // ->where('piloto_equipes.flg_ativo', 'S')
                            ->orderBy('piloto_equipes.id', 'DESC')
                            ->first();

        return $equipe;
    }

    static function getInfoCampeonato($temporada_id, $piloto_id){

        $usuario = Auth::user()->id;

        $temporada = Temporada::find($temporada_id);

        $posicaoPiloto = '-';
        $totalPontos = 0;

        $classificacaoPilotos = $temporada->getClassificacao($usuario, $temporada)['resultadoPilotos']; //recebe array da classificação ja montado (as chaves/posições começam no zero)
        foreach($classificacaoPilotos as $key => $item){
            if($item->piloto_id == $piloto_id){
                $totalPontos = $item->total;
                $posicaoPiloto = $key+1; //adiciona 1 na chave do array (que começa com zero) que ja vem ordenado do banco de dados
            }
        }

        return [
            'posicaoPiloto' => $posicaoPiloto,
            'totalPontos' => $posicaoPiloto != '-' ? $totalPontos : '-'
        ];
    }

    public static function getInfoPorEquipe($idPiloto, $equipeId, $posicao_minima_chegada = 1, $posicao_maxima_chegada = 1000, $posicao_minima_largada = 1, $posicao_maxima_largada = 1000){

        $info = Resultado::join('corridas', 'resultados.corrida_id', '=', 'corridas.id')
                                                ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
                                                ->join('equipes', 'equipes.id', 'piloto_equipes.equipe_id')
                                                ->where('resultados.user_id', Auth::user()->id)
                                                ->where('piloto_equipes.piloto_id', $idPiloto)
                                                ->where('equipes.id', $equipeId)
                                                ->where('resultados.largada', '>=', $posicao_minima_largada)
                                                ->where('resultados.largada', '<=', $posicao_maxima_largada)
                                                ->where('corridas.flg_sprint', '<>', 'S')
                                                ->where('resultados.chegada', '>=', $posicao_minima_chegada)
                                                ->where('resultados.chegada', '<=', $posicao_maxima_chegada)
                                                ->count();

        return $info;
    }
}
