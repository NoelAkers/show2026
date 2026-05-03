<?php

use App\Http\Controllers\Admin\EntryController;
use App\Http\Controllers\Admin\ExhibitorController;
use App\Http\Controllers\Admin\JudgeController;
use App\Http\Controllers\Admin\ShowClassController;
use App\Http\Controllers\Admin\ShowSectionController;
use App\Http\Controllers\Judge\SectionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('show-sections', ShowSectionController::class)->except(['show']);
    Route::resource('show-sections.show-classes', ShowClassController::class);
    Route::post('show-sections/{show_section}/show-classes/{show_class}/entries', [EntryController::class, 'store'])->name('show-sections.show-classes.entries.store');
    Route::delete('show-sections/{show_section}/show-classes/{show_class}/entries/{entry}', [EntryController::class, 'destroy'])->name('show-sections.show-classes.entries.destroy');
    Route::resource('exhibitors', ExhibitorController::class);
    Route::patch('exhibitors/{exhibitor}/mark-paid', [ExhibitorController::class, 'markPaid'])->name('exhibitors.mark-paid');
    Route::patch('exhibitors/{exhibitor}/mark-unpaid', [ExhibitorController::class, 'markUnpaid'])->name('exhibitors.mark-unpaid');
    Route::get('exhibitors/{exhibitor}/add-entry', [ExhibitorController::class, 'addEntry'])->name('exhibitors.add-entry');
    Route::post('exhibitors/{exhibitor}/add-entry', [ExhibitorController::class, 'storeEntry'])->name('exhibitors.store-entry');
    Route::resource('judges', JudgeController::class)->except(['show']);
});

Route::prefix('judge')->name('judge.')->middleware(['auth', 'judge'])->group(function () {
    Route::get('sections', [SectionController::class, 'index'])->name('sections.index');
});

require __DIR__.'/settings.php';
