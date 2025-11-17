<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HelloController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EventController;

use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/hello', [HelloController::class, 'show']);
Route::get('/contact', [ContactController::class, 'create']);
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::resource('events', EventController::class);
Route::resource('blogs', BlogController::class);
// Route::get('/blogs', [BlogController::class, 'index'])->name('index');
require __DIR__.'/auth.php';

