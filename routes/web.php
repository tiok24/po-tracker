<?php

use App\Http\Controllers\PublicOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicOrderController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('orders');