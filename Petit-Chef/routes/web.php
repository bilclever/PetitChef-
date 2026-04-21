<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cook\DishController;
use App\Http\Controllers\Cook\OrderController as CookOrderController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DishController as PublicDishController;
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
    Route::get('/menu/plats/{dish}', [PublicDishController::class, 'show'])->name('dish.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

Route::middleware(['auth', 'role:client'])->group(function (): void {
    Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
    Route::post('/panier/plats/{dish}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/panier/plats/{dish}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/panier/plats/{dish}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/panier', [CartController::class, 'clear'])->name('cart.clear');

    Route::post('/client/orders', [ClientOrderController::class, 'store'])->name('client.orders.store');
    Route::get('/client/orders', [ClientOrderController::class, 'history'])->name('client.orders.history');
    Route::get('/client/orders/{order}', [ClientOrderController::class, 'show'])->name('client.orders.show');
    Route::patch('/client/orders/{order}/pay', [ClientOrderController::class, 'pay'])->name('client.orders.pay');

    Route::get('/client/signalements', [\App\Http\Controllers\Client\ReportController::class, 'index'])->name('client.reports.index');
    Route::get('/client/signalements/nouveau', [\App\Http\Controllers\Client\ReportController::class, 'create'])->name('client.reports.create');
    Route::post('/client/signalements', [\App\Http\Controllers\Client\ReportController::class, 'store'])->name('client.reports.store');
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
        Route::patch('commandes/{order}/avancer', [CookOrderController::class, 'advance'])->name('orders.advance');
        Route::get('commandes/{order}', [CookOrderController::class, 'show'])->name('orders.show');
        Route::patch('boutique/statut', [\App\Http\Controllers\Cook\ShopController::class, 'toggle'])->name('shop.toggle');
        Route::patch('boutique/cloture', [\App\Http\Controllers\Cook\ShopController::class, 'updateClosingTime'])->name('shop.closing-time');
    });

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::patch('/admin/cooks/{user}/status', [AdminDashboardController::class, 'updateCookStatus'])->name('admin.cooks.status');
    Route::patch('/admin/reports/{reportId}/status', [AdminDashboardController::class, 'updateReportStatus'])->name('admin.reports.status');
    Route::patch('/admin/users/{user}/status', [AdminDashboardController::class, 'updateUserStatus'])->name('admin.users.status');
    Route::get('/admin/api/stats', [AdminDashboardController::class, 'stats'])->name('admin.api.stats');
});