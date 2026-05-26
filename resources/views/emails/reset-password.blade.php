<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f2f7ff; padding: 40px 20px; text-align: center;">
    <div style="max-width: 480px; margin: 0 auto; background-color: #ffffff; padding: 40px 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        
        <div style="font-size: 26px; font-weight: 800; color: #1e2d5a; margin-bottom: 20px; letter-spacing: -0.5px;">h<span style="color:#435ebe;">i</span>ro</div>
        
        <h2 style="color: #333333; font-size: 20px; margin-bottom: 15px;">Atur Ulang Password</h2>
        <p style="color: #555555; font-size: 15px; line-height: 1.6; margin-bottom: 30px;">
            Halo, kami menerima permintaan untuk mengatur ulang password akun Anda. Silakan klik tombol di bawah ini untuk membuat password baru.
        </p>
        
        <a href="{{ route('password.reset', ['token' => $token, 'email' => $email]) }}" 
           style="display: inline-block; background-color: #435ebe; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
           Reset Password Saya
        </a>
        
        <p style="color: #888888; font-size: 13px; line-height: 1.5; margin-top: 35px; border-top: 1px solid #eeeeee; padding-top: 20px;">
            Tautan ini hanya berlaku selama 60 menit.<br>
            Jika Anda tidak meminta pengaturan ulang password, abaikan saja email ini.
        </p>

    </div>
</div>