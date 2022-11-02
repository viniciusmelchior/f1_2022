<?php

use App\Models\Site\Piloto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/testes', function (Request $request) {
    return Piloto::with('pais')->where('user_id', 1)->get();
});

Route::get('/classificacao', function (Request $request) {
            return DB::select('select piloto_id, concat(pilotos.nome, " ", pilotos.sobrenome) as nome, equipes.nome as equipe, sum(pontuacao) as total from resultados
            join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
            join pilotos on pilotos.id = piloto_equipes.piloto_id
            join equipes on equipes.id = piloto_equipes.equipe_id
            join corridas on corridas.id = resultados.corrida_id
            join temporadas on temporadas.id = corridas.temporada_id
            where temporadas.id = 1
            and resultados.user_id = 1
            group by piloto_equipes.piloto_id
            order by total desc');
});
