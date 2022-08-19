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
        Schema::create('corridas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temporada_id');
            $table->unsignedBigInteger('pista_id');
            $table->integer('ordem');
            $table->unsignedBigInteger('volta_rapida')->nullable();
            $table->unsignedBigInteger('condicao_id')->nullable();
            $table->string('flg_sprint', 3)->nullable();
            $table->unsignedBigInteger('user_id');

            $table->foreign('temporada_id')->references('id')->on('temporadas');
            $table->foreign('pista_id')->references('id')->on('pistas');
            $table->foreign('condicao_id')->references('id')->on('condicao_climaticas');
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
        Schema::dropIfExists('corridas');
    }
};
