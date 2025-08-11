<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Route Login Page
Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('/login-proses', [AuthController::class, 'proses_login'])->name('login-proses'); // BACKEND LOGIN PROSES BLM BISA
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');