<?php

declare(strict_types=1);

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class)->except('create', 'edit');
    Route::resource('tags', TagController::class)->except('create', 'edit');
    Route::resource('transactions', TransactionController::class)->except('create', 'edit');
});

require __DIR__ . '/settings.php';
