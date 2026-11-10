<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MELHORIA 20261110 (playoff): torneio mata-mata dentro do campeonato.
 *  - temporadas.flg_playoff  => 'S' habilita o playoff na temporada
 *  - temporadas.qtd_corridas => total de corridas principais (classificatórias = qtd_corridas - 9)
 *  - titulos_playoff         => campeão do playoff de cada temporada finalizada
 *    (recalculada junto com titulos / titulos_sprint em TemporadaController::recalcularTitulos()).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('temporadas', function (Blueprint $table) {
            $table->char('flg_playoff', 1)->default('N');
            $table->unsignedSmallInteger('qtd_corridas')->nullable();
        });

        Schema::create('titulos_playoff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temporada_id')->constrained('temporadas')->cascadeOnDelete();
            $table->unsignedBigInteger('pilotoEquipe_id');
            $table->foreign('pilotoEquipe_id')->references('id')->on('piloto_equipes');
            $table->foreignId('equipe_id')->constrained('equipes');
            $table->unsignedBigInteger('vice_pilotoEquipe_id')->nullable();
            $table->foreign('vice_pilotoEquipe_id')->references('id')->on('piloto_equipes');
            $table->foreignId('user_id')->constrained('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('titulos_playoff');

        Schema::table('temporadas', function (Blueprint $table) {
            $table->dropColumn(['flg_playoff', 'qtd_corridas']);
        });
    }
};
