<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotoEquipe extends Model
{
    use HasFactory;

    protected $table = 'piloto_equipes';

    public $timestamps = false;

    protected $fillable = ['piloto_id', 'equipe_id', 'user_id', 'ano_id', 'flg_ativo'];

    /**relacionamentos */

    public function ano(){
        return $this->belongsTo(Ano::class);
    }

    public function piloto(){
        return $this->belongsTo(Piloto::class);
    }

    public function equipe(){
        return $this->belongsTo(Equipe::class);
    }

    public function resultado(){
        return $this->hasMany(Resultado::class);
    }
}
