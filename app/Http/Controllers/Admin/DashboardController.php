<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Borrow;
use App\Models\Interest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\repayment;

class DashboardController extends Controller
{
    // index
    public function dashboard(Request $request)
    {
        $userId = $request->user_id;

        // Interest Stats
        $interestStats = Interest::selectRaw('DATE_FORMAT(interests.start_date, "%Y-%m") as month, currencies.symbol as currency, SUM(interests.amount) as total_interest')
            ->join('borrows', 'interests.borrow_id', '=', 'borrows.id')
            ->join('currencies', 'borrows.currency_id', '=', 'currencies.id')
            ->when($userId, fn($q) => $q->where('interests.user_id', $userId))
            ->groupBy('month', 'currencies.symbol')
            ->orderBy('month')
            ->get();

        // Repayment Stats per borrow
        $repaymentStats = Borrow::with(['user:id,name', 'currency:id,symbol', 'repayments'])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get()
            ->map(function ($borrow) {
                $totalRepaid = $borrow->repayments->sum('amount');
                return [
                    'borrow_id' => $borrow->id,
                    'user' => $borrow->user->name,
                    'borrow_amount' => $borrow->amount,
                    'total_repaid' => $totalRepaid,
                    'balance' => $borrow->remaining_amount,
                    'currency' => $borrow->currency->symbol ?? '',
                ];
            });

        $repay = $repaymentStats->sum('total_repaid');
        $borrow = $repaymentStats->sum('borrow_amount');
        $balance = $borrow - $repay;

        return Inertia::render('admin/Dashboard', [
            'interestStats' => $interestStats,
            'repaymentStats' => $repaymentStats,
            'users' => User::select('id', 'name')->get(),
            'selectedUserId' => $userId,
            'summary' => [
                'totalBorrows' => $repaymentStats->count(),
                'totalRepaid' => $repaymentStats->sum('total_repaid'),
                'totalBorrowAmount' => $repaymentStats->sum('borrow_amount'),
                'totalbalance' => $balance,
                'currency' => $repaymentStats->first()['currency'] ?? '',
            ],
        ]);
    }
}
