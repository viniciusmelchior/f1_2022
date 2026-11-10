<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MELHORIA 20261110 (playoff - oitavas): quantos pilotos classificam para o playoff.
 *  8  => quartas, semi e final (9 corridas de mata-mata)
 *  16 => oitavas, quartas, semi e final (12 corridas de mata-mata)
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('temporadas', function (Blueprint $table) {
            $table->unsignedTinyInteger('playoff_classificados')->default(8);
        });
    }

    public function down()
    {
        Schema::table('temporadas', function (Blueprint $table) {
            $table->dropColumn('playoff_classificados');
        });
    }
};
