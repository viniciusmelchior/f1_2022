<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Corrida extends Model
{
    use HasFactory;

    protected $table = 'corridas';

    public $timestamps = false;

    protected $fillable = ['temporada_id', 'pista_id', 'ordem','volta_rapida','flg_sprint','condicao_id', 'user_id'];

    /**Relacionamentos */

     public function pista(){
        return $this->belongsTo(Pista::class);
    }

    public function temporada(){
        return $this->belongsTo(Temporada::class);
    }
}
