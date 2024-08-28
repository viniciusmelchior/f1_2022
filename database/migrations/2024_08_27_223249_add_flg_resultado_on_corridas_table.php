<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Site\Corrida;

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
            $table->integer('exibir_resultado')->nullable()->after('ordem');
        });

        $corridas = Corrida::all();

        foreach($corridas as $corrida){
            if($corrida->id != 748){
                $corrida->exibir_resultado = 1;
                $corrida->update();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       //
    }
};
