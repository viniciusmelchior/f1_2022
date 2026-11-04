<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;

/**
 * Uma volta de um piloto (sessão "classificacao" ou "corrida").
 */
class ResultadoVolta extends Model
{
    protected $table = 'resultado_voltas';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'entrada_box' => 'boolean',
        'neutralizada' => 'boolean',
    ];

    public function resultadoDetalhe()
    {
        return $this->belongsTo(ResultadoDetalhe::class);
    }
}
