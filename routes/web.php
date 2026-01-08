<?php

use Inertia\Inertia;
use App\Models\Borrow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\user\ApiController;
use App\Http\Controllers\Admin\UserController;


use App\Http\Controllers\Admin\BorrowController;
use App\Http\Controllers\Admin\ReportController;

use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\InterestController;
use App\Http\Controllers\Admin\DashboardController;

use App\Http\Controllers\Admin\RepaymentController;
use App\Http\Controllers\User\UserBorrowController;
use App\Http\Controllers\Admin\ShowInterestController;
use App\Http\Controllers\User\HomeController;

require __DIR__ . '/auth.php';


// Public Route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Authenticated + Verified Users
Route::middleware(['auth', 'verified'])->group(function () {

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/user', [UserController::class, 'index'])->name('user');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::delete('/user/destroy/{id}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::get('/currency', [CurrencyController::class, 'index'])->name('currency');
        Route::post('/currency', [CurrencyController::class, 'store'])->name('currency.store');
        Route::get('/currency/Edit/{id}', [CurrencyController::class, 'edit'])->name('currency.edit');
        Route::post('/currency/Update/{id}', [CurrencyController::class, 'update'])->name('currency.update');
        Route::delete('/currency/delete/{id}', [CurrencyController::class, 'destroy'])->name('currency.destroy');

        //borrow 
        Route::get('/borrow', [BorrowController::class, 'index'])->name('borrow');
        Route::post('/borrow', [BorrowController::class, 'store'])->name('borrow.store');
        Route::delete('/admin/borrow/{id}', [BorrowController::class, 'destroy'])->name('borrow.destroy');

        //interest
        Route::get('/interest', [InterestController::class, 'index'])->name('interest');
        Route::get('/interest/calculate/{borrowId}', [InterestController::class, 'showCalculationPage'])->name('interest.calculatePage');
        // Route::post('/interest/{borrow}/calculate', [InterestController::class, 'processCalculation'])
        //     ->name('interest.calculate.process');
        // calculate interest
        Route::post('/interest/calculate/{borrow}', [ShowInterestController::class, 'calculate'])
            ->name('interest.calculate');
        //paid
        Route::post('/interest/paid/{id}', [ShowInterestController::class, 'markAsPaid'])->name('interest.paid');
        // report
        Route::get('/report/interest', [ReportController::class, 'interestReport'])->name('report.interest');
        // export excel file
        Route::get('/reports/interest/download', [ReportController::class, 'downloadInterestReport'])
            ->name('interest.report.download');
        Route::get('/report/repayment/download', [ReportController::class, 'exportRepaymentReport'])->name('report.repayment.export');

        // profit report
        Route::get('/report/monthly-profit', [ReportController::class, 'monthlyProfitReport'])->name('profit.monthly');
        Route::get('/report/monthly-profit/export', [ReportController::class, 'exportExcel'])->name('profit-report.export');





        // Repayment
        Route::get('/repayment', [RepaymentController::class, 'directRepayment'])->name('repayment');
        Route::post('/repayment/store/{borrowId}', [RepaymentController::class, 'storeRepayment'])->name('repayment.store');
        Route::get('/report/repayment', [ReportController::class, 'repaymentReport'])->name('report.repayment');
    });


    // User Routes
    Route::prefix('user')->name('user.')->middleware('can:user')->group(function () {
        Route::get('/home', [HomeController::class, 'index'])->name('homePage');

        Route::get('/borrow', [UserBorrowController::class, 'borrow'])->name('borrow');
    });


    Route::get('/force-logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    });
});

// Authenticated Only (not necessarily verified)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
