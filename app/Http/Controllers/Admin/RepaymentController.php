<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Borrow;
use App\Models\repayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class RepaymentController extends Controller
{
    public function directRepayment(Request $request)
    {
        $users = User::all();
        $selectedUserId = $request->user_id;

        // Only borrows which are not fully paid (status != 'Paid')
        $borrows = Borrow::with('currency')
            ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
            ->where('status', '!=', 'Paid')
            ->get();

        return Inertia::render('admin/Repayment', [
            'users' => $users,
            'borrows' => $borrows,
            'selectedUserId' => $selectedUserId,
        ]);
    }

    // Store repayment for a borrow
    public function storeRepayment(Request $request, $borrowId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $borrow = Borrow::with('interests')->findOrFail($borrowId);

        // Validate outstanding interest
        $latestInterestEndDate = $borrow->interests->max('end_date');
        if (!$latestInterestEndDate) {
            return redirect()->back()->with('error', 'Cannot repay because interest payment is outstanding.');
        }

        $latestInterestEndDate = Carbon::parse($latestInterestEndDate);
        $today = Carbon::today();
        if ($latestInterestEndDate->lt($today)) {
            return redirect()->back()->with('error', 'Cannot repay because there is outstanding interest for this borrow.');
        }

        DB::transaction(function () use ($request, $borrow, $borrowId) {
            // Create repayment record
            Repayment::create([
                'user_id' => $borrow->user_id,
                'borrow_id' => $borrow->id,
                'currency_id' => $borrow->currency_id,
                'amount' => $request->amount,
                'pay_date' => now(),
            ]);
            // Decrease remaining_amount by the repayment amount
            $borrow->remaining_amount = max(0, $borrow->remaining_amount - $request->amount);
            $borrow->save();

            // Update borrow status if fully repaid
            $this->updateBorrowStatusAfterRepayment($borrowId);
        });

        return redirect()->back()->with('success', 'Repayment recorded successfully.');
    }

    // Update borrow status to 'Paid' if fully repaid
    public function updateBorrowStatusAfterRepayment($borrowId)
    {
        $borrow = Borrow::find($borrowId);

        if (!$borrow) return;

        // Recalculate total repaid
        $totalRepaid = repayment::where('borrow_id', $borrowId)->sum('amount');

        // Use original amount as base for comparison
        $originalAmount = $borrow->original_amount ?? $borrow->amount;

        // Update remaining amount
        $remaining = max($originalAmount - $totalRepaid, 0);
        $borrow->remaining_amount = $remaining;

        // Update status if fully paid
        if ($remaining === 0) {
            $borrow->status = 'Paid';
        }

        $borrow->save();
    }
}
