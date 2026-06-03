{{-- Halaman Lupa Password — pengguna memasukkan email untuk meminta link reset --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — Hiro</title>
    <link rel="icon" href="/images/logo-icon.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background-color: #f2f7ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .auth-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .auth-logo h1 { color: #435ebe; font-size: 2.2rem; font-weight: 700; text-align: center; margin-bottom: 30px; }
        .auth-header { margin-bottom: 25px; }
        .auth-header h2 { font-size: 1.5rem; color: #25396f; margin-bottom: 10px; font-weight: 600; }
        .auth-header p { color: #7c8db5; font-size: 0.9rem; line-height: 1.5; }

        .form-group { margin-bottom: 25px; position: relative; }

        .form-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #a6b0cf; pointer-events: none; }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #dce7f1;
            border-radius: 8px;
            font-size: 0.95rem;
            color: #25396f;
            transition: 0.3s;
            background: #fff;
        }

        .form-control:focus { outline: none; border-color: #435ebe; box-shadow: 0 0 0 3px rgba(67, 94, 190, 0.1); }

        .btn-primary {
            width: 100%;
            background-color: #435ebe;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-primary:hover { background-color: #324c9e; }

        /* Link kembali ke halaman login */
        .back-link { display: block; margin-top: 20px; text-align: center; color: #607080; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .back-link:hover { color: #435ebe; }

        .error-msg { color: #dc3545; font-size: 0.8rem; margin-top: 5px; display: block; }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="auth-logo" style="text-align:center;margin-bottom:28px;">
            <img src="{{ asset('images/logo.svg') }}" alt="Hiro" style="height:46px;display:inline-block;">
        </div>

        <div class="auth-header">
            <h2>Lupa Password?</h2>
            <p>Masukkan email Anda. Kami akan mengirimkan link untuk mereset password akun Anda.</p>
        </div>

        {{-- Form dikirim ke ResetPasswordController@sendResetLink via POST --}}
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <div class="form-icon">
                    {{-- Ikon email/amplop --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                </div>
                <input type="email" name="email" class="form-control" placeholder="Masukkan Email Anda" value="{{ old('email') }}" required autofocus>
                @error('email') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn-primary">Kirim Link Reset</button>

            {{-- Link kembali ke halaman login --}}
            <a href="{{ route('login') }}" class="back-link">
                &larr; Kembali ke Login
            </a>
        </form>
    </div>

@include('components.toast-notification')
</body>
</html>
