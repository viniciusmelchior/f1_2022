<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melhoria 2026-11-03 (etapa 3): treino livre + voltas inválidas.
 * Apenas acrescenta colunas nas tabelas criadas em 2026_11_03_120000.
 * Nenhuma tabela pré-existente do sistema é alterada.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('resultado_voltas', function (Blueprint $table) {
            // voltas inválidas (corte de pista / volta de saída) vêm com time = -1 no JSON
            $table->unsignedInteger('tempo_ms')->nullable()->change();
            $table->boolean('valida')->default(true)->after('tempo_ms');
        });

        Schema::table('resultado_detalhes', function (Blueprint $table) {
            $table->unsignedInteger('voltas_validas_classificacao')->nullable()->after('voltas_classificacao');
            $table->unsignedInteger('volta_ideal_classificacao_ms')->nullable()->after('voltas_validas_classificacao');
            $table->unsignedInteger('treino_posicao')->nullable()->after('volta_ideal_classificacao_ms');
            $table->unsignedInteger('treino_melhor_volta_ms')->nullable()->after('treino_posicao');
            $table->unsignedInteger('treino_voltas')->nullable()->after('treino_melhor_volta_ms');
            $table->unsignedInteger('treino_voltas_validas')->nullable()->after('treino_voltas');
        });

        Schema::table('corrida_detalhes', function (Blueprint $table) {
            $table->unsignedInteger('treino_melhor_volta_ms')->nullable()->after('pole_nome');
            $table->unsignedBigInteger('treino_melhor_piloto_equipe_id')->nullable()->after('treino_melhor_volta_ms');
            $table->string('treino_melhor_nome')->nullable()->after('treino_melhor_piloto_equipe_id');
            $table->unsignedInteger('classificacao_volta_ideal_ms')->nullable()->after('treino_melhor_nome');
        });
    }

    public function down()
    {
        Schema::table('corrida_detalhes', function (Blueprint $table) {
            $table->dropColumn(['treino_melhor_volta_ms', 'treino_melhor_piloto_equipe_id', 'treino_melhor_nome', 'classificacao_volta_ideal_ms']);
        });
        Schema::table('resultado_detalhes', function (Blueprint $table) {
            $table->dropColumn(['voltas_validas_classificacao', 'volta_ideal_classificacao_ms', 'treino_posicao', 'treino_melhor_volta_ms', 'treino_voltas', 'treino_voltas_validas']);
        });
        Schema::table('resultado_voltas', function (Blueprint $table) {
            $table->dropColumn('valida');
        });
    }
};
