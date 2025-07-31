<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\LogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Auth routes
Route::get('/login', [App\Http\Controllers\Web\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Web\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\Web\AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Web\AuthController::class, 'register']);

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/process-all-pending', [DashboardController::class, 'processAllPending'])->name('dashboard.process-all-pending');

    // Contacts
    Route::resource('contacts', ContactController::class);
    Route::post('contacts/{contact}/process-score', [ContactController::class, 'processScore'])->name('contacts.process-score');
    Route::get('contacts/{contact}/json', [ContactController::class, 'showJson'])->name('contacts.show-json');

    // Logs
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/download', [LogController::class, 'download'])->name('logs.download');

    // Profile routes
    Route::get('/profile', [App\Http\Controllers\Web\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [App\Http\Controllers\Web\ProfileController::class, 'update'])->name('profile.update');

    // Auth routes
    Route::post('/logout', [App\Http\Controllers\Web\AuthController::class, 'logout'])->name('logout');
});
