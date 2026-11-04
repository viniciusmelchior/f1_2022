<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;

/**
 * Resumo da corrida extraído dos JSONs (condições, sessão, números gerais).
 */
class CorridaDetalhe extends Model
{
    protected $table = 'corrida_detalhes';

    protected $guarded = ['id'];

    protected $casts = [
        'pista_molhada' => 'boolean',
        'penalidades' => 'boolean',
        'setup_fixo' => 'boolean',
        'data_sessao' => 'date',
    ];

    public function corrida()
    {
        return $this->belongsTo(Corrida::class);
    }

    public function voltaMaisRapidaPilotoEquipe()
    {
        return $this->belongsTo(PilotoEquipe::class, 'volta_mais_rapida_piloto_equipe_id');
    }

    public function vencedorPilotoEquipe()
    {
        return $this->belongsTo(PilotoEquipe::class, 'vencedor_piloto_equipe_id');
    }

    public function polePilotoEquipe()
    {
        return $this->belongsTo(PilotoEquipe::class, 'pole_piloto_equipe_id');
    }
}
