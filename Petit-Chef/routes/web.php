<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cook\DishController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerStore'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

Route::middleware(['auth', 'role:client'])->group(function (): void {
    Route::get('/client', [DashboardController::class, 'client'])->name('client.dashboard');
});

// Routes Cuisinier
Route::middleware(['auth', 'role:cook'])
    ->prefix('cuisinier')
    ->name('cook.')
    ->group(function (): void {
        Route::get('/', [DishController::class, 'dashboard'])->name('dashboard');
        Route::get('plats/creer', [DishController::class, 'create'])->name('dishes.create');
        Route::post('plats', [DishController::class, 'store'])->name('dishes.store');
        Route::get('plats/{dish}/modifier', [DishController::class, 'edit'])->name('dishes.edit');
        Route::put('plats/{dish}', [DishController::class, 'update'])->name('dishes.update');
        Route::delete('plats/{dish}', [DishController::class, 'destroy'])->name('dishes.destroy');
        Route::patch('plats/{dish}/plat-du-jour', [DishController::class, 'toggleOfDay'])->name('dishes.toggle-ofday');
    });

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::patch('/admin/cooks/{user}/status', [AdminDashboardController::class, 'updateCookStatus'])->name('admin.cooks.status');
    Route::patch('/admin/reports/{reportId}/status', [AdminDashboardController::class, 'updateReportStatus'])->name('admin.reports.status');
    Route::patch('/admin/users/{user}/status', [AdminDashboardController::class, 'updateUserStatus'])->name('admin.users.status');
    Route::get('/admin/api/stats', [AdminDashboardController::class, 'stats'])->name('admin.api.stats');
});