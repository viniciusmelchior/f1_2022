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
        Schema::table('corridas', function (Blueprint $table) {
            // Adicionando um índice à coluna 'nome'
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corridas', function (Blueprint $table) {
            // Remova o índice se precisar fazer rollback
            $table->dropIndex('user_id');
        });
    }
};
