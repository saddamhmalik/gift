<?php

use Illuminate\Support\Facades\Route;

// API-only app: redirect root to API health
Route::get('/', function () {
    return redirect('/api/v1/health');
});
