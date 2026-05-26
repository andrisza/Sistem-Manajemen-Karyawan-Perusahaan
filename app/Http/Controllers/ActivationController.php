<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // TAMBAHAN: Untuk fungsi login
use App\Models\Employee; // TAMBAHAN: Untuk mengambil data karyawan

class ActivationController extends Controller
{
    public function showForm(Request $request, User $user)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Tautan aktivasi tidak valid atau sudah kedaluwarsa.');
        }

        // Jika ada sesi login aktif (misal HR admin), logout dulu agar form aktivasi bisa tampil
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('auth.activate', compact('user'));
    }

 public function activate(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        // 1. Simpan Password Baru
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // 2. Login Otomatis
        Auth::login($user);

        // 3. Ambil data karyawan beserta relasi rolenya secara dinamis
        // Pastikan model Employee Anda sudah memiliki fungsi relasi: public function role()
        $employee = Employee::with('role')->find($user->employee_id);
        
        if ($employee) {
            // Mengambil teks judul role (misal: 'Sales', 'Marketing', 'HR') dari tabel roles
            $roleName = $employee->role ? $employee->role->title : 'Employee';

            // Masukkan teks nama role ke dalam session aplikasi
            $request->session()->put('role', $roleName);
            $request->session()->put('employee_id', $employee->id);
        }

        // 4. Redirect ke dashboard
        return redirect('/dashboard')->with('success', 'Akun berhasil diaktifkan! Selamat datang di dashboard Anda.');
    }
}