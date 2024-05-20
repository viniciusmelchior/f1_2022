<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Pais extends Model
{
    use HasFactory;

    protected $table = 'paises';

    public $timestamps = false;

    protected $fillable = ['des_nome', 'user_id','imagem', 'continente_id'];

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

    public function continente(){
        return $this->belongsTo(Continente::class);
    }

    public static function getQtdCorridasSprints($pais_id){
       $corridas = Corrida::with('pista')
                            ->whereHas('pista', function($query) use ($pais_id) {
                                $query->where('pais_id', $pais_id);
                            })
                            ->where('flg_sprint', '<>', 'N')
                            ->count();

        return $corridas;
    }

    public static function getQtdCorridas($pais_id){
        $corridas = Corrida::with('pista')
                            ->whereHas('pista', function($query) use ($pais_id) {
                                $query->where('pais_id', $pais_id);
                            })
                            ->where('flg_sprint', '<>', 'S')
                            ->count();

        return $corridas;
    }

    public static function getUltimaCorrida($pais_id){

        $ultimaCorrida = Corrida::with('pista')
                                ->whereHas('pista', function ($query) use ($pais_id) {
                                    $query->where('pais_id', $pais_id);
                                })
                                ->where('user_id', Auth::user()->id)
                                ->where('flg_sprint', 'N')
                                ->orderBy('id', 'DESC')
                                ->first();

    return [
        'ultimaCorrida' => isset($ultimaCorrida->temporada->des_temporada) ? $ultimaCorrida->temporada->des_temporada : '-'
    ];
}
  

}
