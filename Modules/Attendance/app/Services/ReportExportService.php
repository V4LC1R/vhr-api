<?php

namespace Modules\Attendance\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Modules\Job\Enums\EmploymentTypeEnum;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gera CSV/XLSX/PDF a partir das linhas já agregadas por `AttendanceReportService::summary()`.
 * `$columns` é um mapa `chave-da-linha => rótulo` — define cabeçalho e ordem das colunas,
 * igual nos 3 formatos. Só sai o resumo (uma linha por funcionário); os dias expandidos
 * não entram na exportação.
 */
class ReportExportService
{
    public function export(string $format, Collection $rows, array $columns, string $filename, string $title): Response
    {
        return match ($format) {
            'csv'  => $this->csv($rows, $columns, "{$filename}.csv"),
            'xlsx' => $this->xlsx($rows, $columns, "{$filename}.xlsx"),
            'pdf'  => $this->pdf($rows, $columns, "{$filename}.pdf", $title),
        };
    }

    private function csv(Collection $rows, array $columns, string $filename): Response
    {
        return response()->streamDownload(function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 — sem isso o Excel no Windows quebra acentuação.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_values($columns));

            foreach ($rows as $row) {
                fputcsv($out, $this->formattedValues($row, $columns));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function xlsx(Collection $rows, array $columns, string $filename): Response
    {
        $writer = new XlsxWriter();
        $path   = tempnam(sys_get_temp_dir(), 'report') . '.xlsx';
        $writer->openToFile($path);

        $writer->addRow(Row::fromValues(array_values($columns)));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($this->formattedValues($row, $columns)));
        }

        $writer->close();

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    private function pdf(Collection $rows, array $columns, string $filename, string $title): Response
    {
        $tableRows = $rows->map(fn (array $row) => $this->formattedValues($row, $columns));

        return Pdf::loadView('attendance::reports.summary-pdf', [
            'title'   => $title,
            'headers' => array_values($columns),
            'rows'    => $tableRows,
        ])->download($filename);
    }

    /**
     * @return array<int, string>
     */
    private function formattedValues(array $row, array $columns): array
    {
        return array_map(
            fn (string $key) => $this->formatValue($key, $row[$key] ?? null),
            array_keys($columns)
        );
    }

    private function formatValue(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        // Minutos viram hora decimal (ex.: 90 -> "1,50") — mais útil em planilha
        // do que "1h30", tanto pra somar quanto pra multiplicar por valor/hora.
        if (str_ends_with($key, 'Minutes')) {
            return number_format(((int) $value) / 60, 2, ',', '.');
        }

        if ($key === 'diariaValueTotal') {
            return number_format((float) $value, 2, ',', '.');
        }

        if ($key === 'kind') {
            return EmploymentTypeEnum::tryFrom((string) $value)?->label() ?? (string) $value;
        }

        return (string) $value;
    }
}
