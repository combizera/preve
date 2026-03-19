<?php

declare(strict_types=1);

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionReceiptController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login');

Route::middleware('signed')
    ->get('/receipt/{transaction}', TransactionReceiptController::class)
    ->name('transactions.receipt');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class)->except('create', 'edit', 'show');
    Route::resource('tags', TagController::class)->except('create', 'edit', 'show');
    Route::resource('transactions', TransactionController::class)->except('create', 'edit');
    Route::post('/transactions/{transaction}/share', [TransactionController::class, 'share'])
        ->name('transactions.share');

    Route::patch('recurring/{recurring}/toggle', [RecurringTransactionController::class, 'toggle'])->name('recurring.toggle');
    Route::resource('recurring', RecurringTransactionController::class)->except('create', 'edit', 'show');
});

require __DIR__ . '/settings.php';
