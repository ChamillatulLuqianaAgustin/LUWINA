<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\super_admin\DashboardController;

// Route Login Page
Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('/login-proses', [AuthController::class, 'proses_login'])->name('login-proses'); // BACKEND LOGIN PROSES BLM BISA
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard
Route::get('/super_admin/dashboard', [DashboardController::class, 'dashboard']);
