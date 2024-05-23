<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ForcaPiloto extends Model
{
    use HasFactory;

    protected $table = 'forca_piloto';

    public static function getForcaPiloto($ano_id, $piloto_id){

        $model = ForcaPiloto::where('piloto_id', $piloto_id)
                            ->where('ano_id', $ano_id)
                            ->where('user_id', Auth::user()->id)
                            ->first();

        return isset($model) ? $model->forca : '-';
    }
}
