<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    public $timestamps = false;

    protected $fillable = ['des_nome', 'user_id'];

    /**relacionamentos */

    public function corrida()
    {
        return $this->hasMany(Corrida::class);
    }

    public static function getQtdEventos($evento_id){
        $corridas = Corrida::where('evento_id', $evento_id)
                            ->count();

        return $corridas;
    }

    public static function getUltimoEvento($evento_id){

            $ultimoEvento = Corrida::where('evento_id', $evento_id)
                                    ->where('user_id', Auth::user()->id)
                                    ->where('flg_sprint', 'N')
                                    ->orderBy('id', 'DESC')
                                    ->first();

        return [
            'ultimoEvento' => isset($ultimoEvento->temporada->des_temporada) ? $ultimoEvento->temporada->des_temporada : '-'
        ];
    }

}
