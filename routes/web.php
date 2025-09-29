<?php

use Illuminate\Support\Facades\Route;

// Commented out to avoid conflict with API routes
// Route::get('/', function () {
//     return view('welcome');
// });

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'Web routes are working']);
});
