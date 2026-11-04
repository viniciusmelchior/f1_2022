<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melhoria 2026-11-03: grava os JSONs das sessões (Content Manager) e os dados
 * extraídos deles, atrelados a piloto_equipes / pilotos / equipes, para estatísticas.
 *
 * Apenas cria tabelas novas. Nenhuma tabela existente (corridas, resultados...) é alterada.
 */
return new class extends Migration
{
    public function up()
    {
        // JSON original de cada sessão (largada, chegada, grid invertido)
        Schema::create('corrida_sessoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corrida_id');
            $table->unsignedBigInteger('user_id');
            $table->string('tipo', 20); // largada | chegada | grid_invertido
            $table->string('nome_arquivo')->nullable();
            $table->char('hash', 40)->nullable();
            $table->longText('conteudo');
            $table->timestamps();

            $table->unique(['corrida_id', 'user_id', 'tipo']);
        });

        // Resumo da corrida (condições, sessão, números gerais)
        Schema::create('corrida_detalhes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corrida_id');
            $table->unsignedBigInteger('user_id');

            // pista / sessão
            $table->string('pista_codigo')->nullable();
            $table->string('pista_layout')->nullable();
            $table->string('sessao_nome')->nullable();
            $table->date('data_sessao')->nullable();
            $table->string('horario', 5)->nullable();

            // corrida
            $table->unsignedInteger('total_voltas')->nullable();
            $table->unsignedInteger('total_carros')->nullable();
            $table->unsignedInteger('total_abandonos')->nullable();
            $table->unsignedInteger('total_paradas')->nullable();
            $table->unsignedBigInteger('tempo_corrida_ms')->nullable();
            $table->unsignedInteger('media_volta_vencedor_ms')->nullable();
            $table->unsignedBigInteger('vencedor_piloto_equipe_id')->nullable();
            $table->string('vencedor_nome')->nullable();
            $table->unsignedInteger('volta_mais_rapida_ms')->nullable();
            $table->unsignedInteger('volta_mais_rapida_numero')->nullable();
            $table->unsignedBigInteger('volta_mais_rapida_piloto_equipe_id')->nullable();
            $table->string('volta_mais_rapida_nome')->nullable();
            $table->unsignedInteger('volta_ideal_ms')->nullable();
            $table->string('voltas_neutralizadas')->nullable();

            // classificação (largada)
            $table->unsignedInteger('pole_tempo_ms')->nullable();
            $table->unsignedBigInteger('pole_piloto_equipe_id')->nullable();
            $table->string('pole_nome')->nullable();

            // condições
            $table->string('clima_codigo')->nullable();
            $table->string('clima_descricao')->nullable();
            $table->boolean('pista_molhada')->nullable();
            $table->decimal('temperatura_ar', 5, 1)->nullable();
            $table->decimal('temperatura_pista', 5, 1)->nullable();
            $table->decimal('vento_min_kmh', 6, 1)->nullable();
            $table->decimal('vento_max_kmh', 6, 1)->nullable();
            $table->unsignedSmallInteger('vento_direcao')->nullable();
            $table->decimal('aderencia_inicial', 5, 1)->nullable();
            $table->unsignedInteger('ganho_aderencia')->nullable();

            // configuração
            $table->decimal('nivel_ia', 5, 1)->nullable();
            $table->decimal('agressividade_media', 5, 1)->nullable();
            $table->decimal('desgaste_pneus', 5, 2)->nullable();
            $table->decimal('consumo_combustivel', 5, 2)->nullable();
            $table->decimal('dano', 5, 1)->nullable();
            $table->boolean('penalidades')->nullable();
            $table->boolean('setup_fixo')->nullable();
            $table->unsignedInteger('largada_jogador')->nullable();

            $table->timestamps();

            $table->unique(['corrida_id', 'user_id']);
        });

        // Dados por piloto (classificação + corrida), atrelados a piloto/equipe
        Schema::create('resultado_detalhes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corrida_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('piloto_equipe_id')->nullable(); // null = piloto do arquivo que não está no grid cadastrado
            $table->unsignedBigInteger('piloto_id')->nullable();
            $table->unsignedBigInteger('equipe_id')->nullable();
            $table->string('nome_arquivo');      // nome do piloto como veio no JSON
            $table->string('carro')->nullable();
            $table->string('nacionalidade', 10)->nullable();
            $table->boolean('eh_jogador')->default(false);

            // classificação (JSON de largada / grid invertido)
            $table->unsignedInteger('posicao_classificacao')->nullable();
            $table->unsignedInteger('tempo_classificacao_ms')->nullable();
            $table->unsignedInteger('dif_pole_ms')->nullable();
            $table->unsignedInteger('voltas_classificacao')->nullable();

            // corrida (JSON de chegada)
            $table->unsignedInteger('posicao')->nullable();
            $table->unsignedInteger('voltas_completadas')->nullable();
            $table->unsignedBigInteger('tempo_total_ms')->nullable();
            $table->unsignedBigInteger('dif_lider_ms')->nullable();
            $table->unsignedBigInteger('intervalo_ms')->nullable();
            $table->unsignedInteger('voltas_atras')->nullable();
            $table->boolean('abandonou')->nullable();
            $table->unsignedInteger('volta_abandono')->nullable();
            $table->unsignedInteger('melhor_volta_ms')->nullable();
            $table->unsignedInteger('melhor_volta_numero')->nullable();
            $table->boolean('volta_mais_rapida')->default(false);
            $table->unsignedInteger('melhor_s1_ms')->nullable();
            $table->unsignedInteger('melhor_s2_ms')->nullable();
            $table->unsignedInteger('melhor_s3_ms')->nullable();
            $table->unsignedInteger('ritmo_medio_ms')->nullable();
            $table->unsignedInteger('consistencia_ms')->nullable();
            $table->unsignedInteger('voltas_limpas')->nullable();
            $table->unsignedInteger('paradas')->nullable();
            $table->string('voltas_paradas')->nullable();
            $table->string('pneus')->nullable();
            $table->unsignedInteger('cortes')->nullable();
            $table->unsignedInteger('posicao_volta1')->nullable();

            // configuração do carro / IA
            $table->decimal('lastro', 6, 1)->nullable();
            $table->decimal('restritor', 5, 1)->nullable();
            $table->decimal('agressividade', 5, 1)->nullable();
            $table->decimal('nivel_ia', 5, 1)->nullable();

            $table->timestamps();

            $table->index(['corrida_id', 'user_id']);
            $table->index('piloto_equipe_id');
            $table->index('piloto_id');
            $table->index('equipe_id');
        });

        // Volta a volta (classificação e corrida)
        Schema::create('resultado_voltas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resultado_detalhe_id');
            $table->unsignedBigInteger('corrida_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('piloto_equipe_id')->nullable();
            $table->unsignedBigInteger('piloto_id')->nullable();
            $table->unsignedBigInteger('equipe_id')->nullable();
            $table->string('sessao', 20); // classificacao | corrida
            $table->unsignedInteger('volta');
            $table->unsignedInteger('tempo_ms');
            $table->unsignedInteger('s1_ms')->nullable();
            $table->unsignedInteger('s2_ms')->nullable();
            $table->unsignedInteger('s3_ms')->nullable();
            $table->string('pneu', 10)->nullable();
            $table->unsignedInteger('cortes')->default(0);
            $table->unsignedInteger('posicao')->nullable();
            $table->boolean('entrada_box')->default(false);
            $table->boolean('neutralizada')->default(false);

            $table->index(['corrida_id', 'user_id']);
            $table->index('resultado_detalhe_id');
            $table->index('piloto_id');
            $table->index('equipe_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('resultado_voltas');
        Schema::dropIfExists('resultado_detalhes');
        Schema::dropIfExists('corrida_detalhes');
        Schema::dropIfExists('corrida_sessoes');
    }
};
