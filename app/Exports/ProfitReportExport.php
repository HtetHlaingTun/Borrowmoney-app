<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Interest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProfitReportExport implements FromCollection, WithHeadings, WithEvents, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        // Clean up filters - remove null values and 'null' strings
        $this->filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '' && $value !== 'null';
        });
    }

    public function collection()
    {
        $query = Interest::with(['borrow.user', 'borrow.currency']);

        // Apply filters - now we can safely use !empty()
        if (!empty($this->filters['month'])) {
            $query->whereMonth('int_pay_date', $this->filters['month']);
        }

        if (!empty($this->filters['year'])) {
            $query->whereYear('int_pay_date', $this->filters['year']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->whereHas('borrow', fn($q) => $q->where('user_id', $this->filters['user_id']));
        }

        if (!empty($this->filters['currency_id'])) {
            $query->whereHas('borrow', fn($q) => $q->where('currency_id', $this->filters['currency_id']));
        }

        $interests = $query->get();

        // Handle empty data gracefully
        if ($interests->isEmpty()) {
            return collect([
                [
                    'month' => 'No Data Available',
                    'total_interest' => 0,
                    'total_invested' => 0,
                ]
            ]);
        }

        $grouped = $interests->groupBy(fn($item) => Carbon::parse($item->int_pay_date)->format('Y-m'));

        return $grouped->map(function ($group, $monthKey) {
            return [
                'month' => Carbon::parse($monthKey . '-01')->format('F Y'),
                'total_interest' => $group->sum('amount'),
                'total_invested' => $group->sum(fn($i) => optional($i->borrow)->amount ?? 0),
            ];
        })->values();
    }

    public function headings(): array
    {
        return ['Month', 'Total Interest Profit', 'Total Invested Amount'];
    }

    public function map($row): array
    {
        return [
            $row['month'],
            round($row['total_interest'], 2),
            round($row['total_invested'], 2),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastDataRow = $sheet->getHighestRow();

                // Only add totals if there's actual data (not just "No Data Available")
                $firstDataCell = $sheet->getCell('A2')->getValue();
                if ($firstDataCell !== 'No Data Available') {
                    // Add total row
                    $sheet->setCellValue('A' . ($lastDataRow + 1), 'Total:');
                    $sheet->setCellValue("B" . ($lastDataRow + 1), "=SUM(B2:B{$lastDataRow})");
                    $sheet->setCellValue("C" . ($lastDataRow + 1), "=SUM(C2:C{$lastDataRow})");

                    // Bold style for total row
                    $sheet->getStyle("A" . ($lastDataRow + 1) . ":C" . ($lastDataRow + 1))
                        ->getFont()->setBold(true);
                }

                // Style the header row
                $sheet->getStyle('A1:C1')
                    ->getFont()->setBold(true);

                // Auto-size columns
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
            },
        ];
    }
}
