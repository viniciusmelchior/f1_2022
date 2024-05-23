<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skin extends Model
{
    use HasFactory;

    protected $table = 'skins';

    public function equipe(){
        return $this->belongsTo(Equipe::class);
    }
}
