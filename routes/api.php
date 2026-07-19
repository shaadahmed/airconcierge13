<?php

use Illuminate\Support\Facades\Route;

/*
| API routes — grow as domain modules land.
| Hostaway webhook remains on the web stack (CSRF exception + same-origin path).
*/

Route::get('/health', function () {
    return response()->json(['ok' => true]);
})->name('api.health');
