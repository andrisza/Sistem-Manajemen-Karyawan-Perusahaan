{{-- Halaman detail tugas (read-only) --}}
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
                    <h3>Tasks</h3>
                    <p class="text-subtitle text-muted">Handle employee tasks.</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Task</li>
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

                    {{-- Data tugas dari variabel $task yang dikirim TaskController@show --}}

                    <div class="mb-3">
                        <label for=""><b>Title</b></label>
                        <p>{{ $task->title }}</p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Employee</b></label>
                        {{-- Mengakses nama karyawan via relasi employee di model Task --}}
                        <p>{{ $task->employee->fullname }}</p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Due Date</b></label>
                        {{-- Carbon::parse() mengubah string tanggal menjadi objek Carbon untuk format yang lebih rapi --}}
                        <p>{{ \Carbon\Carbon::parse($task->due_date)->format('d F Y') }}</p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Status</b></label>
                        <p>
                            {{-- Warna status: hijau untuk pending (belum mulai), kuning untuk status lain --}}
                            @if($task->status == 'pending')
                                <span class="text-success">{{ ucfirst($task->status) }}</span>
                            @else
                                <span class="text-warning">{{ ucfirst($task->status) }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for=""><b>Description</b></label>
                        <p>{{ $task->description }}</p>
                    </div>

                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back to List</a>

                </div>
            </div>
        </section>
    </div>

@endsection
