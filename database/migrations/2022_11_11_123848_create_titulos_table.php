<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('titulos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temporada_id');
            $table->unsignedBigInteger('pilotoEquipe_id');
            $table->unsignedBigInteger('equipe_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('temporada_id')->references('id')->on('temporadas');
            $table->foreign('pilotoEquipe_id')->references('id')->on('piloto_equipes');
            $table->foreign('equipe_id')->references('id')->on('equipes');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('titulos');
    }
};
