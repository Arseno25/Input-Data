<?php

namespace App\Filament\Exports;

use App\Models\Assessment;
use Carbon\Carbon;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Exception\InvalidArgumentException;

class AssessmentExporter extends Exporter
{
    protected static ?string $model = Assessment::class;

    public function getFormats(): array
    {
        return [
            ExportFormat::Xlsx,
        ];
    }

    public function getXlsxCellStyle(): ?Style
    {
        return (new Style())
            ->setFontSize(12)
            ->setFontName('Times New Roman');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(14)
            ->setShouldShrinkToFit(false)
            ->setFontName('Times New Roman')
            ->setFontColor(Color::rgb(255, 255, 255))
            ->setBackgroundColor(Color::rgb(0, 0, 0))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    public function getFileName(Export $export): string
    {
        return "Assessment-{$export->getKey()}_" . Carbon::now()->format('Y-m-d H:i:s');
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('student.name')->label('Nama Mahasiswa'),
            ExportColumn::make('student.nim')->label('NIM'),
            ExportColumn::make('student.title_of_the_final_project_proposal')->label('Judul Proposal Tugas Akhir '),
            ExportColumn::make('student.design_theme')->label('Tema Rancangan'),
            ExportColumn::make('assessment_stage')->label('Tahap Penilaian'),
            ExportColumn::make('assessment')->label('Nilai')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your assessment export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
