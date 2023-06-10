<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pista extends Model
{
    use HasFactory;

    protected $table = 'pistas';

    public $timestamps = false;

    protected $fillable = ['nome', 'pais_id', 'user_id', 'flg_ativo','qtd_carros'];

    /**relacionamentos */

    public function pais(){
        return $this->belongsTo(Pais::class);
    }

     public function corrida(){
        return $this->hasMany(Corrida::class);
    }

}
