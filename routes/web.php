<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Commented out to avoid conflict with API routes
// Route::get('/', function () {
//     return view('welcome');
// });

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'Web routes are working']);
});

// Storage route for serving images
Route::get('/storage/{path}', function ($path) {
    $filePath = 'public/' . $path;

    if (Storage::exists($filePath)) {
        $file = Storage::get($filePath);
        $mimeType = Storage::mimeType($filePath);

        return response($file, 200)
            ->header('Content-Type', $mimeType);
    }

    return response('File not found', 404);
})->where('path', '.*');
