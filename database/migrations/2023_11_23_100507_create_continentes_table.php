<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('continentes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
        });

        /**inserindo no dos continentes por padrão */
        DB::table('continentes')->insert([
            ['nome' => 'Europa'],
            ['nome' => 'Asia'],
            ['nome' => 'America do Sul'],
            ['nome' => 'America do Norte'],
            ['nome' => 'Oceania'],
            ['nome' => 'Africa'],
            ['nome' => 'Ficticio'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('continentes');
    }
};
