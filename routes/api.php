<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnThisDaySnapshotController;

Route::get('/on-this-day', [OnThisDaySnapshotController::class, 'historicalToday']);
