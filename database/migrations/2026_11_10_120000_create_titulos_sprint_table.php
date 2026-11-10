<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MELHORIA 20261110: campeões da classificação exclusiva das sprints (pilotos e equipes).
 * Mesma estrutura da tabela "titulos"; recalculada junto com ela em TemporadaController::recalcularTitulos().
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('titulos_sprint', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temporada_id')->constrained('temporadas')->cascadeOnDelete();
            $table->unsignedBigInteger('pilotoEquipe_id');
            $table->foreign('pilotoEquipe_id')->references('id')->on('piloto_equipes');
            $table->foreignId('equipe_id')->constrained('equipes');
            $table->foreignId('user_id')->constrained('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('titulos_sprint');
    }
};
