<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;

/**
 * MELHORIA 20261119: etapa do calendário em planejamento. A sprint (quando houver) fica na mesma linha,
 * com pista própria, e recebe o mesmo número da etapa.
 */
class CalendarioEtapa extends Model
{
    protected $table = 'calendario_etapas';

    protected $fillable = [
        'calendario_temporada_id', 'ordem', 'continente', 'nome', 'sede', 'pista', 'tempo', 'voltas', 'observacoes',
        'duracao', 'sprint_duracao',
        'flg_sprint', 'sprint_nome', 'sprint_continente', 'sprint_sede', 'sprint_pista', 'sprint_tempo', 'sprint_voltas', 'sprint_observacoes',
    ];

    public const CAMPOS_TEXTO = [
        'continente', 'nome', 'sede', 'pista', 'tempo', 'voltas', 'observacoes',
        'sprint_nome', 'sprint_continente', 'sprint_sede', 'sprint_pista', 'sprint_tempo', 'sprint_voltas', 'sprint_observacoes',
    ];

    public const DURACAO_PADRAO = 30;        // minutos da corrida comum
    public const SPRINT_DURACAO_PADRAO = 10; // minutos da sprint
    public const SEGUNDOS_EXTRA_CORRIDA = 4; // somados ao tempo de volta só na corrida comum (só no cálculo)

    /**
     * Mesma conta do script de voltas: tempo disponível ÷ tempo da volta, arredonda para baixo e tira 1.
     * Tempo no formato Minutos.Segundos.Milésimos (ex.: 1.14.766'). Retorna null se o tempo não for válido.
     */
    public static function calcularVoltas(?string $tempo, ?int $minutos, int $segundosExtra = 0): ?int
    {
        $tempo = trim(str_replace(["'", '"', ' '], '', (string) $tempo));
        $partes = explode('.', $tempo);
        if (count($partes) !== 3 || !$minutos) {
            return null;
        }
        foreach ($partes as $p) {
            if ($p === '' || !ctype_digit($p)) {
                return null;
            }
        }
        $volta = ((int) $partes[0] * 60) + (int) $partes[1] + ((int) $partes[2] / 1000) + $segundosExtra;
        if ($volta <= 0) {
            return null;
        }

        return max(0, (int) floor(($minutos * 60) / $volta) - 1);
    }

    public function temporada(){
        return $this->belongsTo(CalendarioTemporada::class, 'calendario_temporada_id');
    }

    /**
     * Mesma regra da planilha: o país é o texto depois do 2º espaço do nome ("GP da Itália" -> "Itália").
     * Com menos de dois espaços usa o nome inteiro. Se a sede foi digitada, ela vale.
     */
    public static function sedeDoNome(?string $nome): string
    {
        // MELHORIA 20261119: só o nome do país (sem "GP", "Sprint"...). Ver App\Support\PaisesCalendario::sede()
        return \App\Support\PaisesCalendario::sede($nome);
    }

    public function getSedeExibicaoAttribute(): string
    {
        return filled($this->sede) ? $this->sede : self::sedeDoNome($this->nome);
    }

    /** MELHORIA 20261119: sede da sprint também automática, pelo nome da sprint (mesma regra). */
    public function getSprintSedeExibicaoAttribute(): string
    {
        return filled($this->sprint_sede) ? $this->sprint_sede : self::sedeDoNome($this->sprint_nome);
    }

    public function paraGrid(): array
    {
        $dados = ['id' => $this->id, 'ordem' => (int) $this->ordem, 'flg_sprint' => $this->flg_sprint === 'S' ? 'S' : 'N',
            'duracao' => (int) ($this->duracao ?: self::DURACAO_PADRAO),
            'sprint_duracao' => (int) ($this->sprint_duracao ?: self::SPRINT_DURACAO_PADRAO)];
        foreach (self::CAMPOS_TEXTO as $campo) {
            $dados[$campo] = $this->{$campo} ?? '';
        }
        $dados['sede_exibicao'] = $this->sede_exibicao;
        $dados['sprint_sede_exibicao'] = $this->sprint_sede_exibicao;

        return $dados;
    }
}
