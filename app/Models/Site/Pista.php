<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pista extends Model
{
    use HasFactory;

    protected $table = 'pistas';

    public $timestamps = false;

    protected $fillable = ['nome', 'pais_id', 'user_id', 'flg_ativo','qtd_carros','tamanho_km','qtd_voltas'];

    /**relacionamentos */

    public function pais(){
        return $this->belongsTo(Pais::class);
    }

     public function corrida(){
        return $this->hasMany(Corrida::class);
    }

    public static function getQtdCorridasSprints($pista_id){
        $corridas = Corrida::with('pista')
                             ->whereHas('pista', function($query) use ($pista_id) {
                                 $query->where('id', $pista_id);
                             })
                             ->where('flg_sprint', '<>', 'N')
                             ->count();
 
         return $corridas;
     }
 
     public static function getQtdCorridas($pista_id){
         $corridas = Corrida::with('pista')
                             ->whereHas('pista', function($query) use ($pista_id) {
                                 $query->where('id', $pista_id);
                             })
                             ->where('flg_sprint', '<>', 'S')
                             ->count();
 
        return $corridas;
     }

}
