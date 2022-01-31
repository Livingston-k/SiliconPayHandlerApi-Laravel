<?php

use App\Http\Controllers\DepositeController;
use App\Http\Controllers\WithdrawController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
Route::post("deposite", [DepositeController::class, 'Process_deposite']);
Route::post("withdraw", [WithdrawController::class, 'Process_withdraw']);
