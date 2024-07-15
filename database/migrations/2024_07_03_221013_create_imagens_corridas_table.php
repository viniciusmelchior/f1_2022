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
        Schema::create('imagens_corridas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corrida_id');
            $table->unsignedBigInteger('user_id');
            $table->string('imagem');
            $table->timestamps();
            
            $table->foreign('corrida_id')->references('id')->on('corridas');
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
        Schema::dropIfExists('imagens_corridas');
    }
};
