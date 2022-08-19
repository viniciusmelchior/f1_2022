<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CondicaoClimatica extends Model
{
    use HasFactory;

    protected $table = 'condicao_climaticas';

    public $timestamps = false;

    protected $fillable = ['descricao', 'user_id'];

    /**relacionamentos */
}
