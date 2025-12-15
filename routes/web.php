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
use App\Http\Controllers\mitra\AllProjectController as MAllProjectController;
use App\Http\Controllers\mitra\MakeProjectController as MMakeProjectController;
use App\Http\Controllers\mitra\ProcessController as MProcessController;
use App\Http\Controllers\mitra\AccController as MAccController;
use App\Http\Controllers\mitra\RejectController as MRejectController;

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
    Route::get('/allproject/detail/{id}', [TAAllProjectController::class, 'detail'])->name('telkomakses.allproject_detail');
    Route::get('/allproject/download', [TAAllProjectController::class, 'downloadPDF'])->name('telkomakses.allproject_download');
    // Process
    Route::get('/process', [TAProcessController::class, 'index'])->name('telkomakses.process');
    Route::get('/process/detail/{id}', [TAProcessController::class, 'detail'])->name('telkomakses.process_detail');
    Route::post('/process/{id}/acc', [TAProcessController::class, 'acc'])->name('telkomakses.process.acc');
    Route::post('/process/{id}/reject', [TAProcessController::class, 'reject'])->name('telkomakses.process.reject');
    // acc
    Route::get('/acc', [TAAccController::class, 'index'])->name('telkomakses.acc');
    Route::get('/acc/detail/{id}', [TAAccController::class, 'detail'])->name('telkomakses.acc_detail');
    // reject
    Route::get('/reject', [TARejectController::class, 'index'])->name('telkomakses.reject');
    Route::get('/reject/detail/{id}', [TARejectController::class, 'detail'])->name('telkomakses.reject_detail');
});

// Mitra
Route::prefix('mitra')->group(function () {
    // make project
    Route::get('/makeproject', [MMakeProjectController::class, 'index'])->name('mitra.makeproject');
    Route::post('/makeproject', [MMakeProjectController::class, 'store'])->name('mitra.makeproject_store');
    // all project
    Route::get('/allproject', [MAllProjectController::class, 'index'])->name('mitra.allproject');
    Route::post('/allproject', [MAllProjectController::class, 'create'])->name('mitra.allproject_create');
    Route::get('/allproject/detail/{id}', [MAllProjectController::class, 'detail'])->name('mitra.allproject_detail');
    Route::delete('/allproject/detail/{id}/destroy/{detailId}', [MAllProjectController::class, 'destroy'])
        ->name('mitra.allproject_destroy');
    Route::get('/allproject/edit/{id}', [MAllProjectController::class, 'edit'])->name('mitra.allproject_edit');
    Route::put('/allproject/update/{id}', [MAllProjectController::class, 'update'])->name('mitra.allproject_update');
    Route::delete('/allproject/{id}/destroy', [MAllProjectController::class, 'destroyProject'])
        ->name('mitra.allproject_destroy_project');
    Route::get('/allproject/download', [MAllProjectController::class, 'downloadPDF'])->name('mitra.allproject_download');
    // Process
    Route::get('/process', [MProcessController::class, 'index'])->name('mitra.process');
    Route::get('/process/detail/{id}', [MProcessController::class, 'detail'])->name('mitra.process_detail');
    Route::delete('/process/detail/{id}/destroy/{detailId}', [MProcessController::class, 'destroy'])
        ->name('mitra.process_destroy');
    Route::get('/process/edit/{id}', [MProcessController::class, 'edit'])->name('mitra.process_edit');
    Route::put('/process/update/{id}', [MProcessController::class, 'update'])->name('mitra.process_update');
    Route::delete('/process/{id}/destroy', [MProcessController::class, 'destroyProject'])
        ->name('mitra.process_destroy_project');
    Route::post('/process/{id}/acc', [MProcessController::class, 'acc'])->name('mitra.process.acc');
    Route::post('/process/{id}/reject', [MProcessController::class, 'reject'])->name('mitra.process.reject');
    // acc
    Route::get('/acc', [MAccController::class, 'index'])->name('mitra.acc');
    Route::get('/acc/detail/{id}', [MAccController::class, 'detail'])->name('mitra.acc_detail');
    Route::delete('/acc/detail/{id}/destroy/{detailId}', [MAccController::class, 'destroy'])
        ->name('mitra.acc_destroy');
    Route::get('/acc/edit/{id}', [MAccController::class, 'edit'])->name('mitra.acc_edit');
    Route::put('/acc/update/{id}', [MAccController::class, 'update'])->name('mitra.acc_update');
    Route::delete('/acc/{id}/destroy', [MAccController::class, 'destroyProject'])
        ->name('mitra.acc_destroy_project');
    Route::post('/acc/{id}/kerjakan', [MAccController::class, 'kerjakan'])->name('mitra.acc.kerjakan');
    Route::post('/acc/{id}/done', [MAccController::class, 'storeFoto'])->name('mitra.acc.storeFoto');
    Route::post('/acc/{id}/pending', [MAccController::class, 'pending'])->name('mitra.acc.pending');
    // reject
    Route::get('/reject', [MRejectController::class, 'index'])->name('mitra.reject');
    Route::get('/reject/detail/{id}', [MRejectController::class, 'detail'])->name('mitra.reject_detail');
    Route::delete('/reject/detail/{id}/destroy/{detailId}', [MRejectController::class, 'destroy'])
        ->name('mitra.reject_destroy');
    Route::get('/reject/edit/{id}', [MRejectController::class, 'edit'])->name('mitra.reject_edit');
    Route::put('/reject/update/{id}', [MRejectController::class, 'update'])->name('mitra.reject_update');
    Route::delete('/reject/{id}/destroy', [MRejectController::class, 'destroyProject'])
        ->name('mitra.reject_destroy_project');
    Route::post('/reject/{id}/upload-revisi', [MRejectController::class, 'updateRevisi'])
        ->name('mitra.reject_upload_revisi');
});

// use App\Http\Controllers\DebugController;

// // HALAMAN FORM DEBUG
// Route::get('/debug-upload', function () {
//     return view('debug-upload');
// });

// PROSES UPLOAD DEBUG
// Route::post('/debug-upload', [DebugController::class, 'upload']);