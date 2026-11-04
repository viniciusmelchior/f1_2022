<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;

/**
 * JSON original de uma sessão do Content Manager (largada, chegada ou grid invertido).
 */
class CorridaSessao extends Model
{
    protected $table = 'corrida_sessoes';

    public const TIPOS = ['largada', 'chegada', 'grid_invertido'];

    protected $fillable = ['corrida_id', 'user_id', 'tipo', 'nome_arquivo', 'hash', 'conteudo'];

    public function corrida()
    {
        return $this->belongsTo(Corrida::class);
    }
}
