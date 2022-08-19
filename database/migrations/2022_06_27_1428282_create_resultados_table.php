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
        Schema::create('resultados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corrida_id');
            $table->unsignedBigInteger('pilotoEquipe_id');
            $table->integer('largada');
            $table->integer('chegada')->nullable();
            $table->integer('pontuacao');
            $table->unsignedBigInteger('user_id');

            $table->foreign('corrida_id')->references('id')->on('corridas');
            $table->foreign('pilotoEquipe_id')->references('id')->on('piloto_equipes');
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
        Schema::dropIfExists('resultados');
    }
};
