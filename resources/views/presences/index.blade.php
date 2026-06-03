{{-- Halaman daftar presensi — HR melihat semua karyawan, karyawan hanya melihat riwayat pribadinya --}}
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
                <p class="text-subtitle text-muted">Handle presence data</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Presence</li>
                        <li class="breadcrumb-item active" aria-current="page">Index</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Presences List</h5>
            </div>
            <div class="card-body">

                {{-- Label tombol berbeda berdasarkan role: HR = "New Presence", karyawan = "Lakukan Absensi" --}}
                <div class="d-flex">
                    <a href="{{ route('presences.create') }}" class="btn btn-primary mb-3 ms-auto">
                        {{ session('role') == 'HR' ? 'New Presence' : 'Lakukan Absensi' }}
                    </a>
                </div>

                <table class="table table-striped" id="table1">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Date</th>
                            <th>Status</th>
                            {{-- Kolom Option hanya tampil untuk HR --}}
                            @if(session('role') == 'HR')
                                <th>Option</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($presences as $presence)
                        <tr>
                            <td>{{ $presence->employee->fullname }}</td>

                            {{-- Format waktu check-in menjadi HH:MM:SS saja --}}
                            <td>{{ \Carbon\Carbon::parse($presence->check_in)->format('H:i:s') }}</td>

                            <td>
                                {{-- Jika check_in == check_out berarti karyawan belum checkout (nilai sementara) --}}
                                @if($presence->check_in == $presence->check_out)
                                    <span class="badge bg-warning text-dark">Belum Checkout</span>
                                @else
                                    {{ \Carbon\Carbon::parse($presence->check_out)->format('H:i:s') }}
                                @endif
                            </td>

                            <td>{{ $presence->date }}</td>

                            <td>
                                {{-- Warna status: hijau untuk hadir, merah untuk tidak hadir --}}
                                @if($presence->status == 'present')
                                    <span class="text-success fw-bold">Present</span>
                                @else
                                    <span class="text-danger fw-bold">{{ ucfirst($presence->status) }}</span>
                                @endif
                            </td>

                            {{-- Tombol Edit dan Delete hanya untuk HR --}}
                            <td>
                                @if(session('role') == 'HR')
                                    <a href="{{ route('presences.edit', $presence->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('presences.destroy', $presence->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
