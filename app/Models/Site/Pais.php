<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;

    protected $table = 'paises';

    public $timestamps = false;

    protected $fillable = ['des_nome', 'user_id','imagem'];

    /**relacionamentos */

    public function pilotos(){
        return $this->hasMany(Piloto::class);
    }

    public function equipes(){
        return $this->hasMany(Equipe::class);
    }
    
    public function pistas(){
        return $this->hasMany(Pista::class);
    }
}
