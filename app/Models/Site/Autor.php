<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autor extends Model
{
    use HasFactory;

    protected $table = 'autores';

    public $timestamps = false;

    protected $fillable = ['nome'];

    public function pista()
    {
        return $this->hasMany(Pista::class);
    }
}
