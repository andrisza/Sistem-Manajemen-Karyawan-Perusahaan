<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ActivationController;
use App\Http\Controllers\ResetPasswordController;

Route::get('/', function () {
    return redirect()->route('login');
});

// 1. MEMUAT ROUTE BAWAAN LARAVEL (Ditaruh di atas agar bisa kita timpa jika ada yang sama)
require __DIR__.'/auth.php';


// =========================================================================
// KAMAR PUBLIK (Bisa diakses oleh karyawan yang BELUM LOGIN / GUEST)
// =========================================================================
Route::middleware('guest')->group(function () {
    
    // Fitur Aktivasi Akun
    Route::get('/activate/{user}', [ActivationController::class, 'showForm'])->name('activation.form')->middleware('signed');
    Route::post('/activate/{user}', [ActivationController::class, 'activate'])->name('activation.submit');

    // Fitur Lupa Password 
    // (Karena diletakkan di bawah require auth.php, controller ini yang akan menang dan error $token PASTI HILANG)
    Route::get('/forgot-password', [ResetPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'updatePassword'])->name('password.update');
    
});


// =========================================================================
// KAMAR PRIVAT (Hanya boleh diakses oleh karyawan yang SUDAH LOGIN / AUTH)
// =========================================================================
Route::middleware('auth')->group(function () {
    
    // Fitur Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware(['role:HR,Developer,Sales']);
    Route::get('/dashboard/presences', [DashboardController::class, 'presence']);
    
    // Fitur Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Fitur Karyawan, Departemen, & Role (Khusus HR)
    Route::resource('/employees', EmployeeController::class)->middleware(['role:HR']);
    Route::resource('/departments', DepartmentController::class)->middleware(['role:HR']);
    Route::resource('/roles', RoleController::class)->middleware(['role:HR']);

    // Fitur Tugas
    Route::resource('/tasks', TaskController::class)->middleware(['role:HR,Developer,Sales']);
    Route::get('/tasks/done/{id}', [TaskController::class, 'done'])->name('tasks.done')->middleware(['role:HR,Developer,Sales']);
    Route::get('/tasks/pending/{id}', [TaskController::class, 'pending'])->name('tasks.pending')->middleware(['role:HR,Developer,Sales']);
    
    // Fitur Presensi
    Route::resource('/presences', PresenceController::class)->middleware(['role:HR,Developer,Sales']);
    Route::post('/presences/checkout', [PresenceController::class, 'checkout'])->name('presences.checkout');
    
    // Fitur Penggajian & QR Code
    Route::resource('payrolls', PayrollController::class)->middleware(['role:HR,Developer,Sales']);
    Route::get('/payroll/{id}/download', [PayrollController::class, 'downloadSlip'])->name('payroll.download');
    Route::get('/payroll/verify/{id}/{hash}', [PayrollController::class, 'verify'])->name('payroll.verify');

    // Fitur Cuti
    Route::resource('leave-requests', LeaveRequestController::class)->middleware(['role:HR,Developer,Sales']);
    Route::get('/leave-requests/confirm/{id}', [LeaveRequestController::class, 'confirm'])->name('leave-requests.confirm')->middleware(['role:HR']);
    Route::get('/leave-requests/reject/{id}', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject')->middleware(['role:HR']);

});