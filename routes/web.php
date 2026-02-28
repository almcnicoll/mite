<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CauseController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\PickController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\UserController;

Route::resource('users',    UserController::class)->except(['show']);
Route::resource('causes',   CauseController::class);
Route::resource('donations', DonationController::class)->only(['index', 'create', 'store', 'destroy']);
Route::resource('setup',    SetupController::class)->only(['index', 'create', 'store']);
Route::resource('picks',    PickController::class)->only(['index', 'edit', 'update', 'store']);
Route::get('/', [PickController::class, 'today'])->name('picks.today');

Route::get('/', function () {
    return view('welcome');
});