{{-- Halaman form edit pengajuan cuti — hanya HR yang dapat mengedit --}}
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
                <h3>Leave Requests</h3>
                <p class="text-subtitle text-muted">Manage leave data.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                        <li class="breadcrumb-item">Leave Requests</li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">

                {{-- Form dikirim ke LeaveRequestController@update via PUT --}}
                <form action="{{ route('leave-requests.update', $leaveRequest->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="" class="form-label">Employee</label>
                        {{-- Dropdown semua karyawan, tandai karyawan pemilik pengajuan ini --}}
                        <select name="employee_id" id="status" class="form-control">
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $leaveRequest->employees_id == $employee->id ? 'selected' : '' }}>{{ $employee->fullname }}</option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="" class="form-label">Leave Type</label>
                        {{-- Tampilkan tipe cuti saat ini sebagai nilai default --}}
                        <select name="leave_type" id="status" class="form-control">
                            <option value="Sick Leave">{{ $leaveRequest->leave_type == 'Sick Leave' ? 'selected' : '' }}Sick Leave</option>
                            <option value="Vacation">{{ $leaveRequest->leave_type == 'Vacation' ? 'selected' : '' }}Vacation</option>
                            <option value="Birth Leave">{{ $leaveRequest->leave_type == 'Birth Leave' ? 'selected' : '' }}Birth Leave</option>
                        </select>
                        @error('leave_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="" class="form-label">Start Date</label>
                        {{-- old() mengembalikan nilai lama jika validasi gagal, atau nilai dari database --}}
                        <input type="text" class="form-control date" name="start_date" value="{{ old('start_date', $leaveRequest->start_date) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="text" class="form-control date" name="end_date" value="{{ old('end_date', $leaveRequest->end_date) }}" required>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update Leave Request</button>
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">Back to Leave Requests</a>
                </form>

            </div>
        </div>
    </section>
</div>

@endsection
