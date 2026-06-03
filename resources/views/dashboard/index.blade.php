{{-- Halaman Dashboard utama — konten ini disisipkan ke layouts/dashboard.blade.php via @yield('content') --}}
@extends('layouts.dashboard')

@section('content')
    {{-- Tombol burger untuk membuka sidebar pada layar kecil/mobile --}}
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <h3>Dashboard</h3>
    </div>

    <div class="page-content">
        {{-- ── KARTU STATISTIK RINGKASAN ─────────────────────────────────── --}}
        {{-- Variabel $department, $employee, $presence, $payroll dikirim dari DashboardController --}}
        {{-- HR: menampilkan angka total; Karyawan biasa: menampilkan data pribadi --}}
        <div class="row">

            {{-- Kartu Departemen --}}
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                <div class="stats-icon purple mb-2">
                                    <i class="icon dripicons dripicons-tag"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Department</h6>
                                {{-- Menampilkan nama departemen (karyawan) atau jumlah departemen (HR) --}}
                                <h6 class="font-extrabold mb-0">{{ $department }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu Karyawan / Nama Karyawan --}}
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                <div class="stats-icon blue mb-2">
                                    <i class="icon dripicons dripicons-user-group"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Employees</h6>
                                {{-- Menampilkan nama lengkap (karyawan) atau jumlah karyawan (HR) --}}
                                <h6 class="font-extrabold mb-0">{{ $employee }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu Presensi --}}
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                <div class="stats-icon green mb-2">
                                    <i class="icon dripicons dripicons-alarm"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Presence</h6>
                                {{-- Menampilkan jumlah kehadiran (karyawan: milik pribadi; HR: semua) --}}
                                <h6 class="font-extrabold mb-0">{{ $presence }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu Payroll --}}
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                <div class="stats-icon red mb-2">
                                    <i class="icon dripicons dripicons-to-do"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Payroll</h6>
                                {{-- Menampilkan jumlah data gaji --}}
                                <h6 class="font-extrabold mb-0">{{ $payroll }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── GRAFIK KEHADIRAN BULANAN ──────────────────────────────────── --}}
        {{-- Canvas ini digunakan oleh Chart.js (diinisialisasi di layouts/dashboard.blade.php) --}}
        {{-- Data grafik diambil via fetch('/dashboard/presences') yang mengembalikan JSON --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Latest Presence</h4>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 400px; width: 100%;">
                            <canvas id="presence"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TABEL TUGAS TERBARU ───────────────────────────────────────── --}}
        {{-- Variabel $tasks: HR mendapat semua tugas, karyawan hanya mendapat 5 tugas terbaru miliknya --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Latest Tasks</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-lg">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Detail</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Loop semua tugas dan tampilkan nama karyawan, judul, dan status --}}
                                    @foreach ($tasks as $task)
                                    <tr>
                                        <td class="col-3">
                                            <div class="d-flex align-items-center">
                                                {{-- Avatar dinamis dari ui-avatars.com berdasarkan nama karyawan --}}
                                                <div class="avatar avatar-md">
                                                    <img src="https://ui-avatars.com/api/?name={{ $task->employee->fullname }}&background=random">
                                                </div>
                                                <p class="font-bold ms-3 mb-0">{{ $task->employee->fullname }}</p>
                                            </div>
                                        </td>
                                        <td class="col-auto">
                                            <p class=" mb-0">{{ $task->title }}</p>
                                        </td>
                                        <td class="col-auto">
                                            {{-- ucfirst() mengubah huruf pertama status menjadi kapital --}}
                                            <p class=" mb-0">{{ ucfirst($task->status) }}</p>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
