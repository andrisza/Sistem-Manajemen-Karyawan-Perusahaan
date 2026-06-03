{{-- Halaman detail profil karyawan (read-only) — dibuka di tab baru dari daftar karyawan --}}
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
                    <h3>Employee</h3>
                    <p class="text-subtitle text-muted">Handle employee data and profile.</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Employee</li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Detail</h5>
                </div>
                <div class="card-body">

                    {{-- Menampilkan data karyawan dari variabel $employee yang dikirim EmployeeController@show --}}

                    <div class="mb-3">
                        <label for=""><b>Fullname</b></label>
                        <p>{{ $employee->fullname }}</p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Email</b></label>
                        <p>{{ $employee->email }}</p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Role</b></label>
                        {{-- Mengakses relasi role dari model Employee --}}
                        <p>{{ $employee->role->title }}</p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Department</b></label>
                        {{-- Mengakses relasi department dari model Employee --}}
                        <p>{{ $employee->department->name }}</p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Birth Date</b></label>
                        <p>{{ $employee->birth_date }}</p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Hire Date</b></label>
                        <p>{{ $employee->hire_date }}</p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Status</b></label>
                        <p>
                            {{-- Warna status: hijau untuk aktif, merah untuk tidak aktif --}}
                            @if(strtolower($employee->status) == 'active')
                                <span class="text-success">{{ ucfirst($employee->status) }}</span>
                            @else
                                <span class="text-danger">{{ ucfirst($employee->status) }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Salary</b></label>
                        {{-- Format angka gaji dengan pemisah ribuan --}}
                        <p>{{ "Rp" . number_format($employee->salary) }}</p>
                    </div>

                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Back to List</a>

                </div>
            </div>
        </section>
    </div>

@endsection
