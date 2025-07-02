<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resultados', function (Blueprint $table) {
            if (config('database.default') === 'mysql') {
                DB::statement('ALTER TABLE resultados MODIFY pontuacao FLOAT');
            } 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resultados', function (Blueprint $table) {
            // Reversão para MySQL/MariaDB
            if (config('database.default') === 'mysql') {
                DB::statement('ALTER TABLE resultados MODIFY pontuacao INTEGER');
            }
        });
    }
};
