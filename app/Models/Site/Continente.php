<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Continente extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function pais(){
        return $this->hasMany(Pais::class);
    }
}
