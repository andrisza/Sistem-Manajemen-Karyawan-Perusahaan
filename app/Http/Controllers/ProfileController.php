<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// Controller untuk mengelola profil akun pengguna yang sedang login
class ProfileController extends Controller
{
    // Menampilkan halaman form edit profil pengguna
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(), // Kirim data user yang sedang login ke view
        ]);
    }

    // Memproses dan menyimpan perubahan informasi profil (nama, email, dll)
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Isi model user dengan data yang telah divalidasi oleh ProfileUpdateRequest
        $request->user()->fill($request->validated());

        // Jika email diubah, reset status verifikasi email menjadi null (belum terverifikasi)
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save(); // Simpan perubahan ke database

        // Redirect kembali ke halaman profil dengan status sukses
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // Menghapus akun pengguna setelah konfirmasi password
    public function destroy(Request $request): RedirectResponse
    {
        // Validasi password saat ini sebelum menghapus akun
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user(); // Ambil data user yang akan dihapus

        Auth::logout(); // Logout dari sesi saat ini sebelum menghapus akun

        $user->delete(); // Hapus akun dari database

        // Invalidasi sesi dan regenerasi CSRF token untuk keamanan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman utama setelah akun berhasil dihapus
        return Redirect::to('/');
    }
}
