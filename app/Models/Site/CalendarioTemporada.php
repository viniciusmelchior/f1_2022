<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;

/**
 * MELHORIA 20261119: temporada em planejamento (calendário). Nome livre, sem ligação com "temporadas".
 */
class CalendarioTemporada extends Model
{
    protected $table = 'calendario_temporadas';

    protected $fillable = ['user_id', 'nome', 'anotacoes'];

    public function etapas(){
        return $this->hasMany(CalendarioEtapa::class, 'calendario_temporada_id')->orderBy('ordem')->orderBy('id');
    }
}
