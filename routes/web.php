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
use App\Http\Controllers\telkom_akses\AllProjectController as TAAllProjectController;
use App\Http\Controllers\telkom_akses\ProcessController as TAProcessController;
use App\Http\Controllers\telkom_akses\AccController as TAAccController;
use App\Http\Controllers\telkom_akses\RejectController as TARejectController;


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
    // all project
    Route::get('/allproject', [AllProjectController::class, 'index'])->name('superadmin.allproject');
    Route::post('/allproject', [AllProjectController::class, 'create'])->name('superadmin.allproject_create');
    Route::get('/allproject/detail/{id}', [AllProjectController::class, 'detail'])->name('superadmin.allproject_detail');
    Route::delete('/allproject/detail/{id}/destroy/{detailId}', [AllProjectController::class, 'destroy'])
        ->name('superadmin.allproject_destroy');
    Route::get('/allproject/edit/{id}', [AllProjectController::class, 'edit'])->name('superadmin.allproject_edit');
    Route::put('/allproject/update/{id}', [AllProjectController::class, 'update'])->name('superadmin.allproject_update');
    Route::delete('/allproject/{id}/destroy', [AllProjectController::class, 'destroyProject'])
        ->name('superadmin.allproject_destroy_project');
    Route::get('/allproject/download', [AllProjectController::class, 'downloadPDF'])->name('superadmin.allproject_download');
    // Process
    Route::get('/process', [ProcessController::class, 'index'])->name('superadmin.process');
    Route::get('/process/detail/{id}', [ProcessController::class, 'detail'])->name('superadmin.process_detail');
    Route::delete('/process/detail/{id}/destroy/{detailId}', [ProcessController::class, 'destroy'])
        ->name('superadmin.process_destroy');
    Route::get('/process/edit/{id}', [ProcessController::class, 'edit'])->name('superadmin.process_edit');
    Route::put('/process/update/{id}', [ProcessController::class, 'update'])->name('superadmin.process_update');
    Route::delete('/process/{id}/destroy', [ProcessController::class, 'destroyProject'])
        ->name('superadmin.process_destroy_project');
    Route::post('/process/{id}/acc', [ProcessController::class, 'acc'])->name('superadmin.process.acc');
    Route::post('/process/{id}/reject', [ProcessController::class, 'reject'])->name('superadmin.process.reject');
    // acc
    Route::get('/acc', [AccController::class, 'index'])->name('superadmin.acc');
    Route::get('/acc/detail/{id}', [AccController::class, 'detail'])->name('superadmin.acc_detail');
    Route::delete('/acc/detail/{id}/destroy/{detailId}', [AccController::class, 'destroy'])
        ->name('superadmin.acc_destroy');
    Route::get('/acc/edit/{id}', [AccController::class, 'edit'])->name('superadmin.acc_edit');
    Route::put('/acc/update/{id}', [AccController::class, 'update'])->name('superadmin.acc_update');
    Route::delete('/acc/{id}/destroy', [AccController::class, 'destroyProject'])
        ->name('superadmin.acc_destroy_project');
    Route::post('/acc/{id}/kerjakan', [AccController::class, 'kerjakan'])->name('superadmin.acc.kerjakan');
    Route::post('/acc/{id}/done', [AccController::class, 'storeFoto'])->name('superadmin.acc.storeFoto');
    Route::post('/acc/{id}/pending', [AccController::class, 'pending'])->name('superadmin.acc.pending');
    // reject
    Route::get('/reject', [RejectController::class, 'index'])->name('superadmin.reject');
    Route::get('/reject/detail/{id}', [RejectController::class, 'detail'])->name('superadmin.reject_detail');
    Route::delete('/reject/detail/{id}/destroy/{detailId}', [RejectController::class, 'destroy'])
        ->name('superadmin.reject_destroy');
    Route::get('/reject/edit/{id}', [RejectController::class, 'edit'])->name('superadmin.reject_edit');
    Route::put('/reject/update/{id}', [RejectController::class, 'update'])->name('superadmin.reject_update');
    Route::delete('/reject/{id}/destroy', [RejectController::class, 'destroyProject'])
        ->name('superadmin.reject_destroy_project');
    Route::post('/reject/{id}/upload-revisi', [RejectController::class, 'updateRevisi'])
        ->name('superadmin.reject_upload_revisi');
});

// Telkom Akses
Route::prefix('telkomakses')->group(function () {
    // all project
    Route::get('/allproject', [TAAllProjectController::class, 'index'])->name('telkomakses.allproject');
    Route::post('/allproject', [TAAllProjectController::class, 'create'])->name('telkomakses.allproject_create');
    Route::get('/allproject/detail/{id}', [TAAllProjectController::class, 'detail'])->name('telkomakses.allproject_detail');
    Route::delete('/allproject/detail/{id}/destroy/{detailId}', [TAAllProjectController::class, 'destroy'])
        ->name('telkomakses.allproject_destroy');
    Route::get('/allproject/edit/{id}', [TAAllProjectController::class, 'edit'])->name('telkomakses.allproject_edit');
    Route::put('/allproject/update/{id}', [TAAllProjectController::class, 'update'])->name('telkomakses.allproject_update');
    Route::delete('/allproject/{id}/destroy', [TAAllProjectController::class, 'destroyProject'])
        ->name('telkomakses.allproject_destroy_project');
    Route::get('/allproject/download', [TAAllProjectController::class, 'downloadPDF'])->name('telkomakses.allproject_download');
    // Process
    Route::get('/process', [TAProcessController::class, 'index'])->name('telkomakses.process');
    Route::get('/process/detail/{id}', [TAProcessController::class, 'detail'])->name('telkomakses.process_detail');
    Route::delete('/process/detail/{id}/destroy/{detailId}', [TAProcessController::class, 'destroy'])
        ->name('telkomakses.process_destroy');
    Route::get('/process/edit/{id}', [TAProcessController::class, 'edit'])->name('telkomakses.process_edit');
    Route::put('/process/update/{id}', [TAProcessController::class, 'update'])->name('telkomakses.process_update');
    Route::delete('/process/{id}/destroy', [TAProcessController::class, 'destroyProject'])
        ->name('telkomakses.process_destroy_project');
    Route::post('/process/{id}/acc', [TAProcessController::class, 'acc'])->name('telkomakses.process.acc');
    Route::post('/process/{id}/reject', [TAProcessController::class, 'reject'])->name('telkomakses.process.reject');
    // acc
    Route::get('/acc', [TAAccController::class, 'index'])->name('telkomakses.acc');
    Route::get('/acc/detail/{id}', [TAAccController::class, 'detail'])->name('telkomakses.acc_detail');
    Route::delete('/acc/detail/{id}/destroy/{detailId}', [TAAccController::class, 'destroy'])
        ->name('superadmin.acc_destroy');
    Route::get('/acc/edit/{id}', [TAAccController::class, 'edit'])->name('telkomakses.acc_edit');
    Route::put('/acc/update/{id}', [TAAccController::class, 'update'])->name('telkomakses.acc_update');
    Route::delete('/acc/{id}/destroy', [TAAccController::class, 'destroyProject'])
        ->name('superadmin.acc_destroy_project');
    Route::post('/acc/{id}/kerjakan', [TAAccController::class, 'kerjakan'])->name('telkomakses.acc.kerjakan');
    Route::post('/acc/{id}/done', [TAAccController::class, 'storeFoto'])->name('telkomakses.acc.storeFoto');
    Route::post('/acc/{id}/pending', [TAAccController::class, 'pending'])->name('telkomakses.acc.pending');
    // reject
    Route::get('/reject', [TARejectController::class, 'index'])->name('telkomakses.reject');
    Route::get('/reject/detail/{id}', [TARejectController::class, 'detail'])->name('telkomakses.reject_detail');
    Route::delete('/reject/detail/{id}/destroy/{detailId}', [TARejectController::class, 'destroy'])
        ->name('telkomakses.reject_destroy');
    Route::get('/reject/edit/{id}', [TARejectController::class, 'edit'])->name('telkomakses.reject_edit');
    Route::put('/reject/update/{id}', [TARejectController::class, 'update'])->name('telkomakses.reject_update');
    Route::delete('/reject/{id}/destroy', [TARejectController::class, 'destroyProject'])
        ->name('telkomakses.reject_destroy_project');
    Route::post('/reject/{id}/upload-revisi', [TARejectController::class, 'updateRevisi'])
        ->name('telkomakses.reject_upload_revisi');
});

// use App\Http\Controllers\DebugController;

// // HALAMAN FORM DEBUG
// Route::get('/debug-upload', function () {
//     return view('debug-upload');
// });

// PROSES UPLOAD DEBUG
// Route::post('/debug-upload', [DebugController::class, 'upload']);