<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. VITRINE (Pública para todos)

Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// 2. AUTENTICAÇÃO (Apenas para quem não está logado)
Route::middleware('guest')->group(function () {
    Route::get('/', [StoreController::class, 'index'])->name('store.index');
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// 3. PAINEL ADMINISTRATIVO (Protegido por login)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard e Logout
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Gerenciamento (Agora protegidos!)
    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class);
    
    // Relatórios
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');

    // 4. ÁREA DO SUPER-ADMIN (Protegido por login + middleware de Admin)
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    });
});