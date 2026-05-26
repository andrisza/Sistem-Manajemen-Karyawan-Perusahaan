<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Aktivasi Akun — Hiro</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; padding-top: 40px; }
        .email-card { max-width: 550px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .email-header { background-color: #435ebe; padding: 30px; text-align: center; color: #ffffff; }
        .email-header h1 { margin: 0; font-size: 28px; font-weight: bold; letter-spacing: 1px; }
        .email-body { padding: 40px 30px; color: #555b6d; line-height: 1.6; font-size: 16px; }
        .email-body h2 { color: #25396f; margin-top: 0; font-size: 20px; font-weight: bold; }
        .btn-activate { display: inline-block; padding: 12px 30px; background-color: #435ebe; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; text-align: center; }
        .note-box { color: #ef4444; font-size: 14px; background: #fff5f5; padding: 12px; border-radius: 6px; border-left: 4px solid #ef4444; margin-top: 20px; }
        .email-footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 13px; color: #a8afb7; border-top: 1px solid #edf1f5; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="email-header">
            <h1>Hiro</h1>
        </div>
        <div class="email-body">
            <h2>Halo, {{ $user->name }}!</h2>
            <p>Selamat bergabung di <strong>PT. Nusantara Human Capital</strong>! Akun Hiro Anda telah berhasil disiapkan oleh tim HRD.</p>
            <p>Untuk mulai menggunakan sistem, silakan aktifkan akun Anda dan buat kata sandi baru secara mandiri dengan mengklik tombol di bawah ini:</p>
            
            <div style="text-align: center;">
                <a href="{{ $activationLink }}" class="btn-activate">Aktifkan Akun & Set Password</a>
            </div>

            <div class="note-box">
                <strong>PENTING:</strong> Demi keamanan data, tautan aktivasi ini bersifat rahasia dan hanya berlaku selama 24 jam ke depan.
            </div>
            <br>
            <p>Terima kasih,<br><strong>Tim HRD Perusahaan</strong></p>
        </div>
        <div class="email-footer">
            &copy; {{ date('Y') }} PT. Nusantara Human Capital — Hiro. All rights reserved.<br>
            Mohon tidak membalas email otomatis ini.
        </div>
    </div>
</body>
</html>