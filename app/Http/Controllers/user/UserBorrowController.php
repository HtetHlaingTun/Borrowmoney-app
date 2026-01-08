<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Borrow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserBorrowController extends Controller
{
    public function borrow(Request $request)
    {
        $search = $request->input('search');
        $query = Borrow::with(['currency', 'showInterest', 'repayments'])
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'Paid');   // exclude fully paid borrows

        if ($search) {
            $query->where('id', $search);
        }

        $borrows = $query->paginate(5)->through(function ($borrow) {
            return [
                'id' => $borrow->id,
                'amount' => $borrow->amount,
                'remaining_amount' => $borrow->remaining_amount,
                'currency' => $borrow->currency->symbol ?? '',
                'borrowed_date' => Carbon::parse($borrow->borrowed_date)->toDateString(),
                'interest' => optional($borrow->showInterest)->interest,
                'status' => $borrow->status,
                'total_repaid' => $borrow->repayments->sum('amount'),
                'repayments' => $borrow->repayments->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'amount' => $r->amount,
                        'date' => $r->created_at->toDateString(),
                    ];
                }),
            ];
        });

        return Inertia::render('user/UserBorrow', [
            'borrows' => $borrows->items(),
            'currentPage' => $borrows->currentPage(),
            'lastPage' => $borrows->lastPage(),
            'filters' => ['search' => $search],
        ]);
    }
}
