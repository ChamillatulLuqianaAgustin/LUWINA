<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\super_admin\UserController;
use App\Http\Controllers\super_admin\MakeProjectController;
use App\Http\Controllers\super_admin\AllProjectController;
use App\Http\Controllers\super_admin\ProcessController;
use App\Http\Controllers\super_admin\AccController;
use App\Http\Controllers\super_admin\RejectController;


// Route Login Page
Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('/login-proses', [AuthController::class, 'proses_login'])->name('login-proses'); 
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Super Admin
Route::prefix('superadmin')->group(function () {
    // user
    Route::get('/user', [UserController::class, 'index'])->name('superadmin.user');
    Route::post('/user/store', [UserController::class, 'store'])->name('superadmin.user_store');
    Route::post('/user/update/{id}', [UserController::class, 'update'])->name('superadmin.user_update');
    Route::delete('/user/delete/{id}', [UserController::class, 'destroy'])->name('superadmin.user_destroy');
    // make project
    Route::get('/makeproject', [MakeProjectController::class, 'index'])->name('superadmin.makeproject');
    Route::post('/makeproject', [MakeProjectController::class, 'store'])->name('superadmin.makeproject_store');
    // all peoject
    Route::get('/allproject', [AllProjectController::class, 'index'])->name('superadmin.allproject');
    Route::get('/allproject/detail/{id}', [AllProjectController::class, 'detail'])->name('superadmin.allproject_detail');
    // Process
    Route::get('/process', [ProcessController::class, 'index'])->name('superadmin.process');
    Route::get('/process/detail/{id}', [ProcessController::class, 'detail'])->name('superadmin.process_detail');
    Route::get('/process/edit/{id}', [ProcessController::class, 'edit'])->name('superadmin.process_edit');
    Route::put('/process/update/{id}', [ProcessController::class, 'update'])->name('superadmin.process_update');
    Route::delete('/process/delete/{id}', [ProcessController::class, 'destroy'])->name('superadmin.process_destroy');
    Route::post('/process/{id}/acc', [ProcessController::class, 'acc'])->name('superadmin.process.acc');
    Route::post('/process/{id}/reject', [ProcessController::class, 'reject'])->name('superadmin.process.reject');
    // acc
    Route::get('/acc', [AccController::class, 'index'])->name('superadmin.acc');
    Route::get('/acc/detail/{id}', [AccController::class, 'detail'])->name('superadmin.acc_detail');
    // reject
    Route::get('/reject', [RejectController::class, 'index'])->name('superadmin.reject');
    Route::get('/reject/detail/{id}', [RejectController::class, 'detail'])->name('superadmin.reject_detail');
});