<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Models\Currency; // Make sure you have a Currency model

class CurrencyController extends Controller
{
    // Show list
    public function index(): Response
    {
        $currencies = Currency::all();

        return Inertia::render('admin/Currency', [
            'currencies' => $currencies,
        ]);
    }

    // Show create form
    public function create(): Response
    {
        return Inertia::render('admin/CurrencyCreate');
    }

    // Store new currency
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
        ]);

        // Save to DB (assuming you have a Currency model)
        Currency::create([
            'name' => $request->name,
            'symbol' => $request->symbol,
        ]);



        return redirect()->route('admin.currency')->with('success', 'Currency saved.');
    }

    // Show edit form
    public function edit($id)
    {
        $currencies = Currency::findOrFail($id);
        return Inertia::render('admin/Currency', [
            'edit_currencies' => $currencies
        ]);
    }

    // Update currency
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
        ]);

        $currency = Currency::findOrFail($id);
        $currency->update([
            'name' => $request->name,
            'symbol' => $request->symbol,
        ]);

        return redirect()->route('admin.currency')->with('success', 'Currency updated successfully!');
    }

    // Delete currency
    public function destroy($id): RedirectResponse
    {
        Log::info("Deleting currency ID: $id");
        $currency = Currency::findOrFail($id);
        $currency->delete();

        return redirect()->back()->with('success', 'Currency deleted successfully!');
    }
}
