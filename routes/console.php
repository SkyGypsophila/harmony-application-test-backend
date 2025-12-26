<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Log::info('this is from scheduler.');
})->everyFiveSeconds();

Artisan::command('log', function () {
    Log::info('this is second way of scheduler.');
})->purpose('Log in the laravel.log file');

Schedule::command('log')->everyTwoSeconds();
