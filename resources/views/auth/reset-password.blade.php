<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru — Hiro</title>
    <link rel="icon" href="/images/logo-icon.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f2f7ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 40px;
            width: 100%;
            max-width: 440px;
            text-align: center;
        }
        .auth-logo {
            font-size: 32px;
            font-weight: 800;
            color: #435ebe;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        .btn-primary {
            background-color: #435ebe;
            border-color: #435ebe;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #354a9c;
            border-color: #354a9c;
        }
        .form-control {
            background-color: #ffffff;
            border: 1px solid #dce7f1;
            padding: 12px 12px 12px 40px;
            border-radius: 8px;
        }
        /* Style khusus input readonly */
        .form-control[readonly] {
            background-color: #e9ecef; 
            color: #6c757d;
        }
        .form-control:focus {
            background-color: #fff;
            border-color: #435ebe;
            box-shadow: 0 0 0 0.25rem rgba(67, 94, 190, 0.25);
        }
        .input-group-custom {
            position: relative;
            text-align: left;
        }
        .input-group-custom i.input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            z-index: 10;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-logo" style="font-size:0;">
        <img src="{{ asset('images/logo.svg') }}" alt="Hiro" style="height:40px;display:block;margin:0 auto 20px;">
    </div>
    <h4 class="text-dark fw-bold mb-1">Buat Password Baru</h4>
    <p class="text-muted small mb-4">Silakan masukkan kata sandi baru Anda di bawah ini.</p>

    @if ($errors->any())
        <div class="alert alert-danger small text-start mb-3" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <div class="input-group-custom">
                <i class="fa-solid fa-envelope input-icon"></i>
                <input type="email" name="email" class="form-control" value="{{ $email }}" readonly>
            </div>
        </div>

        <div class="mb-3">
            <div class="input-group-custom">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" name="password" class="form-control" placeholder="Password Baru" required>
            </div>
        </div>

        <div class="mb-4">
            <div class="input-group-custom">
                <i class="fa-solid fa-check-circle input-icon"></i>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi Password Baru" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">Simpan Password Baru</button>
        
        <a href="{{ route('login') }}" class="text-decoration-none small" style="color: #435ebe; font-weight: 600;">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Login
        </a>
    </form>
</div>

@include('components.toast-notification')
</body>
</html>