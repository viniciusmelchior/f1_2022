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
            $table->string('autor')->after('qtd_voltas')->nullable();
            $table->string('drs')->after('autor')->nullable();
            $table->string('tipo')->after('drs')->nullable();
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
