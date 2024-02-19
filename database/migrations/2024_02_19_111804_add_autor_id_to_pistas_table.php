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
        Schema::table('pistas', function (Blueprint $table) {
            $table->unsignedBigInteger('autor_id')->after('tamanho_km')->nullable();

            $table->foreign('autor_id')->references('id')->on('autores');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pistas', function (Blueprint $table) {
            //
        });
    }
};
