<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagensCorrida extends Model
{
    use HasFactory;

    protected $table = 'imagens_corridas';

    protected $fillable = [
        'corrida_id',
        'imagem',
        'user_id'
    ];

    public static function getImagensCorrida($corrida_id){

        return $imagens_corrida = ImagensCorrida::where('corrida_id', $corrida_id)->get();
        
    }
}
