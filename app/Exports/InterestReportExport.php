<?php

namespace App\Exports;

use App\Models\Borrow;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;

class InterestReportExport implements FromCollection, WithHeadings, WithEvents
{
    protected $reportData;

    public function __construct(Collection $reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        return collect($this->reportData)->map(function ($item) {
            return [
                'Borrow ID'      => $item['borrow_id'],
                'User Name'        => $item['user_name'],
                'Currency Name'        => $item['currency_name'],
                'Amount'         => number_format($item['amount'], 2),
                'Rate (%)'       => $item['rate'],
                'Start Date'     => $item['start_date'],
                'End Date'       => $item['end_date'] ?? '-',
                'Total Interest' => number_format($item['total_interest'], 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Borrow ID',
            'User Name',
            'Currency Name',
            'Amount',
            'Rate (%)',
            'Start Date',
            'End Date',
            'Total Interest',
        ];
    }
    // add row for sum of amount in excel
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $rowCount = count($this->reportData) + 1; // +1 for header row
                $totalAmount = collect($this->reportData)->sum('amount');
                $totalInterest = collect($this->reportData)->sum('total_interest');

                // Put "Totals:" label in column C (or where appropriate)
                $sheet->setCellValue("A" . ($rowCount + 1), "Totals:");

                // Right align the label cell
                $sheet->getStyle("A" . ($rowCount + 1))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Put total amount in the "Amount" column (e.g., D)
                $sheet->setCellValue("D" . ($rowCount + 1), $totalAmount);
                // Format total amount as number with 2 decimals and right-align
                $sheet->getStyle("D" . ($rowCount + 1))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                $sheet->getStyle("D" . ($rowCount + 1))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Put total interest in the "Total Interest" column (e.g., H)
                $sheet->setCellValue("H" . ($rowCount + 1), $totalInterest);
                // Format total interest as number with 2 decimals and right-align
                $sheet->getStyle("H" . ($rowCount + 1))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                $sheet->getStyle("H" . ($rowCount + 1))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Make totals row bold
                $sheet->getStyle("A" . ($rowCount + 1) . ":H" . ($rowCount + 1))
                    ->getFont()->setBold(true);

                // Format and align Amount and Total Interest columns in data rows as well
                $dataRowStart = 2;
                $dataRowEnd = $rowCount;

                $sheet->getStyle("D{$dataRowStart}:D{$dataRowEnd}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                $sheet->getStyle("D{$dataRowStart}:D{$dataRowEnd}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("H{$dataRowStart}:H{$dataRowEnd}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                $sheet->getStyle("H{$dataRowStart}:H{$dataRowEnd}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
