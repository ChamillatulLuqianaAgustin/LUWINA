<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\super_admin\UserController;
use App\Http\Controllers\super_admin\MakeProjectController;
use App\Http\Controllers\super_admin\AllProjectController;
use App\Http\Controllers\super_admin\ProcessController;
use App\Http\Controllers\super_admin\AccController;
use App\Http\Controllers\super_admin\RejectController;


// Route Login Page
Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('/login-proses', [AuthController::class, 'proses_login'])->name('login-proses'); // BACKEND LOGIN PROSES BLM BISA
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// All Project
Route::prefix('superadmin')->group(function () {
    Route::get('/user', [UserController::class, 'index'])->name('superadmin.user');
    Route::get('/make-project', [MakeProjectController::class, 'index'])->name('superadmin.make-project');
    Route::get('/allproject', [AllProjectController::class, 'allproject']);
    Route::get('/process', [ProcessController::class, 'index'])->name('superadmin.process');
    Route::get('/acc', [AccController::class, 'index'])->name('superadmin.acc');
    Route::get('/reject', [RejectController::class, 'index'])->name('superadmin.reject');
});