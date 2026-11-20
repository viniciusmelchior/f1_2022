<?php

namespace App\Exports;

use App\Models\Site\CalendarioTemporada;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * MELHORIA 20261119: aba de uma temporada no backup do calendário.
 * Mesma ordem da grid: a sprint vem antes da corrida, com o mesmo número.
 */
class CalendarioTemporadaSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    private array $linhasSprint = [];
    private ?int $linhaAnotacoes = null;

    public function __construct(private ?CalendarioTemporada $temporada, private string $titulo)
    {
    }

    public function title(): string
    {
        return $this->titulo;
    }

    public function array(): array
    {
        $linhas = [
            [$this->temporada?->nome ?? 'Nenhuma temporada cadastrada'],
            ['Nº', 'Tipo', 'Continente', 'Nome', 'Sede', 'Pista', 'Tempo', 'Minutos', 'Voltas', 'Observações'],
        ];
        if (!$this->temporada) {
            return $linhas;
        }

        foreach ($this->temporada->etapas()->get() as $e) {
            if ($e->flg_sprint === 'S') {
                $linhas[] = [
                    $e->ordem, 'Sprint', $e->sprint_continente ?: $e->continente, $e->sprint_nome, $e->sprint_sede_exibicao,
                    $e->sprint_pista, $e->sprint_tempo, $e->sprint_duracao, $e->sprint_voltas, $e->sprint_observacoes,
                ];
                $this->linhasSprint[] = count($linhas);
            }
            $linhas[] = [
                $e->ordem, 'Corrida', $e->continente, $e->nome, $e->sede_exibicao,
                $e->pista, $e->tempo, $e->duracao, $e->voltas, $e->observacoes,
            ];
        }

        $linhas[] = [];
        $linhas[] = ['Anotações'];
        $this->linhaAnotacoes = count($linhas);
        $linhas[] = [$this->temporada->anotacoes];

        return $linhas;
    }

    public function columnWidths(): array
    {
        return ['A' => 7, 'B' => 10, 'C' => 18, 'D' => 30, 'E' => 18, 'F' => 28, 'G' => 12, 'H' => 9, 'I' => 9, 'J' => 45];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('E10600');
        $sheet->getStyle('A2:J2')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A2:J2')->getFill()->setFillType('solid')->getStartColor()->setRGB('15151E');
        foreach ($this->linhasSprint as $linha) {
            $sheet->getStyle("A{$linha}:J{$linha}")->getFill()->setFillType('solid')->getStartColor()->setRGB('FFF6CC');
            $sheet->getStyle("A{$linha}:J{$linha}")->getFont()->setItalic(true);
        }
        if ($this->linhaAnotacoes) {
            $sheet->getStyle('A' . $this->linhaAnotacoes)->getFont()->setBold(true);
            $l = $this->linhaAnotacoes + 1;
            $sheet->mergeCells("A{$l}:J{$l}");
            $sheet->getStyle("A{$l}")->getAlignment()->setWrapText(true)->setVertical('top');
            $qtdLinhas = substr_count((string) $this->temporada?->anotacoes, "\n") + 1;
            $sheet->getRowDimension($l)->setRowHeight(min(409, max(30, $qtdLinhas * 15)));
        }
        $sheet->getStyle('J3:J' . max(3, $sheet->getHighestRow()))->getAlignment()->setWrapText(true);
        $sheet->freezePane('A3');

        return [];
    }
}
