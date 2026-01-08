<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;

class RepaymentReportExport implements FromCollection, WithHeadings, WithEvents
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        // Convert array data to a Laravel Collection
        return collect($this->reportData);
    }

    public function headings(): array
    {
        return [
            'Borrow ID',
            'User Name',
            'Borrowed Amount',
            'Currency',
            'Rate (%)',
            'Borrowed Date',
            'Latest Payment Date',
            'Total Repaid'

        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $rowCount = count($this->reportData) + 1; // +1 for heading row

                // Calculate totals
                $totalBorrowed = collect($this->reportData)->sum('amount');
                $totalRepaid = collect($this->reportData)->sum('total_repaid');

                // Write totals in the next row after data
                $sheet->setCellValue("A" . ($rowCount + 1), "Totals:");
                $sheet->setCellValue("C" . ($rowCount + 1), $totalBorrowed);
                $sheet->setCellValue("H" . ($rowCount + 1), $totalRepaid);

                // Optionally, style the totals row (bold)
                $sheet->getStyle("A" . ($rowCount + 1) . ":H" . ($rowCount + 1))->getFont()->setBold(true);
            },
        ];
    }
}
