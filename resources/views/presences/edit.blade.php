{{-- Halaman form edit data presensi — hanya HR yang dapat mengedit --}}
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
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Edit</h5>
            </div>
            <div class="card-body">

                {{-- Form dikirim ke PresenceController@update via PUT --}}
                <form action="{{ route('presences.update', $presence->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="" class="form-label">Employee</label>
                        {{-- Tandai karyawan pemilik data presensi ini --}}
                        <select name="employee_id" id="status" class="form-control">
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ ($employee->id == $presence->employee_id) ? 'selected' : '' }}>{{ $employee->fullname }}</option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="" class="form-label">Check In</label>
                        {{-- Carbon::parse() mengambil bagian jam saja dari datetime lengkap --}}
                        <input type="text" class="form-control time-only" name="check_in"
                            value="{{ old('check_in', \Carbon\Carbon::parse($presence->check_in)->format('H:i:s')) }}" required>
                        @error('check_in')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="" class="form-label">Check Out</label>
                        <input type="text" class="form-control time-only" name="check_out"
                            value="{{ old('check_out', \Carbon\Carbon::parse($presence->check_out)->format('H:i:s')) }}" required>
                        @error('check_out')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="" class="form-label">Date</label>
                        <input type="text" class="form-control date" name="date" value="{{ old('date', $presence->date) }}" required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="" class="form-label">Status</label>
                        {{-- Tandai status presensi saat ini --}}
                        <select name="status" id="status" class="form-control">
                            <option value="present" {{ ($presence->status == 'present') ? 'selected' : '' }}>Present</option>
                            <option value="absent"  {{ ($presence->status == 'absent')  ? 'selected' : '' }}>Absent</option>
                            <option value="leave"   {{ ($presence->status == 'leave')   ? 'selected' : '' }}>Leave</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('presences.index') }}" class="btn btn-secondary">Back to List</a>
                </form>

            </div>
        </div>
    </section>
</div>
@endsection
