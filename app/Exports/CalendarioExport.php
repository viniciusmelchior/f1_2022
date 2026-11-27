<?php

namespace App\Exports;

use App\Models\Site\CalendarioTemporada;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * MELHORIA 20261119: backup do calendário em Excel — uma aba por temporada do usuário.
 */
class CalendarioExport implements WithMultipleSheets
{
    public function __construct(private int $userId)
    {
    }

    public function sheets(): array
    {
        $temporadas = CalendarioTemporada::where('user_id', $this->userId)->orderBy('id')->get();
        $sheets = [];
        $usados = [];
        foreach ($temporadas as $temporada) {
            $sheets[] = new CalendarioTemporadaSheet($temporada, $this->tituloAba($temporada->nome, $usados));
        }
        if (!$sheets) {
            $sheets[] = new CalendarioTemporadaSheet(null, 'Calendário');
        }

        return $sheets;
    }

    /** Nome de aba válido no Excel: até 31 caracteres, sem []:*?/\ e sem repetir. */
    private function tituloAba(string $nome, array &$usados): string
    {
        $base = trim(preg_replace('/[\[\]\:\*\?\/\\\\]/u', ' ', $nome)) ?: 'Temporada';
        $base = mb_substr($base, 0, 31);
        $titulo = $base;
        $n = 2;
        while (in_array(mb_strtolower($titulo), $usados, true)) {
            $sufixo = ' (' . $n++ . ')';
            $titulo = mb_substr($base, 0, 31 - mb_strlen($sufixo)) . $sufixo;
        }
        $usados[] = mb_strtolower($titulo);

        return $titulo;
    }
}
