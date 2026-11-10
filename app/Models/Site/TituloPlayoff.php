<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;

/**
 * MELHORIA 20261110 (playoff): campeão do playoff (mata-mata) de uma temporada finalizada com playoff habilitado.
 */
class TituloPlayoff extends Model
{
    protected $table = 'titulos_playoff';

    public $timestamps = false;

    protected $fillable = ['temporada_id', 'user_id', 'pilotoEquipe_id', 'equipe_id', 'vice_pilotoEquipe_id'];

    public function pilotoEquipe(){
        return $this->belongsTo(PilotoEquipe::class, 'pilotoEquipe_id', 'id');
    }

    public function vicePilotoEquipe(){
        return $this->belongsTo(PilotoEquipe::class, 'vice_pilotoEquipe_id', 'id');
    }

    public function equipe(){
        return $this->belongsTo(Equipe::class);
    }

    public function temporada(){
        return $this->belongsTo(Temporada::class);
    }
}
