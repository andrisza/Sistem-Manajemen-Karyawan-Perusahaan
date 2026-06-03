<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

// Controller untuk mengelola proses aktivasi akun karyawan baru via link email
class ActivationController extends Controller
{
    // Menampilkan form aktivasi akun — dipanggil saat karyawan mengklik link email undangan
    public function showForm(Request $request, User $user)
    {
        // Verifikasi bahwa URL yang diakses memiliki signature yang valid dan belum kadaluarsa (24 jam)
        if (!$request->hasValidSignature()) {
            abort(401, 'Tautan aktivasi tidak valid atau sudah kedaluwarsa.');
        }

        // Jika ada sesi login aktif (misal HR yang sedang login), logout dulu
        // agar form aktivasi bisa diisi oleh karyawan baru tanpa konflik sesi
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Tampilkan form isian password baru, kirim data user agar nama bisa ditampilkan di form
        return view('auth.activate', compact('user'));
    }

    // Memproses pengiriman form aktivasi: simpan password baru dan login otomatis
    public function activate(Request $request, User $user)
    {
        // Validasi password baru: minimal 8 karakter dan harus cocok dengan konfirmasi
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        // Simpan password baru yang sudah di-hash ke database
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Login otomatis setelah akun berhasil diaktifkan
        Auth::login($user);

        // Ambil data karyawan beserta jabatannya untuk dimasukkan ke session
        $employee = Employee::with('role')->find($user->employee_id);

        if ($employee) {
            // Ambil nama jabatan (title) dari relasi role, default 'Employee' jika tidak ada
            $roleName = $employee->role ? $employee->role->title : 'Employee';

            // Simpan nama role dan ID karyawan ke session — digunakan di seluruh aplikasi untuk RBAC
            $request->session()->put('role', $roleName);
            $request->session()->put('employee_id', $employee->id);
        }

        // Redirect ke dashboard setelah aktivasi berhasil
        return redirect('/dashboard')->with('success', 'Akun berhasil diaktifkan! Selamat datang di dashboard Anda.');
    }
}
