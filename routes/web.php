<?php

use App\Http\Controllers\Judge\SectionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Phase 3+ CRUD routes will be added here
    Route::view('show-sections', 'admin.show-sections.index')->name('show-sections.index');
});

Route::prefix('judge')->name('judge.')->middleware(['auth', 'judge'])->group(function () {
    Route::get('sections', [SectionController::class, 'index'])->name('sections.index');
});

require __DIR__.'/settings.php';
