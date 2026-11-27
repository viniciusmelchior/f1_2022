<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MELHORIA 20261119: planejamento do calendário das temporadas (substitui a planilha CalendarioF1).
 * Tabelas independentes: o nome da temporada é livre e todos os campos são digitados à mão.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('calendario_temporadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('nome', 120);
            $table->longText('anotacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('calendario_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendario_temporada_id')->constrained('calendario_temporadas')->cascadeOnDelete();
            $table->unsignedInteger('ordem')->default(0);
            $table->string('continente', 80)->nullable();
            $table->string('nome', 150)->nullable();
            $table->string('sede', 100)->nullable();          // vazio = automático a partir do nome
            $table->string('pista', 150)->nullable();
            $table->string('tempo', 40)->nullable();
            $table->string('voltas', 40)->nullable();
            $table->text('observacoes')->nullable();
            $table->char('flg_sprint', 1)->default('N');
            $table->string('sprint_continente', 80)->nullable(); // vazio = mesmo continente da etapa
            $table->string('sprint_sede', 100)->nullable();
            $table->string('sprint_pista', 150)->nullable();
            $table->string('sprint_tempo', 40)->nullable();
            $table->string('sprint_voltas', 40)->nullable();
            $table->text('sprint_observacoes')->nullable();
            $table->timestamps();
            $table->index(['calendario_temporada_id', 'ordem']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('calendario_etapas');
        Schema::dropIfExists('calendario_temporadas');
    }
};
