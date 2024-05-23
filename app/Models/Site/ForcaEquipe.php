<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ForcaEquipe extends Model
{
    use HasFactory;

    protected $table = 'forca_equipe';

    public static function getForcaEquipe($ano_id, $equipe_id){

        $model = ForcaEquipe::where('equipe_id', $equipe_id)
                            ->where('ano_id', $ano_id)
                            ->where('user_id', Auth::user()->id)
                            ->first();

        return isset($model) ? $model->forca : '-';
    }
}
