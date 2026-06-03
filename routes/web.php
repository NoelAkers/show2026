<?php

use App\Http\Controllers\Admin\ClassCardsController;
use App\Http\Controllers\Admin\EntryController;
use App\Http\Controllers\Admin\ExhibitorController;
use App\Http\Controllers\Admin\JudgeController;
use App\Http\Controllers\Admin\LeaderboardController;
use App\Http\Controllers\Admin\ResultCardsController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Admin\ShowClassController;
use App\Http\Controllers\Admin\ShowSectionController;
use App\Http\Controllers\Admin\TrophyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Judge\SectionController;
use App\Http\Controllers\Public\ResultController as PublicResultController;
use App\Http\Controllers\Public\ScheduleController;
use App\Http\Controllers\Public\TrophyController as PublicTrophyController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
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
    Route::get('exhibitors/{exhibitor}/labels', [ExhibitorController::class, 'labels'])->name('exhibitors.labels');
    Route::patch('exhibitors/{exhibitor}/update-payment', [ExhibitorController::class, 'updatePayment'])->name('exhibitors.update-payment');
    Route::resource('judges', JudgeController::class)->except(['show']);
    Route::post('show-sections/{show_section}/show-classes/{show_class}/results', [AdminResultController::class, 'store'])->name('show-sections.show-classes.results.store');
    Route::patch('show-sections/{show_section}/show-classes/{show_class}/results/{result}', [AdminResultController::class, 'update'])->name('show-sections.show-classes.results.update');
    Route::delete('show-sections/{show_section}/show-classes/{show_class}/results/{result}', [AdminResultController::class, 'destroy'])->name('show-sections.show-classes.results.destroy');
    Route::get('class-cards', ClassCardsController::class)->name('class-cards');
    Route::get('result-cards', [ResultCardsController::class, 'index'])->name('result-cards');
    Route::post('result-cards/mark-printed', [ResultCardsController::class, 'markPrinted'])->name('result-cards.mark-printed');
    Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
    Route::resource('trophies', TrophyController::class)->except(['show']);
});

Route::prefix('judge')->name('judge.')->middleware(['auth', 'judge'])->group(function () {
    Route::get('sections', [SectionController::class, 'index'])->name('sections.index');
    Route::get('sections/{show_section}/classes', [SectionController::class, 'show'])->name('sections.show');
});

Route::prefix('public')->name('public.')->group(function () {
    Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule');
    Route::get('results', [PublicResultController::class, 'index'])->name('results');
    Route::get('trophies', [PublicTrophyController::class, 'index'])->name('trophies');
});

require __DIR__.'/settings.php';
