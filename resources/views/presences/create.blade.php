@extends('layouts.dashboard')

@section('content')
<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Presences</h3>
                <p class="text-subtitle text-muted">Handle employee presence.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Presence</li>
                        <li class="breadcrumb-item active" aria-current="page">New</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Create / Action</h5>
            </div>
            <div class="card-body">

                {{-- ── FORM HR: input manual, tidak butuh geolocation ── --}}
                @if(session('role') == 'HR')
                <form action="{{ route('presences.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-control">
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->fullname }}</option>
                            @endforeach
                        </select>
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Check In</label>
                        <input type="text" class="form-control time-only" name="check_in" placeholder="HH:MM:SS" required>
                        @error('check_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Check Out</label>
                        <input type="text" class="form-control time-only" name="check_out" placeholder="HH:MM:SS" required>
                        @error('check_out')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="text" class="form-control date" name="date" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="leave">Leave</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a href="{{ route('presences.index') }}" class="btn btn-secondary">Back to List</a>
                </form>

                {{-- ── FORM KARYAWAN: geolocation check-in/out ── --}}
                @else
                    @if(isset($todayPresence) && $todayPresence->check_in != $todayPresence->check_out)
                        <div class="alert alert-success text-center">
                            <h4 class="alert-heading">Kerja Bagus!</h4>
                            <p>Anda sudah menyelesaikan absensi (Check In & Check Out) untuk hari ini.</p>
                        </div>
                        <a href="{{ route('presences.index') }}" class="btn btn-secondary d-block w-100">Kembali ke Daftar Absensi</a>
                    @else
                        <form action="{{ isset($todayPresence) ? route('presences.checkout') : route('presences.store') }}" method="POST">
                            @csrf

                            <div id="alert-location" class="alert alert-info">
                                <b>Mendeteksi lokasi...</b> Mohon izinkan akses lokasi di browser Anda.
                            </div>

                            {{-- Field lokasi tersembunyi --}}
                            <div style="display:none;">
                                <input type="text" name="latitude"  id="latitude">
                                <input type="text" name="longitude" id="longitude">
                            </div>

                            <div class="mb-3 w-100">
                                <iframe id="map-frame" class="w-100" height="300"
                                    frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
                            </div>

                            <button type="submit"
                                class="btn btn-lg w-100 {{ isset($todayPresence) ? 'btn-danger' : 'btn-primary' }}"
                                id="btn-present" disabled>
                                {{ isset($todayPresence) ? 'Check Out Sekarang' : 'Check In Sekarang' }}
                            </button>
                        </form>

                        <script>
                        (function () {
                            const officeLat  = -7.299740;
                            const officeLon  = 112.718535;
                            const threshold  = 0.01;
                            const alertBox   = document.getElementById('alert-location');
                            const btnPresent = document.getElementById('btn-present');
                            const mapFrame   = document.getElementById('map-frame');
                            const latInput   = document.getElementById('latitude');
                            const lonInput   = document.getElementById('longitude');

                            function showError(msg) {
                                alertBox.className = 'alert alert-danger';
                                alertBox.innerHTML  = msg;
                                // Tombol tetap disabled — presensi tidak bisa tanpa lokasi
                            }

                            // 1. Cek apakah koneksi aman (HTTPS / localhost)
                            if (!window.isSecureContext) {
                                showError(
                                    '<b>🔒 Akses lokasi memerlukan koneksi HTTPS.</b><br>' +
                                    'Gunakan salah satu cara berikut agar presensi bisa dilakukan:<br>' +
                                    '<ul class="mb-0 mt-2">' +
                                    '<li>Akses via <code>http://localhost/humanresourcesapp/public</code></li>' +
                                    '<li>Aktifkan SSL di Laragon (klik kanan tray → SSL → Enable SSL)</li>' +
                                    '</ul>'
                                );
                                return;
                            }

                            // 2. Cek apakah browser mendukung geolocation
                            if (!navigator.geolocation) {
                                showError('<b>Browser Anda tidak mendukung deteksi lokasi.</b> Gunakan Chrome atau Firefox versi terbaru.');
                                return;
                            }

                            // 3. Minta izin lokasi
                            navigator.geolocation.getCurrentPosition(
                                function (position) {
                                    const lat      = position.coords.latitude;
                                    const lon      = position.coords.longitude;
                                    const distance = Math.sqrt(
                                        Math.pow(lat - officeLat, 2) + Math.pow(lon - officeLon, 2)
                                    );

                                    latInput.value = lat;
                                    lonInput.value = lon;
                                    mapFrame.src   = `https://maps.google.com/maps?q=${lat},${lon}&z=15&output=embed`;

                                    if (distance <= threshold) {
                                        // Dalam jangkauan — aktifkan tombol
                                        alertBox.className = 'alert alert-success';
                                        alertBox.innerHTML = '<b>✅ Lokasi terverifikasi.</b> Anda berada di jangkauan kantor.';
                                        btnPresent.removeAttribute('disabled');
                                    } else {
                                        // Di luar jangkauan — tombol tetap disabled
                                        showError(
                                            '<b>📍 Anda berada di luar jangkauan kantor.</b><br>' +
                                            'Presensi hanya bisa dilakukan dari area kantor. Hubungi HR jika ada masalah.'
                                        );
                                    }
                                },
                                function (error) {
                                    // Izin ditolak atau error lainnya
                                    const pesan = {
                                        1: 'Izin lokasi <b>ditolak</b>. Izinkan akses lokasi di browser lalu muat ulang halaman.',
                                        2: 'Posisi lokasi <b>tidak tersedia</b>. Pastikan GPS aktif lalu coba lagi.',
                                        3: 'Permintaan lokasi <b>timeout</b>. Pastikan sinyal GPS/internet stabil lalu muat ulang.'
                                    };
                                    showError('📵 ' + (pesan[error.code] ?? 'Gagal mendeteksi lokasi: ' + error.message));
                                },
                                { timeout: 10000, enableHighAccuracy: true }
                            );
                        })();
                        </script>
                    @endif
                @endif

            </div>
        </div>
    </section>
</div>
@endsection
