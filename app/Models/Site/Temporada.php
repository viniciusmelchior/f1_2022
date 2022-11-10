<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temporada extends Model
{
    use HasFactory;

    protected $table = 'temporadas';

    public $timestamps = false;

    protected $fillable = ['des_temporada', 'user_id', 'ano_id', 'flg_finalizada'];

    /*relacionamentos */
    public function ano(){
        return $this->belongsTo(Ano::class);
    }

    public function corrida(){
        return $this->hasMany(Corrida::class);
    }
}
