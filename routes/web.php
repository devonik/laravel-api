<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Yogagraphy\YogagraphyController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**Yogagraphy */

Route::get('/yogagraphy', [YogagraphyController::class, 'viewForm'])->name('yogagraphyForm');
Route::get('/api/yogagraphy/displayYogasByName/{name}', [YogagraphyController::class, 'displayYogasByName'])->name('yogagraphyImage');
Route::get('/api/yogagraphy/displayFinalChristmasCardByName/{name}', [YogagraphyController::class, 'displayFinalChristmasCardByName'])->name('yogagraphyChristmasCard');
Route::post('/api/yogagraphy/getImageByForm', [YogagraphyController::class, 'getImageByForm'])->name('yogagraphyImageByForm');

require __DIR__.'/auth.php';
