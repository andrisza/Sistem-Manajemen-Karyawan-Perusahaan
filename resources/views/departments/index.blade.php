{{-- Halaman daftar semua departemen — hanya dapat diakses oleh HR --}}
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
                    <h3>Department</h3>
                    <p class="text-subtitle text-muted">Handle department data</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Department</li>
                            <li class="breadcrumb-item active" aria-current="page">Index</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Department List</h5>
                </div>
                <div class="card-body">

                    {{-- Tombol untuk membuka halaman tambah departemen baru --}}
                    <div class="d-flex">
                        <a href="{{ route('departments.create') }}" class="btn btn-primary mb-3 ms-auto">New Department</a>
                    </div>

                    {{-- Tabel daftar departemen dengan Simple DataTables (search & sort) --}}
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Option</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Loop setiap departemen dari variabel $departments --}}
                            @foreach($departments as $department)
                            <tr>
                                <td>{{ $department->name }}</td>
                                <td>{{ $department->description }}</td>
                                <td>
                                    {{-- Warna status: hijau untuk aktif, kuning untuk tidak aktif --}}
                                    @if($department->status == 'active')
                                        <span class="text-success">Active</span>
                                    @else
                                        <span class="text-warning">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-warning btn-sm">Edit</a>

                                    {{-- Form Delete dengan method spoofing DELETE --}}
                                    <form action="{{ route('departments.destroy', $department->id) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
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
