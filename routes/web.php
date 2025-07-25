<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\categoryController;
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
  Route::delete('account/multiple', [AccountController::class, 'multipleDestroy'])->name('account.multiple-destroy');
  Route::resource('account', AccountController::class);

  // Category
  Route::delete('category/multiple', [CategoryController::class, 'multipleDestroy'])->name('category.multiple-destroy');
  Route::resource('category', CategoryController::class);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
