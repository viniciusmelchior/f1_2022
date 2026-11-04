<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;

/**
 * Dados de um piloto em uma corrida (classificação + corrida) extraídos dos JSONs.
 * piloto_equipe_id / piloto_id / equipe_id ficam nulos quando o piloto do arquivo
 * não foi encontrado no grid cadastrado da temporada.
 */
class ResultadoDetalhe extends Model
{
    protected $table = 'resultado_detalhes';

    protected $guarded = ['id'];

    protected $casts = [
        'eh_jogador' => 'boolean',
        'abandonou' => 'boolean',
        'volta_mais_rapida' => 'boolean',
    ];

    public function corrida()
    {
        return $this->belongsTo(Corrida::class);
    }

    public function pilotoEquipe()
    {
        return $this->belongsTo(PilotoEquipe::class);
    }

    public function piloto()
    {
        return $this->belongsTo(Piloto::class);
    }

    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }

    public function voltas()
    {
        return $this->hasMany(ResultadoVolta::class);
    }
}
