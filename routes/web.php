<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

// routes/web.php

Route::get('/products/import', [ProductController::class, 'showForm'])->name('products.import'); // blade form
Route::post('/products/upload', [ProductController::class, 'importCsv'])->name('products.upload'); // form submit

// Route::get('/uploads', [UploadController::class, 'showForm']); // drag-drop blade
// Route::post('/uploads/initiate', [UploadController::class, 'initiate']);
// Route::post('/uploads/{id}/chunk', [UploadController::class, 'chunk']);
// Route::post('/uploads/{id}/complete', [UploadController::class, 'complete']);
