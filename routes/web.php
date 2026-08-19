<?php

use Illuminate\Support\Facades\Route;

// SPA fallback — должен быть ПОСЛЕДНИМ
Route::view('/{any}', 'index')->where('any', '.*');
