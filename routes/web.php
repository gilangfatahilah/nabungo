<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
  return Inertia::render('dashboard/Index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
  // Account
  Route::get('account/options', [AccountController::class, 'options'])->name('account.options');
  Route::get('account/transaction-summary/{account}', [AccountController::class, 'transactionSummary'])
    ->name('account.transaction-summary');
  Route::delete('account/multiple', [AccountController::class, 'multipleDestroy'])->name('account.multiple-destroy');
  Route::resource('account', AccountController::class);

  // Category
  Route::get('category/options', [CategoryController::class, 'options'])->name('category.options');
  Route::delete('category/multiple', [CategoryController::class, 'multipleDestroy'])->name('category.multiple-destroy');
  Route::resource('category', CategoryController::class);

  // Budget
  Route::delete('budget/multiple', [BudgetController::class, 'multipleDestroy'])->name('budget.multiple-destroy');
  Route::get('budget/by-category', [BudgetController::class, 'getBudgetsByCategory'])->name('budget.by-category');
  Route::resource('budget', BudgetController::class);

  // Transaction
  Route::delete('transaction/multiple', [TransactionController::class, 'multipleDestroy'])->name('transaction.multiple-destroy');
  Route::resource('transaction', TransactionController::class);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
