<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Titulo extends Model
{
    use HasFactory;

    protected $table = 'titulos';

    public $timestamps = false;

    protected $fillable = ['temporada_id', 'user_id', 'pilotoEquipe_id', 'equipe_id'];

     /**Relacionamentos */

    public function pilotoEquipe(){
        return $this->belongsTo(PilotoEquipe::class, 'pilotoEquipe_id', 'id');
    }

    public function equipe(){
        return $this->belongsTo(Equipe::class);
    }
}
