<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun — Hiro</title>
    <link rel="icon" href="/images/logo-icon.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f4f7fb; /* Warna background mirip halaman login */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .auth-logo {
            font-size: 32px;
            font-weight: 800;
            color: #435ebe; /* Biru khas HRIS Anda */
            margin-bottom: 25px;
        }
        .auth-title {
            font-size: 22px;
            font-weight: 700;
            color: #25396f;
            margin-bottom: 10px;
        }
        .auth-subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #435ebe;
            border-color: #435ebe;
            padding: 10px;
            font-weight: 600;
            border-radius: 6px;
        }
        .btn-primary:hover {
            background-color: #354a9c;
            border-color: #354a9c;
        }
        .form-control {
            background-color: #ffffff;
            border: 1px solid #ced4da;
            padding: 10px 10px 10px 40px;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-control:focus {
            background-color: #fff;
            border-color: #435ebe;
            box-shadow: 0 0 0 0.25rem rgba(67, 94, 190, 0.15);
        }
        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
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
        .input-group-custom .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }
        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #25396f;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-logo" style="font-size:0;">
        <img src="{{ asset('images/logo.svg') }}" alt="Hiro" style="height:40px;display:block;margin:0 auto 20px;">
    </div>
    
    <h4 class="auth-title">Aktivasi Akun</h4>
    <p class="auth-subtitle">Halo <strong>{{ $user->name }}</strong>, silakan buat password baru Anda untuk mengaktifkan akun login.</p>

    <form action="{{ route('activation.submit', $user->id) }}" method="POST">
        @csrf

        <div class="input-group-custom">
            <label class="form-label">Password Baru</label>
            <div style="position: relative;">
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('password')"></i>
            </div>
            @error('password')
                <div class="text-danger small mt-1 text-start">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group-custom">
            <label class="form-label">Konfirmasi Password Baru</label>
            <div style="position: relative;">
                <i class="fa-solid fa-circle-check input-icon"></i>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password baru" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('password_confirmation')"></i>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-2">Aktifkan Akun & Selesai</button>
    </form>
</div>

<script>
    function togglePass(id) {
        const input = document.getElementById(id);
        const icon = input.parentElement.querySelector('.toggle-password');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

@include('components.toast-notification')
</body>
</html>