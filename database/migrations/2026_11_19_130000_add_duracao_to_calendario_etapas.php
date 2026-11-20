<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MELHORIA 20261119: duração (minutos) da corrida e da sprint de cada etapa do calendário,
 * usada no cálculo automático das voltas. Padrão: 30 min corrida / 10 min sprint.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('calendario_etapas', function (Blueprint $table) {
            $table->unsignedSmallInteger('duracao')->default(30)->after('tempo');
            $table->unsignedSmallInteger('sprint_duracao')->default(10)->after('sprint_tempo');
        });
    }

    public function down()
    {
        Schema::table('calendario_etapas', function (Blueprint $table) {
            $table->dropColumn(['duracao', 'sprint_duracao']);
        });
    }
};
