<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Borrow;
use App\Models\Interest;
use App\Models\showInterest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowInterestController extends Controller
{
    public function calculate(Borrow $borrow)
    {
        $startDate = Carbon::parse($borrow->interest_date)->startOfDay();
        $endDate = Carbon::now()->startOfDay();
        $diffDays = $startDate->diffInDays($endDate) + 1;

        $daysInMonth = $startDate->daysInMonth;
        $dailyRate = $borrow->rate / $daysInMonth;

        $interest = ($borrow->amount * $dailyRate * $diffDays) / 100;


        // Save to show_interests table
        $show = showInterest::updateOrCreate(
            [
                'borrow_id' => $borrow->id,
            ],
            [
                'user_id'     => $borrow->user_id,
                'currency_id' => $borrow->currency_id,
                'amount'      => $borrow->amount,
                'rate'        => $borrow->rate, // ✅ Add this line
                'start_date'  => $startDate->toDateString(),
                'end_date'    => $endDate->toDateString(),
                'days'        => $diffDays,
                'interest'    => $interest,
            ]
        );

        return response()->json([
            'message' => 'Interest calculated successfully.',
            'data' => [
                'interest'    => round($interest, 2),
                'days'        => $diffDays,
                'start_date'  => $startDate->toDateString(),
                'end_date'    => $endDate->toDateString(),
            ],
        ]);
    }

    // mark as paid 
    public function markAsPaid($id)
    {
        $showInterest = showInterest::findOrFail($id);
        $showInterest->paid = true;
        $showInterest->save();

        // Get related borrow
        $borrow = Borrow::findOrFail($showInterest->borrow_id);
        $amount = $showInterest->interest;

        // Get start and end dates from showInterest
        $startDate = Carbon::parse($showInterest->start_date)->startOfDay();
        $endDate = Carbon::parse($showInterest->end_date)->startOfDay();

        // Save to interest history
        Interest::create([
            'int_pay_date' => now()->toDateString(),
            'amount'       => $amount,
            'user_id'      => $showInterest->user_id,
            'borrow_id'    => $showInterest->borrow_id,
            'start_date'   => $startDate->toDateString(),
            'end_date'     => $endDate->toDateString(),
        ]);

        // Update borrow's interest_date so it starts from tomorrow
        $borrow->update([
            'interest_date' => now()->addDay()->toDateString()
        ]);

        // Optional: delete show_interest to reset the cycle
        $showInterest->delete();

        return response()->json(['success' => true]);
    }
}
