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
        Schema::create('forca_equipe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipe_id');
            $table->unsignedBigInteger('ano_id');
            $table->string('forca');
            $table->unsignedBigInteger('user_id');

            $table->foreign('equipe_id')->references('id')->on('equipes');
            $table->foreign('ano_id')->references('id')->on('anos');
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forca_equipe');
    }
};
