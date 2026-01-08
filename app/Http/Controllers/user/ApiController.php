<?php

namespace App\Http\Controllers\user;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;


use Inertia\Response;
use App\Models\Borrow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    // Show list
    public function borrow(): Response
    {
        // Load all users with their borrows
        $users = User::with('borrows')->get();

        $data = $users->map(function ($user) {
            $borrows = $user->borrows->map(function ($borrow) {
                $borrowedDate = Carbon::parse($borrow->borrowed_date);
                $days = $borrowedDate->diffInDays(now());

                $interest = ($borrow->amount * $borrow->rate * $days) / (100 * 365);

                return [
                    'id' => $borrow->id,
                    'amount' => $borrow->amount,
                    'rate' => $borrow->rate,
                    'borrowed_date' => $borrow->borrowed_date,
                    'days' => $days,
                    'interest' => round($interest, 2),
                ];
            });

            return [
                'user_name' => $user->name,
                'borrows' => $borrows,
                'total_interest' => round($borrows->sum('interest'), 2),
            ];
        });

        return Inertia::render('user/UserBorrow', [
            'borrows' => $data,
        ]);
    }
}
