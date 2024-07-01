<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Corrida extends Model
{
    use HasFactory;

    protected $table = 'corridas';

    public $timestamps = false;

    protected $fillable = ['temporada_id', 'pista_id', 'ordem','volta_rapida','flg_sprint','condicao_id', 'user_id','qtd_safety_car','dificuldade_ia', 'observacoes','flg_super_corrida','updated_at','evento_id'];

    /**Relacionamentos */

    public function pista(){
        return $this->belongsTo(Pista::class);
    }

    public function evento(){
        return $this->belongsTo(Evento::class);
    }

    public function temporada(){
        return $this->belongsTo(Temporada::class);
    }

    public function condicao(){
        return $this->belongsTo(CondicaoClimatica::class);
    }
    
    public function resultado(){
        return $this->hasOne(Resultado::class);
    }

    public static function getInfoCorrida($corrida_id){

        $polePosition = '-';
        $vencedor = '-';
        $totAbandonos = '-';

        $resultado = Resultado::where('corrida_id', $corrida_id)->get();

        if($resultado){
            $polePosition = $resultado->where('largada', 1)->first(); 
            $polePosition = isset($polePosition) ? $polePosition->pilotoEquipe->piloto->nomeCompleto() : '-';

            $vencedor = $resultado->where('chegada', 1)->first(); 
            $vencedor = isset($vencedor) ? $vencedor->pilotoEquipe->piloto->nomeCompleto() : '-';

            $totAbandonos = $resultado->where('flg_abandono', 'S')->count();
        }

        // dd($polePosition);

        // dd( $polePosition,$vencedor, $totAbandonos);

        return [
            'polePosition' => $polePosition,
            'vencedor' => $vencedor,
            'totAbandonos' => $totAbandonos
        ];
    }
}
