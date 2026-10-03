<?php

use Illuminate\Support\Facades\Route;

/*
| API routes — grow as domain modules land.
*/

Route::get('/health', function () {
    return response()->json(['ok' => true]);
})->name('api.health');
