<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\ShowClassController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('show-classes', [ShowClassController::class, 'lookup']);
    Route::get('entries/{number}', [EntryController::class, 'lookup']);
    Route::post('results', [ResultController::class, 'store']);
});
