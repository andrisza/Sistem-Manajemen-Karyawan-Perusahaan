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

// Halaman utama (/) langsung diarahkan ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

// File ini memuat route-route autentikasi bawaan Laravel (login, logout, register, dll)
require __DIR__.'/auth.php';

// =========================================================================
// PUBLIK — Aktivasi Akun Karyawan Baru
// Menggunakan signed URL untuk keamanan: URL berisi tanda tangan kriptografis
// yang memastikan link hanya valid 24 jam dan tidak bisa dimanipulasi
// =========================================================================
Route::get('/activate/{user}',  [ActivationController::class, 'showForm'])->name('activation.form')->middleware('signed');
Route::post('/activate/{user}', [ActivationController::class, 'activate'])->name('activation.submit');

// =========================================================================
// GUEST — Lupa & Reset Password
// Middleware 'guest' memastikan hanya pengguna yang BELUM login yang bisa akses
// =========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password',        [ResetPasswordController::class, 'showForgotForm'])->name('password.request'); // Tampilkan form lupa password
    Route::post('/forgot-password',       [ResetPasswordController::class, 'sendResetLink'])->name('password.email');   // Proses kirim email reset
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');   // Tampilkan form buat password baru
    Route::post('/reset-password',        [ResetPasswordController::class, 'updatePassword'])->name('password.update'); // Proses simpan password baru
});

// =========================================================================
// AUTH — Semua pengguna yang sudah login (role apapun boleh akses)
// Middleware 'auth' memastikan hanya pengguna yang sudah login yang bisa akses
// =========================================================================
Route::middleware('auth')->group(function () {

    // ── Dashboard & Profil (tersedia untuk semua role) ─────────────────────
    Route::get('/dashboard',           [DashboardController::class, 'index'])->name('dashboard');        // Halaman utama setelah login
    Route::get('/dashboard/presences', [DashboardController::class, 'presence']);                        // Endpoint JSON data grafik presensi

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');     // Tampilkan form edit profil
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update'); // Proses simpan perubahan profil
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); // Hapus akun pengguna

    // ── Modul Tugas — semua role dapat mengakses ────────────────────────────
    Route::resource('/tasks',         TaskController::class);                                                       // CRUD tugas (index, create, store, show, edit, update, destroy)
    Route::get('/tasks/done/{id}',    [TaskController::class, 'done'])->name('tasks.done');                        // Tandai tugas sebagai selesai
    Route::get('/tasks/pending/{id}', [TaskController::class, 'pending'])->name('tasks.pending');                  // Kembalikan tugas ke status pending

    // ── Modul Presensi — semua role dapat mengakses ─────────────────────────
    Route::resource('/presences',      PresenceController::class);                                                  // CRUD presensi
    Route::post('/presences/checkout', [PresenceController::class, 'checkout'])->name('presences.checkout');       // Proses check-out karyawan

    // ── Modul Payroll — semua role dapat melihat, hanya HR yang bisa kelola ─
    Route::resource('payrolls', PayrollController::class);                                                         // CRUD payroll
    Route::get('/payroll/{id}/download',      [PayrollController::class, 'downloadSlip'])->name('payroll.download'); // Unduh slip gaji PDF
    Route::get('/payroll/verify/{id}/{hash}', [PayrollController::class, 'verify'])->name('payroll.verify');        // Verifikasi keaslian slip via QR Code

    // ── Modul Cuti — semua role dapat mengakses ─────────────────────────────
    Route::resource('leave-requests', LeaveRequestController::class);                                              // CRUD pengajuan cuti

    // ── Fitur Eksklusif HR — hanya role 'HR' yang boleh akses ───────────────
    // Middleware 'role:HR' akan menolak akses jika session role bukan 'HR'
    Route::resource('/employees',   EmployeeController::class)->middleware('role:HR');   // CRUD data karyawan
    Route::resource('/departments', DepartmentController::class)->middleware('role:HR'); // CRUD departemen
    Route::resource('/roles',       RoleController::class)->middleware('role:HR');       // CRUD jabatan

    // Aksi konfirmasi dan penolakan pengajuan cuti — hanya HR
    Route::get('/leave-requests/confirm/{id}', [LeaveRequestController::class, 'confirm'])->name('leave-requests.confirm')->middleware('role:HR'); // Setujui cuti
    Route::get('/leave-requests/reject/{id}',  [LeaveRequestController::class, 'reject'])->name('leave-requests.reject')->middleware('role:HR');   // Tolak cuti
});
