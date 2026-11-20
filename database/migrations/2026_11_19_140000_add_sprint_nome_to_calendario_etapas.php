<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MELHORIA 20261119: nome próprio da sprint no calendário.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('calendario_etapas', function (Blueprint $table) {
            $table->string('sprint_nome', 150)->nullable()->after('flg_sprint');
        });
    }

    public function down()
    {
        Schema::table('calendario_etapas', function (Blueprint $table) {
            $table->dropColumn('sprint_nome');
        });
    }
};
