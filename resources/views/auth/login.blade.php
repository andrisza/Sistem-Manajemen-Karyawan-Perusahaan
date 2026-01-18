<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HRIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS RESET & VARIABLES */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body {
            background-color: #f2f7ff; /* Background Dashboard */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* CARD STYLE */
        .auth-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        /* TYPOGRAPHY */
        .auth-logo h1 { color: #435ebe; font-size: 2.2rem; font-weight: 700; text-align: center; margin-bottom: 30px; }
        .auth-header { margin-bottom: 25px; }
        .auth-header h2 { font-size: 1.5rem; color: #25396f; margin-bottom: 5px; font-weight: 600; }
        .auth-header p { color: #7c8db5; font-size: 0.9rem; }

        /* FORM ELEMENTS */
        .form-group { margin-bottom: 20px; position: relative; }
        
        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a6b0cf;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px; /* Space for icon */
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

        /* BUTTON */
        .btn-primary {
            width: 100%;
            background-color: #435ebe; /* Primary Blue */
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

        /* EXTRAS */
        .remember-box { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; font-size: 0.9rem; }
        .remember-me { display: flex; align-items: center; color: #607080; }
        .remember-me input { margin-right: 8px; accent-color: #435ebe; width: 16px; height: 16px; }
        
        .forgot-link { color: #435ebe; text-decoration: none; font-weight: 500; }
        .forgot-link:hover { text-decoration: underline; }

        .error-msg { color: #dc3545; font-size: 0.8rem; margin-top: 5px; display: block; }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="auth-logo">
            <h1>HRIS</h1>
        </div>

        <div class="auth-header">
            <h2>Log in</h2>
            <p>Masuk dengan akun terdaftar Anda.</p>
        </div>

        @if (session('status'))
            <div style="color: #2d8f56; background: #e6f7ed; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <div class="form-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required autofocus>
                @error('email') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <div class="form-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                @error('password') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="remember-box">
                <div class="remember-me">
                    <input type="checkbox" id="remember_me" name="remember">
                    <label for="remember_me">Ingat Saya</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Lupa Password?</a>
                @endif
            </div>

            <button type="submit" class="btn-primary">Log In</button>
        </form>
    </div>

</body>
</html>