<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ano extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = ['ano', 'user_id', 'flg_ativo'];

    /**Relacionamentos */
    public function pilotoEquipe(){
        return $this->hasMany(PilotoEquipe::class);
    }
}
