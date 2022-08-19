<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
    use HasFactory;

    protected $table = 'equipes';

    public $timestamps = false;

    protected $fillable = ['nome', 'pais_id', 'user_id', 'flg_ativo', 'des_cor'];

    /**relacionamentos */
    public function pais(){
        return $this->belongsTo(Pais::class);
    }

    public function pilotoEquipe(){
        return $this->hasMany(PilotoEquipe::class);
    }
}
