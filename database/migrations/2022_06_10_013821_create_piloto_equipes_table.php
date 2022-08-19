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
        Schema::create('piloto_equipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('piloto_id');
            $table->unsignedBigInteger('equipe_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ano_id');
            $table->string('flg_ativo', 3);

            $table->foreign('piloto_id')->references('id')->on('pilotos');
            $table->foreign('equipe_id')->references('id')->on('equipes');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('ano_id')->references('id')->on('anos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('piloto_equipes');
    }
};
