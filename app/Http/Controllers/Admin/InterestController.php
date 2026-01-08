<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Borrow;
use App\Models\Currency;
use App\Models\Interest;
use App\Models\showInterest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InterestController extends Controller
{
    // Show list
    public function index(): Response
    {
        // $interest = Interest::all();
        $currencies = Currency::all();
        $showInterest = showInterest::all();
        $user = User::all();
        $borrows = Borrow::select('borrows.*', 'users.name as user_name', 'currencies.name as currency_name', 'currencies.symbol', 'currencies.name as currency_name', 'currencies.id as currency_id')
            ->join('users', 'borrows.user_id', 'users.id')
            ->join('currencies', 'borrows.currency_id', 'currencies.id')
            ->groupBy('borrows.id')
            ->get();
        // $borrows = Borrow::with('user')->get()->groupBy('user_id')->map(function ($items) {
        //     return [
        //         'user' => $items->first()->user->name,
        //         'borrows' => $items->map(function ($borrow) {
        //             $borrowedDate = Carbon::parse($borrow->borrowed_date);
        //             $today = Carbon::now();

        //             // Calculate days difference
        //             $days = $borrowedDate->diffInDays($today);

        //             // Accurate daily interest formula
        //             $borrow->days = $days;
        //             $borrow->interest = ($borrow->amount * $borrow->rate * $days) / (100 * 365);

        //             return $borrow;
        //         }),
        //         'total_interest' => $items->sum(function ($b) {
        //             $borrowedDate = Carbon::parse($b->borrowed_date);
        //             $days = $borrowedDate->diffInDays(Carbon::now());
        //             return ($b->amount * $b->rate * $days) / (100 * 365);
        //         }),
        //     ];
        // });

        return Inertia::render('admin/Interest', [
            // 'interests' => $interest,
            // 'borrows' => $borrows,
            'borrows' => $borrows,
            'users' => $user,
            'currencies' => $currencies,
            'showInterests' => $showInterest,
        ]);
    }
    // show calculation page
    public function showCalculationPage(Borrow $borrow)
    {
        $borrow->load('user', 'currency');
        return Inertia::render('admin/InterestCalculate', [
            'borrow' => $borrow,
        ]);
    }

    // process calculation
    public function processCalculation(Request $request, Borrow $borrow)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = $start->diffInDays($end);

        $interest = ($borrow->amount * $borrow->rate * $days) / (100 * 365);

        return back()->with([
            'result' => [
                'interest' => round($interest, 2),
                'days' => $days,
            ]
        ]);
    }
}
