<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Borrow;
use App\Models\Currency;


use App\Models\Interest;
use App\Models\repayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ProfitReportExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InterestReportExport;
use App\Exports\RepaymentReportExport;

class ReportController extends Controller
{
    public function interestReport(Request $request)
    {
        $users = User::all();
        $currencies = Currency::all();
        $selectedUserId = $request->user_id;

        $borrows = Borrow::with('interests')
            ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
            ->get();

        $reportData = $borrows->map(function ($borrow) {
            $totalInterest = $borrow->interests->sum('amount');

            // Get the latest end_date from interests
            $latestEndDate = $borrow->interests->max('end_date');

            return [
                'borrow_id'     => $borrow->id,
                'user_id'       => $borrow->user_id,
                'currency_id'   => $borrow->currency_id,
                'amount'        => $borrow->amount,
                'rate'          => $borrow->rate,
                'start_date'    => Carbon::parse($borrow->borrowed_date)->toDateString(),
                'end_date'      => $latestEndDate ? Carbon::parse($latestEndDate)->toDateString() : null,
                'total_interest' => round($totalInterest, 2),
            ];
        });

        return inertia('admin/Report/Interest', [
            'users' => $users,
            'report' => $reportData,
            'currencies' => $currencies,
            'selectedUserId' => $selectedUserId,
        ]);
    }

    // export excel file
    public function downloadInterestReport(Request $request)
    {
        $selectedUserId = $request->user_id;

        $borrows = Borrow::with('interests', 'user', 'currency')
            ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
            ->get();

        $reportData = $borrows->map(function ($borrow) {
            $totalInterest = $borrow->interests->sum('amount');
            $latestEndDate = $borrow->interests->max('end_date');

            return [
                'borrow_id'      => $borrow->id,
                'user_name'      => optional($borrow->user)->name, // safe access
                'currency_name'  => optional($borrow->currency)->symbol,
                'amount'         => $borrow->amount,
                'rate'           => $borrow->rate,
                'start_date'     => Carbon::parse($borrow->borrowed_date)->toDateString(),
                'end_date'       => $latestEndDate ? Carbon::parse($latestEndDate)->toDateString() : null,
                'total_interest' => round($totalInterest, 2),
            ];
        });

        return Excel::download(new InterestReportExport($reportData), 'interest-report.xlsx');
    }
    // repayment
    public function repaymentReport(Request $request)
    {
        $userId = $request->user_id;

        $users = User::all();

        $borrows = Borrow::with(['currency', 'user', 'repayments'])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();

        // Flatten repayment records per borrow
        $reportData = [];

        foreach ($borrows as $borrow) {
            $totalRepaid = $borrow->repayments->sum('amount');
            $balance = $borrow->remaining_amount;

            foreach ($borrow->repayments as $repayment) {
                $reportData[] = [
                    'repayment_id' => $repayment->id,
                    'borrow_id' => $borrow->id,
                    'user_name' => $borrow->user->name ?? 'Unknown',
                    'amount' => $repayment->amount,
                    'currency' => $borrow->currency->symbol ?? '',
                    'rate' => $borrow->rate,
                    'borrowed_date' => $borrow->borrowed_date,
                    'pay_date' => $repayment->pay_date,
                    'balance' => $balance,

                ];
            }
        }

        return inertia('admin/Report/RepaymentReport', [
            'users' => $users,
            'report' => $reportData,
            'selectedUserId' => $userId,
        ]);
    }
    // repayment export excel
    public function exportRepaymentReport(Request $request)
    {
        $userId = $request->user_id;

        $borrows = Borrow::with(['currency', 'user'])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();

        $reportData = $borrows->map(function ($borrow) {
            $totalRepaid = $borrow->repayments->sum('amount');
            $latestRepaymentDate = $borrow->repayments->max('pay_date');
            return [
                'borrow_id' => $borrow->id,
                'user_name' => $borrow->user->name ?? 'Unknown',
                'amount' => $borrow->amount,
                'currency' => $borrow->currency->symbol ?? '',
                'rate' => $borrow->rate,
                'borrowed_date' => $borrow->borrowed_date,
                'latest_repayment_date' => $latestRepaymentDate ? Carbon::parse($latestRepaymentDate)->toDateString() : '',
                'total_repaid' => $totalRepaid,
            ];
        });

        return Excel::download(new RepaymentReportExport($reportData), 'repayment-report.xlsx');
    }
    // monthly profit report
    public function monthlyProfitReport(Request $request)
    {
        $currency_id = $request->input('currency_id');
        $user_id = $request->input('user_id');
        $month = $request->input('month');
        $year = $request->input('year');
        $period = $request->input('period', 'monthly');


        $query = Interest::with('borrow');

        if ($month) $query->whereMonth('int_pay_date', $month);
        if ($year) $query->whereYear('int_pay_date', $year);
        if ($currency_id) $query->whereHas('borrow', fn($q) => $q->where('currency_id', $currency_id));
        if ($user_id) $query->whereHas('borrow', fn($q) => $q->where('user_id', $user_id));

        $interests = $query->get();

        $profits = $interests->groupBy(function ($item) use ($period) {
            $date = Carbon::parse($item->int_pay_date);
            return match ($period) {
                'daily' => $date->format('Y-m-d'),
                'weekly' => $date->format('o-\WW'),
                default => $date->format('Y-m'),
            };
        })->map(function ($group, $key) use ($period) {
            $borrowIds = $group->pluck('borrow_id')->unique();
            $totalInvested = Borrow::whereIn('id', $borrowIds)->sum('amount');

            $label = match ($period) {
                'daily' => Carbon::parse($key)->format('d M Y'),
                'weekly' => $key,
                default => Carbon::createFromFormat('Y-m', $key)->format('F Y'),
            };

            return [
                'period_label' => $label,
                'total_interest' => $group->sum('amount'),
                'total_invested' => $totalInvested,
            ];
        })->values();

        return Inertia::render('admin/Report/ProfitPerMonth', [
            'profits' => $profits,
            'currencies' => Currency::all(),
            'users' => User::all(),
            'filters' => compact('month', 'year', 'currency_id', 'user_id', 'period'),
            'totalProfit' => $interests->sum('amount'),
        ]);
    }


    public function exportExcel(Request $request)
    {
        $filters = $request->only(['month', 'year', 'currency_id', 'user_id', 'period']);
        $filename = 'profit-report-' . now()->format('YmdHis') . '.xlsx';

        return Excel::download(new ProfitReportExport($filters), $filename);
    }
}
