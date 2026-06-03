<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

// Controller untuk mengelola alur Lupa Password & Reset Password (2 fase)
class ResetPasswordController extends Controller
{
    // =========================================================================
    // FASE 1: PERMINTAAN RESET — Pengguna memasukkan email untuk minta link reset
    // =========================================================================

    // Menampilkan halaman form "Lupa Password"
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // Memproses permintaan reset: membuat token dan mengirim email ke pengguna
    public function sendResetLink(Request $request)
    {
        // Validasi bahwa input email memiliki format yang benar
        $request->validate(['email' => 'required|email']);

        // Cari user berdasarkan email (tidak memberi tahu jika email tidak terdaftar — demi keamanan)
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Buat token acak 64 karakter sebagai kunci reset yang aman
            $token = Str::random(64);

            // Simpan atau perbarui token di tabel password_reset_tokens
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

            // Kirim email berisi link reset dengan token yang disematkan
            Mail::send('emails.reset-password', ['token' => $token, 'email' => $request->email], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Password HRIS');
            });
        }

        // Pesan generik: tidak mengungkap apakah email terdaftar atau tidak
        return back()->with('success', 'Jika email terdaftar, link reset telah dikirim ke kotak masuk Anda.');
    }

    // =========================================================================
    // FASE 2: PEMBARUAN PASSWORD — Pengguna membuat password baru via link email
    // =========================================================================

    // Menampilkan halaman form "Buat Password Baru" setelah klik link di email
    public function showResetForm(Request $request, $token)
    {
        // Teruskan token dan email (dari query string di URL) ke view
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    // Memproses penyimpanan password baru setelah validasi token
    public function updatePassword(Request $request)
    {
        // Validasi input form reset password
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required',
            'password' => 'required|min:8|confirmed', // Field 'password_confirmation' wajib ada di form
        ]);

        // Cari record token yang cocok dengan email dan token yang dikirim
        $resetRequest = DB::table('password_reset_tokens')
                            ->where('email', $request->email)
                            ->where('token', $request->token)
                            ->first();

        // Tolak jika token tidak ditemukan atau tidak cocok
        if (!$resetRequest) {
            return back()->withErrors(['email' => 'Token reset password tidak valid.']);
        }

        // Tolak jika token sudah kadaluarsa (lebih dari 60 menit)
        if (Carbon::parse($resetRequest->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Tautan reset sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        // Perbarui password pengguna di tabel users dengan password baru yang di-hash
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Hapus token dari database agar tidak bisa dipakai ulang
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Redirect ke halaman login dengan pesan sukses
        return redirect('/login')->with('success', 'Password berhasil diubah! Silakan login.');
    }
}
