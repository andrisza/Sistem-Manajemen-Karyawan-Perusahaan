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

require __DIR__.'/auth.php';

// =========================================================================
// PUBLIK — Aktivasi Akun
// URL signed sudah menjamin keamanan, tidak butuh auth/guest middleware
// =========================================================================
Route::get('/activate/{user}',  [ActivationController::class, 'showForm'])->name('activation.form')->middleware('signed');
Route::post('/activate/{user}', [ActivationController::class, 'activate'])->name('activation.submit');

// =========================================================================
// GUEST — Lupa & Reset Password
// =========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password',          [ResetPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password',         [ResetPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}',   [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',          [ResetPasswordController::class, 'updatePassword'])->name('password.update');
});

// =========================================================================
// AUTH — Semua karyawan yang sudah login (role apapun)
// Session role di-set otomatis oleh SetSessionRole middleware (via web group)
// =========================================================================
Route::middleware('auth')->group(function () {

    // ── Dashboard & Profil (semua role) ──────────────────────────────
    Route::get('/dashboard',          [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/presences', [DashboardController::class, 'presence']);

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Fitur Umum (semua role) ───────────────────────────────────────
    Route::resource('/tasks',         TaskController::class);
    Route::get('/tasks/done/{id}',    [TaskController::class, 'done'])->name('tasks.done');
    Route::get('/tasks/pending/{id}', [TaskController::class, 'pending'])->name('tasks.pending');

    Route::resource('/presences',      PresenceController::class);
    Route::post('/presences/checkout', [PresenceController::class, 'checkout'])->name('presences.checkout');

    Route::resource('payrolls', PayrollController::class);
    Route::get('/payroll/{id}/download',       [PayrollController::class, 'downloadSlip'])->name('payroll.download');
    Route::get('/payroll/verify/{id}/{hash}',  [PayrollController::class, 'verify'])->name('payroll.verify');

    Route::resource('leave-requests', LeaveRequestController::class);

    // ── Fitur Eksklusif HR ────────────────────────────────────────────
    Route::resource('/employees',   EmployeeController::class)->middleware('role:HR');
    Route::resource('/departments', DepartmentController::class)->middleware('role:HR');
    Route::resource('/roles',       RoleController::class)->middleware('role:HR');

    Route::get('/leave-requests/confirm/{id}', [LeaveRequestController::class, 'confirm'])->name('leave-requests.confirm')->middleware('role:HR');
    Route::get('/leave-requests/reject/{id}',  [LeaveRequestController::class, 'reject'])->name('leave-requests.reject')->middleware('role:HR');
});
