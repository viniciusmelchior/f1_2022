<?php

use App\Http\Controllers\Site\AjaxController;
use App\Http\Controllers\Site\PaisesController;
use App\Http\Controllers\Site\PilotoController;
use App\Http\Controllers\Site\EquipeController;
use App\Http\Controllers\Site\PistaController;
use App\Http\Controllers\Site\PilotoEquipeController;
use App\Http\Controllers\Site\CondicaoClimaticaController;
use App\Http\Controllers\Site\CorridaController;
use App\Http\Controllers\Site\AnoController;
use App\Http\Controllers\Site\ResultadoController;
use App\Http\Controllers\Site\TemporadaController;
use App\Http\Controllers\Site\PDFController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\DataCollector\AjaxDataCollector;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('landingPage.index');
})->name('landingPage');

Route::get('/dashboard', function () {
    return view('site.dashboard.index');
})->name('dashboard')->middleware('auth');

Route::get('/home', function () {
    return view('home.home');
})->middleware('auth')->name('home');

/**Rotas de Login e Logout */
Route::get('/register', [UserController::class, 'create'])->name('user.register');
Route::post('/users', [UserController::class, 'store'])->name('user.store');
Route::post('/users/logout', [UserController::class, 'logout'])->name('user.logout');
Route::get('/users/login', [UserController::class, 'login'])->name('login');
Route::post('/users/authenticate', [UserController::class, 'authenticate'])->name('user.authenticate');

                                    /**rotas admin */

/** Países */
route::get('/paises/create', [PaisesController::class, 'create'])->name('paises.create')->middleware('auth');
route::post('/paises/store', [PaisesController::class, 'store'])->name('paises.store')->middleware('auth');
route::get('/paises/index', [PaisesController::class, 'index'])->name('paises.index')->middleware('auth');
route::get('/paises/edit/{id}', [PaisesController::class, 'edit'])->name('paises.edit')->middleware('auth');
route::put('/paises/update/{id}', [PaisesController::class, 'update'])->name('paises.update')->middleware('auth');
route::get('/paises/delete/{id}', [PaisesController::class, 'destroy'])->name('paises.delete')->middleware('auth');

/**Pilotos */
route::get('/pilotos/create', [PilotoController::class, 'create'])->name('pilotos.create')->middleware('auth');
route::post('/pilotos/store', [PilotoController::class, 'store'])->name('pilotos.store')->middleware('auth');
route::get('/pilotos/index', [PilotoController::class, 'index'])->name('pilotos.index')->middleware('auth');
route::get('/pilotos/edit/{id}', [PilotoController::class, 'edit'])->name('pilotos.edit')->middleware('auth');
route::get('/pilotos/show/{id}', [PilotoController::class, 'show'])->name('pilotos.show')->middleware('auth');
route::put('/pilotos/update/{id}', [PilotoController::class, 'update'])->name('pilotos.update')->middleware('auth');
route::get('/pilotos/delete', [PilotoController::class, 'destroy'])->name('pilotos.delete')->middleware('auth');

/**Equipes */
route::get('/equipes/create', [EquipeController::class, 'create'])->name('equipes.create')->middleware('auth');
route::post('/equipes/store', [EquipeController::class, 'store'])->name('equipes.store')->middleware('auth');
route::get('/equipes/index', [EquipeController::class, 'index'])->name('equipes.index')->middleware('auth');
route::get('/equipes/edit/{id}', [EquipeController::class, 'edit'])->name('equipes.edit')->middleware('auth');
route::get('/equipes/show/{id}', [EquipeController::class, 'show'])->name('equipes.show')->middleware('auth');
route::put('/equipes/update/{id}', [EquipeController::class, 'update'])->name('equipes.update')->middleware('auth');
route::get('/equipes/delete/{id}', [EquipeController::class, 'destroy'])->name('equipes.delete')->middleware('auth');

/**Pistas */
route::get('/pistas/create', [PistaController::class, 'create'])->name('pistas.create')->middleware('auth');
route::post('/pistas/store', [PistaController::class, 'store'])->name('pistas.store')->middleware('auth');
route::get('/pistas/index', [PistaController::class, 'index'])->name('pistas.index')->middleware('auth');
route::get('/pistas/edit/{id}', [PistaController::class, 'edit'])->name('pistas.edit')->middleware('auth');
route::put('/pistas/update/{id}', [PistaController::class, 'update'])->name('pistas.update')->middleware('auth');
route::get('/pistas/delete/{id}', [PistaController::class, 'destroy'])->name('pistas.delete')->middleware('auth');

/**Relação de Pilotos e Equipes (montagem das duplas de pilotos por equipe) */
route::get('/pilotoEquipe/create', [PilotoEquipeController::class, 'create'])->name('pilotoEquipe.create')->middleware('auth');
route::post('/pilotoEquipe/store', [PilotoEquipeController::class, 'store'])->name('pilotoEquipe.store')->middleware('auth');
route::get('/pilotoEquipe/index', [PilotoEquipeController::class, 'index'])->name('pilotoEquipe.index')->middleware('auth');
route::get('/pilotoEquipe/edit/{id}', [PilotoEquipeController::class, 'edit'])->name('pilotoEquipe.edit')->middleware('auth');
route::put('/pilotoEquipe/update/{id}', [PilotoEquipeController::class, 'update'])->name('pilotoEquipe.update')->middleware('auth');
route::get('/pilotoEquipe/delete/{id}', [PilotoEquipeController::class, 'destroy'])->name('pilotoEquipe.delete')->middleware('auth');

/**Condições Climáticas */
route::get('/condicaoClimatica/create', [CondicaoClimaticaController::class, 'create'])->name('condicaoClimatica.create')->middleware('auth');
route::post('/condicaoClimatica/store', [CondicaoClimaticaController::class, 'store'])->name('condicaoClimatica.store')->middleware('auth');
route::get('/condicaoClimatica/index', [CondicaoClimaticaController::class, 'index'])->name('condicaoClimatica.index')->middleware('auth');
route::get('/condicaoClimatica/edit/{id}', [CondicaoClimaticaController::class, 'edit'])->name('condicaoClimatica.edit')->middleware('auth');
route::put('/condicaoClimatica/update/{id}', [CondicaoClimaticaController::class, 'update'])->name('condicaoClimatica.update')->middleware('auth');
route::get('/condicaoClimatica/delete/{id}', [CondicaoClimaticaController::class, 'destroy'])->name('condicaoClimatica.delete')->middleware('auth');

/**Pistas */
route::get('/temporadas/create', [TemporadaController::class, 'create'])->name('temporadas.create')->middleware('auth');
route::post('/temporadas/store', [TemporadaController::class, 'store'])->name('temporadas.store')->middleware('auth');
route::get('/temporadas/index', [TemporadaController::class, 'index'])->name('temporadas.index')->middleware('auth');
route::get('/temporadas/edit/{id}', [TemporadaController::class, 'edit'])->name('temporadas.edit')->middleware('auth');
route::put('/temporadas/update/{id}', [TemporadaController::class, 'update'])->name('temporadas.update')->middleware('auth');
route::get('/temporadas/delete/{id}', [TemporadaController::class, 'destroy'])->name('temporadas.delete')->middleware('auth');

/**Corridas */
route::get('/corridas/create', [CorridaController::class, 'create'])->name('corridas.create')->middleware('auth');
route::post('/corridas/store', [CorridaController::class, 'store'])->name('corridas.store')->middleware('auth');
route::get('/corridas/index/{id}', [CorridaController::class, 'index'])->name('corridas.index')->middleware('auth');
route::get('/corridas/edit/{id}', [CorridaController::class, 'edit'])->name('corridas.edit')->middleware('auth');
route::put('/corridas/update/{id}', [CorridaController::class, 'update'])->name('corridas.update')->middleware('auth');
route::get('/corridas/delete/{id}', [CorridaController::class, 'destroy'])->name('corridas.delete')->middleware('auth');

route::get('/corridas/adicionar/{id}', [CorridaController::class, 'adicionar'])->name('corridas.adicionar')->middleware('auth');
route::post('/corridas/alterar/{id}', [CorridaController::class, 'alterar'])->name('corridas.alterar')->middleware('auth');


/**Corridas */
route::get('/resultados/edit/{id}', [ResultadoController::class, 'edit'])->name('resultados.edit')->middleware('auth');
route::post('/resultados/update/{id}', [ResultadoController::class, 'update'])->name('resultados.update')->middleware('auth');
route::get('/resultados/show/{id}', [ResultadoController::class, 'show'])->name('resultados.show')->middleware('auth');

/**Requisições Ajax */
route::post('/ajax/classificacaoGeralPorTemporada', [AjaxController::class, 'classificacaoPorTemporada'])->name('ajax.classificacaoGeralPorTemporada')->middleware('auth');

/**Rotas Excel */
Route::get('pilotos/export/{id}', [PilotoController::class, 'export'])->name('pilotos.export');

/**Disparo de Email */
Route::get('send-email-pdf', [PDFController::class, 'index']);

/**Anos */
route::get('/anos/create', [AnoController::class, 'create'])->name('anos.create')->middleware('auth');
route::post('/anos/store', [AnoController::class, 'store'])->name('anos.store')->middleware('auth');
route::get('/anos/index/', [AnoController::class, 'index'])->name('anos.index')->middleware('auth');
route::get('/anos/edit/{id}', [AnoController::class, 'edit'])->name('anos.edit')->middleware('auth');
route::put('/anos/update/{id}', [AnoController::class, 'update'])->name('anos.update')->middleware('auth');
route::get('/anos/delete/{id}', [AnoController::class, 'destroy'])->name('anos.delete')->middleware('auth');



