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
