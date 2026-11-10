<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;

/**
 * MELHORIA 20261110: campeão da classificação exclusiva das sprints de uma temporada finalizada.
 */
class TituloSprint extends Model
{
    protected $table = 'titulos_sprint';

    public $timestamps = false;

    protected $fillable = ['temporada_id', 'user_id', 'pilotoEquipe_id', 'equipe_id'];

    public function pilotoEquipe(){
        return $this->belongsTo(PilotoEquipe::class, 'pilotoEquipe_id', 'id');
    }

    public function equipe(){
        return $this->belongsTo(Equipe::class);
    }
}
