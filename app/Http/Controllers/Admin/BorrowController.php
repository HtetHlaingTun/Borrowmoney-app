<?php

namespace App\Http\Controllers\Admin;


use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Borrow;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class BorrowController extends Controller
{
    // Show list
    public function index(): Response
    {
        $borrows = Borrow::all();
        $currencies = Currency::all();
        $users = User::all();

        return Inertia::render('admin/RegisterBorrow', [
            'borrows' => $borrows,
            'currencies' => $currencies,
            'users' => $users,
        ]);
    }

    // store
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'currency_id' => 'required|exists:currencies,id',
            'borrowed_date' => 'required|date',
            'interest_date' => 'required|date',
            'rate' => 'required|numeric',
            'amount' => 'required|numeric',
        ]);

        Borrow::create([
            'user_id' => $request->user_id,
            'currency_id' => $request->currency_id,
            'borrowed_date' => $request->borrowed_date,
            'interest_date' => $request->interest_date,
            'rate' => $request->rate,
            'amount' => $request->amount,
            'remaining_amount' => $request->amount,


        ]);

        return redirect()->route('admin.borrow')->with('success', 'Borrow record saved!');
    }

    // delete
    public function destroy($id): RedirectResponse
    {

        $borrow = Borrow::findOrFail($id);
        $borrow->delete();

        return redirect()->back()->with('success', 'User deleted successfully!');
    }
}
