<?php

namespace App\Http\Controllers\User;

use Inertia\Inertia;
use App\Models\Borrow;
use App\Models\Currency;
use App\Models\repayment;
use App\Models\showInterest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $borrows = Borrow::with('currency')
            ->where('user_id', $user->id)
            ->get();

        return Inertia::render('user/HomePage', [
            'borrows' => $borrows,
        ]);
    }
}
