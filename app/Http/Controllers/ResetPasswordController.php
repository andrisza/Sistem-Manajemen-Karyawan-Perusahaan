<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class ResetPasswordController extends Controller
{
    // --- FASE 1: PERMINTAAN RESET ---

    // 1. Tampilkan UI halaman Lupa Password
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // 2. Proses pengiriman email
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(64); // Buat token rahasia

            // Simpan token ke database
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

            // Kirim email
            Mail::send('emails.reset-password', ['token' => $token, 'email' => $request->email], function($message) use($request){
                $message->to($request->email);
                $message->subject('Reset Password HRIS');
            });
        }

        return back()->with('success', 'Jika email terdaftar, link reset telah dikirim ke kotak masuk Anda.');
    }


    // --- FASE 2: PEMBARUAN PASSWORD ---

    // 3. Tampilkan UI halaman Buat Password Baru (dari link email)
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    // 4. Proses penyimpanan password baru
    public function updatePassword(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed', // Harus ada input 'password_confirmation' di form
        ]);

        // Cek kecocokan email dan token di database
        $resetRequest = DB::table('password_reset_tokens')
                            ->where('email', $request->email)
                            ->where('token', $request->token)
                            ->first();

        // Jika token salah / tidak ada
        if (!$resetRequest) {
            return back()->withErrors(['email' => 'Token reset password tidak valid.']);
        }

        // Jika token sudah kedaluwarsa (lebih dari 60 menit)
        if (Carbon::parse($resetRequest->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Tautan reset sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        // Update password di tabel users
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Hapus token yang sudah dipakai
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect('/login')->with('success', 'Password berhasil diubah! Silakan login.');
    }
}