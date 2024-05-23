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
        //cria tabela de skins
        Schema::create('skins', function (Blueprint $table) {
            $table->id();
            $table->string('skin');
            $table->unsignedBigInteger('equipe_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('equipe_id')->references('id')->on('equipes');
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
        });

        //adiciona chave estrangeira
        Schema::table('piloto_equipes', function (Blueprint $table) {
            $table->unsignedBigInteger('skin_id')->nullable()->after('equipe_id');
           
            $table->foreign('skin_id')->references('id')->on('skins');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('piloto_equipes', function (Blueprint $table) {
            //
        });
    }
};
