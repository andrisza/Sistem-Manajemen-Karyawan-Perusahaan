{{-- Halaman Login — halaman pertama yang dilihat pengguna saat membuka aplikasi --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Hiro</title>
    <link rel="icon" href="/images/logo-icon.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS RESET: hapus margin/padding bawaan browser dan atur box model */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        /* Pusatkan kartu login secara vertikal dan horizontal di tengah layar */
        body {
            background-color: #f2f7ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Kartu putih yang berisi form login */
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
        .auth-header h2 { font-size: 1.5rem; color: #25396f; margin-bottom: 5px; font-weight: 600; }
        .auth-header p { color: #7c8db5; font-size: 0.9rem; }

        /* Wrapper untuk setiap field input agar bisa memposisikan ikon di dalamnya */
        .form-group { margin-bottom: 20px; position: relative; }

        /* Ikon di sisi kiri input (envelope/lock) */
        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a6b0cf;
            pointer-events: none; /* Ikon tidak menghalangi klik pada input */
        }

        /* Style input field dengan padding kiri ekstra untuk memberi ruang ikon */
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

        .form-control:focus {
            outline: none;
            border-color: #435ebe;
            box-shadow: 0 0 0 3px rgba(67, 94, 190, 0.1);
        }

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

        .remember-box { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; font-size: 0.9rem; }
        .remember-me { display: flex; align-items: center; color: #607080; }
        .remember-me input { margin-right: 8px; accent-color: #435ebe; width: 16px; height: 16px; }

        .forgot-link { color: #435ebe; text-decoration: none; font-weight: 500; }
        .forgot-link:hover { text-decoration: underline; }

        /* Style untuk pesan error validasi di bawah input */
        .error-msg { color: #dc3545; font-size: 0.8rem; margin-top: 5px; display: block; }
    </style>
</head>
<body>

    <div class="auth-card">
        {{-- Logo aplikasi --}}
        <div class="auth-logo" style="text-align:center;margin-bottom:28px;">
            <img src="{{ asset('images/logo.svg') }}" alt="Hiro" style="height:46px;display:inline-block;">
        </div>

        <div class="auth-header">
            <h2>Log in</h2>
            <p>Masuk dengan akun terdaftar Anda.</p>
        </div>

        {{-- Form login dikirim ke route 'login' yang ditangani oleh Laravel Breeze (routes/auth.php) --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf {{-- Token CSRF wajib untuk keamanan --}}

            <div class="form-group">
                <div class="form-icon">
                    {{-- Ikon user/avatar --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                {{-- old('email') mengembalikan nilai yang diketik sebelumnya jika login gagal --}}
                <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required autofocus>
                @error('email') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <div class="form-icon">
                    {{-- Ikon kunci --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                @error('password') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="remember-box">
                {{-- Checkbox "Ingat Saya" — memperpanjang sesi login --}}
                <div class="remember-me">
                    <input type="checkbox" id="remember_me" name="remember">
                    <label for="remember_me">Ingat Saya</label>
                </div>
                {{-- Link lupa password — hanya tampil jika route 'password.request' terdaftar --}}
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Lupa Password?</a>
                @endif
            </div>

            <button type="submit" class="btn-primary">Log In</button>
        </form>
    </div>

{{-- Komponen toast notification untuk menampilkan pesan sukses/error (session flash) --}}
@include('components.toast-notification')
</body>
</html>
