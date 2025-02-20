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
use App\Http\Controllers\Site\EventoController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\ResultadoController;
use App\Http\Controllers\Site\TemporadaController;
use App\Http\Controllers\Site\PDFController;
use App\Http\Controllers\Site\SkinController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Site\EstatisticasPilotosEquipes;
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
//teste comentario
Route::get('/', function () {
    return view('landingPage.index');
})->name('landingPage')->middleware('auth');

Route::get('/dashboard', function () {
    return view('site.dashboard.index');
})->name('dashboard')->middleware('auth');

// Route::get('/home', function () {
//     return view('home.home');
// })->middleware('auth')->name('home');

Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

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
route::get('/paises/show/{id}', [PaisesController::class, 'show'])->name('paises.show')->middleware('auth');
route::get('/paises/delete', [PaisesController::class, 'destroy'])->name('paises.delete')->middleware('auth');
route::post('/paises/ajaxGetChegadasPilotos', [PaisesController::class, 'ajaxGetChegadasPilotos'])->name('paises.ajaxGetChegadasPilotos')->middleware('auth');
route::post('/paises/ajaxGetLargadasPilotos', [PaisesController::class, 'ajaxGetLargadasPilotos'])->name('paises.ajaxGetLargadasPilotos')->middleware('auth');
route::post('/paises/ajaxGetChegadasEquipes', [PaisesController::class, 'ajaxGetChegadasEquipes'])->name('paises.ajaxGetChegadasEquipes')->middleware('auth');
route::post('/paises/ajaxGetLargadasEquipes', [PaisesController::class, 'ajaxGetLargadasEquipes'])->name('paises.ajaxGetLargadasEquipes')->middleware('auth');

/**Pilotos */
route::get('/pilotos/create', [PilotoController::class, 'create'])->name('pilotos.create')->middleware('auth');
route::post('/pilotos/store', [PilotoController::class, 'store'])->name('pilotos.store')->middleware('auth');
route::get('/pilotos/index', [PilotoController::class, 'index'])->name('pilotos.index')->middleware('auth');
route::get('/pilotos/edit/{id}', [PilotoController::class, 'edit'])->name('pilotos.edit')->middleware('auth');
route::get('/pilotos/show/{id}', [PilotoController::class, 'show'])->name('pilotos.show')->middleware('auth');
route::get('/pilotos/comparativo', [PilotoController::class, 'comparativo'])->name('pilotos.comparativo')->middleware('auth');
route::put('/pilotos/update/{id}', [PilotoController::class, 'update'])->name('pilotos.update')->middleware('auth');
route::get('/pilotos/delete', [PilotoController::class, 'destroy'])->name('pilotos.delete')->middleware('auth');
route::get('/pilotos/relacaoForcas/index', [PilotoController::class, 'relacaoForcasIndex'])->name('pilotos.relacaoForcas.index')->middleware('auth');
route::get('/pilotos/relacaoForcas/listagemPilotos/{temporada_id}', [PilotoController::class, 'relacaoForcasIndexListagemPilotos'])->name('pilotos.relacaoForcas.listagemPilotos')->middleware('auth');
route::get('/pilotos/relacaoForcas/edit/{ano_id}/{temporada_id}', [PilotoController::class, 'relacaoForcasEdit'])->name('pilotos.relacaoForcas.edit')->middleware('auth');
route::post('/pilotos/relacaoForcas/update', [PilotoController::class, 'relacaoForcasUpdate'])->name('pilotos.relacaoForcas.update')->middleware('auth');

/**Equipes */
route::get('/equipes/create', [EquipeController::class, 'create'])->name('equipes.create')->middleware('auth');
route::post('/equipes/store', [EquipeController::class, 'store'])->name('equipes.store')->middleware('auth');
route::get('/equipes/index', [EquipeController::class, 'index'])->name('equipes.index')->middleware('auth');
route::get('/equipes/edit/{id}', [EquipeController::class, 'edit'])->name('equipes.edit')->middleware('auth');
route::get('/equipes/show/{id}', [EquipeController::class, 'show'])->name('equipes.show')->middleware('auth');
route::put('/equipes/update/{id}', [EquipeController::class, 'update'])->name('equipes.update')->middleware('auth');
route::get('/equipes/delete/{id}', [EquipeController::class, 'destroy'])->name('equipes.delete')->middleware('auth');
route::get('/equipes/relacaoForcas/index', [EquipeController::class, 'relacaoForcasIndex'])->name('equipes.relacaoForcas.index')->middleware('auth');
route::get('/equipes/relacaoForcas/listagemEquipes/{temporada_id}', [EquipeController::class, 'relacaoForcasIndexListagemEquipes'])->name('equipes.relacaoForcas.listagemEquipes')->middleware('auth');
route::get('/equipes/relacaoForcas/edit/{ano_id}/{temporada_id}', [EquipeController::class, 'relacaoForcasEdit'])->name('equipes.relacaoForcas.edit')->middleware('auth');
route::post('/equipes/relacaoForcas/update', [EquipeController::class, 'relacaoForcasUpdate'])->name('equipes.relacaoForcas.update')->middleware('auth');

route::get('/skin/index', [SkinController::class, 'index'])->name('skins.index')->middleware('auth');
route::get('/skin/create', [SkinController::class, 'create'])->name('skins.create')->middleware('auth');
route::post('/skin/store', [SkinController::class, 'store'])->name('skins.store')->middleware('auth');
route::put('/skin/update/{id}', [SkinController::class, 'update'])->name('skins.update')->middleware('auth');
route::get('/skin/edit/{id}', [SkinController::class, 'edit'])->name('skins.edit')->middleware('auth');

route::post('/ajax/ajaxGetStatsEquipePorTemporada', [AjaxController::class, 'ajaxGetStatsEquipePorTemporada'])->name('ajax.ajaxGetStatsEquipePorTemporada')->middleware('auth');

route::post('/ajax/ajaxGetStatsPilotoPorTemporada', [AjaxController::class, 'ajaxGetStatsPilotoPorTemporada'])->name('ajax.ajaxGetStatsPilotoPorTemporada')->middleware('auth');

/**Pistas */
route::get('/pistas/create', [PistaController::class, 'create'])->name('pistas.create')->middleware('auth');
route::post('/pistas/store', [PistaController::class, 'store'])->name('pistas.store')->middleware('auth');
route::get('/pistas/index', [PistaController::class, 'index'])->name('pistas.index')->middleware('auth');
route::get('/pistas/edit/{id}', [PistaController::class, 'edit'])->name('pistas.edit')->middleware('auth');
route::put('/pistas/update/{id}', [PistaController::class, 'update'])->name('pistas.update')->middleware('auth');
route::get('/pistas/show/{id}', [PistaController::class, 'show'])->name('pistas.show')->middleware('auth');
route::get('/pistas/delete/{id}', [PistaController::class, 'destroy'])->name('pistas.delete')->middleware('auth');

/**Adicionar Autor */
route::get('/pistas/adicionarAutor', [PistaController::class, 'adicionarAutor'])->name('pistas.adicionarAutor')->middleware('auth');

/**Criação de eventos (grand prix ou grande premio) - GP de Madrid (circuito urbano de madrid) Sprint Madrid (circuito urbano de madrid)*/
route::get('/eventos/index', [EventoController::class, 'index'])->name('eventos.index')->middleware('auth');
route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create')->middleware('auth');
route::post('/eventos/store', [EventoController::class, 'store'])->name('eventos.store')->middleware('auth');
route::get('/eventos/edit/{id}', [EventoController::class, 'edit'])->name('eventos.edit')->middleware('auth');
route::put('/eventos/update/{id}', [EventoController::class, 'update'])->name('eventos.update')->middleware('auth');
route::get('/eventos/show/{id}', [EventoController::class, 'show'])->name('eventos.show')->middleware('auth');
route::get('/eventos/delete', [EventoController::class, 'destroy'])->name('eventos.delete')->middleware('auth');

/**Relação de Pilotos e Equipes (montagem das duplas de pilotos por equipe) */
route::get('/pilotoEquipe/create', [PilotoEquipeController::class, 'create'])->name('pilotoEquipe.create')->middleware('auth');
route::post('/pilotoEquipe/store', [PilotoEquipeController::class, 'store'])->name('pilotoEquipe.store')->middleware('auth');
route::post('/pilotoEquipe/replicarPilotoEquipe', [PilotoEquipeController::class, 'replicarPilotoEquipe'])->name('pilotoEquipe.replicarPilotoEquipe')->middleware('auth');
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
route::get('/temporadas/classificacao/{id}', [TemporadaController::class, 'classificacao'])->name('temporadas.classificacao')->middleware('auth');
route::put('/temporadas/update/{id}', [TemporadaController::class, 'update'])->name('temporadas.update')->middleware('auth');
route::get('/temporadas/delete/{id}', [TemporadaController::class, 'destroy'])->name('temporadas.delete')->middleware('auth');
route::get('/temporadas/resultados/{id}/{porPontuacao?}', [TemporadaController::class, 'resultados'])->name('temporadas.resultados')->middleware('auth');

/**Corridas */
route::get('/corridas/create', [CorridaController::class, 'create'])->name('corridas.create')->middleware('auth');
route::post('/corridas/store', [CorridaController::class, 'store'])->name('corridas.store')->middleware('auth');
route::get('/corridas/index/{id}', [CorridaController::class, 'index'])->name('corridas.index')->middleware('auth');
// route::get('/corridas/edit/{id}', [CorridaController::class, 'edit'])->name('corridas.edit')->middleware('auth');
// route::put('/corridas/update/{id}', [CorridaController::class, 'update'])->name('corridas.update')->middleware('auth');
// route::get('/corridas/delete/{id}', [CorridaController::class, 'destroy'])->name('corridas.delete')->middleware('auth');
route::get('/corridas/delete/', [CorridaController::class, 'destroy'])->name('corridas.delete')->middleware('auth');

route::get('/corridas/adicionar/{id}', [CorridaController::class, 'adicionar'])->name('corridas.adicionar')->middleware('auth');
route::get('/corridas/edit/{id}/{corrida}', [CorridaController::class, 'edit'])->name('corridas.edit')->middleware('auth');
route::post('/corridas/store/{id}', [CorridaController::class, 'store'])->name('corridas.alterar')->middleware('auth');
route::post('/corridas/update/{id}', [CorridaController::class, 'update'])->name('corridas.update')->middleware('auth');


/**Corridas */
route::get('/resultados/edit/{id}', [ResultadoController::class, 'edit'])->name('resultados.edit')->middleware('auth');
route::post('/resultados/update/{id}', [ResultadoController::class, 'update'])->name('resultados.update')->middleware('auth');
route::get('/resultados/show/{id}', [ResultadoController::class, 'show'])->name('resultados.show')->middleware('auth');

/**Requisições Ajax */
route::post('/ajax/classificacaoGeralPorTemporada', [AjaxController::class, 'classificacaoPorTemporada'])->name('ajax.classificacaoGeralPorTemporada')->middleware('auth');

route::post('/ajax/comparativos', [AjaxController::class, 'comparativos'])->name('ajax.comparativos')->middleware('auth');
route::post('/ajax/getPilotosPorTemporada', [AjaxController::class, 'getPilotosPorTemporada'])->name('ajax.getPilotosPorTemporada')->middleware('auth');
route::get('/estudos', [AjaxController::class, 'estudos'])->name('estudos')->middleware('auth');

/**Montagem das tabelas de vitorias por pilotos e equipes (dinamicamente via ajax) */
route::post('/ajax/ajaxGetVitoriasPilotoPorTemporada', [HomeController::class, 'ajaxGetVitoriasPilotoPorTemporada'])->name('ajax.ajaxGetVitoriasPilotoPorTemporada')->middleware('auth');

route::post('/ajax/ajaxGetVitoriasEquipePorTemporada', [HomeController::class, 'ajaxGetVitoriasEquipesPorTemporada'])->name('ajax.ajaxGetVitoriasEquipesPorTemporada')->middleware('auth');

route::post('/ajax/ajaxGetPolesPilotosPorTemporada', [HomeController::class, 'ajaxGetPolesPilotosPorTemporada'])->name('ajax.ajaxGetPolesPilotosPorTemporada')->middleware('auth');

route::post('/ajax/ajaxGetPolesEquipesPorTemporada', [HomeController::class, 'ajaxGetPolesEquipesPorTemporada'])->name('ajax.ajaxGetPolesEquipesPorTemporada')->middleware('auth');

/**montagem da nova tela de HOME 16/08/2024 */
route::post('/ajax/url_chegada_pilotos', [HomeController::class, 'chegada_pilotos'])->name('url_chegada_pilotos')->middleware('auth');
route::post('/ajax/url_chegada_equipes', [HomeController::class, 'chegada_equipes'])->name('url_chegada_equipes')->middleware('auth');
route::post('/ajax/url_largada_pilotos', [HomeController::class, 'largada_pilotos'])->name('url_largada_pilotos')->middleware('auth');
route::post('/ajax/url_largada_equipes', [HomeController::class, 'largada_equipes'])->name('url_largada_equipes')->middleware('auth');

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

/**Classificação após corrida */
route::post('/fetch/getClassificacaoAposCorrida', [TemporadaController::class, 'getClassificacaoAposCorrida'])->name('fetch.getClassificacaoAposCorrida')->middleware('auth');

/**
 * Listagem de Vitorias por Piloto
 */
route::get('/visualizarVitoriasPiloto/{piloto_id}', [HomeController::class, 'visualizarVitoriasPiloto'])->name('visualizarVitoriasPiloto')->middleware('auth');

route::post('/ajax/ajaxGetPodiosPilotoPorTemporada', [HomeController::class, 'ajaxGetPodiosPilotosPorTemporada'])->name('ajax.ajaxGetPodiosPilotoPorTemporada')->middleware('auth');

route::post('/ajax/ajaxGetPodiosEquipesPorTemporada', [HomeController::class, 'ajaxGetPodiosEquipesPorTemporada'])->name('ajax.ajaxGetPodiosEquipesPorTemporada')->middleware('auth');

route::post('/ajax/ajaxGetChegadasPilotosPorTemporada', [HomeController::class, 'ajaxGetChegadasPilotosPorTemporada'])->name('ajax.ajaxGetChegadasPilotosPorTemporada')->middleware('auth');
route::post('/ajax/ajaxGetChegadasEquipesPorTemporada', [HomeController::class, 'ajaxGetChegadasEquipesPorTemporada'])->name('ajax.ajaxGetChegadasEquipesPorTemporada')->middleware('auth');

route::get('tempos', function(){
    return view('site.listagemTempos.index');
})->name('tempos')->middleware('auth');


/**ESTUDOS PAGINACAO */
// route::get('/allDrivers', [PilotoController::class, 'listAllDrivers'])->name('allDrivers');
route::get('/estatisticas', [HomeController::class, 'indexEstatisticas'])->name('estatisticas');
route::get('/teste', [HomeController::class, 'teste'])->name('teste');
route::post('/buscaResultadosCorrida', [HomeController::class, 'buscaResultadosCorrida'])->name('buscaResultadosCorrida');
// route::post('/getAllDrivers', [PilotoController::class, 'getAllDrivers'])->name('getAllDrivers');

/** 19/02/2025 - tela de estatisticas de pilotos e equipes */
route::get('/estatisticasPilotosEquipes/index', [EstatisticasPilotosEquipes::class, 'index'])->name('estatisticas.pilotos.equipes.index');
route::post('/estatisticasPilotosEquipes/buscar', [EstatisticasPilotosEquipes::class, 'buscar'])->name('estatisticas.pilotos.equipes.buscar');


