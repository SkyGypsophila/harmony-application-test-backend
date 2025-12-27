<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnThisDaySnapshotController;


Route::get('/index', [OnThisDaySnapshotController::class, 'index']);
Route::get('/on-this-day', [OnThisDaySnapshotController::class, 'historicalToday']);

