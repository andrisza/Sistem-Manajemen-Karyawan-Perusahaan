{{-- Halaman form edit data payroll — hanya HR yang dapat mengakses --}}
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
                    <h3>Payrolls</h3>
                    <p class="text-subtitle text-muted">Handle payroll data.</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll</li>
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

                    {{-- Form dikirim ke PayrollController@update via PUT --}}
                    <form action="{{ route('payrolls.update', $payroll->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="" class="form-label">Employee</label>
                            {{-- Tandai karyawan yang saat ini terdaftar pada payroll ini --}}
                            <select name="employee_id" id="status" class="form-control">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ $payroll->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->fullname }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="" class="form-label">Salary</label>
                            <input type="number" class="form-control" name="salary" value="{{ old('salary', $payroll->salary) }}" required>
                            @error('salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="" class="form-label">Deductions</label>
                            <input type="number" class="form-control" name="deductions" value="{{ old('deductions', $payroll->deduction) }}" required>
                            @error('deductions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="" class="form-label">Bonuses</label>
                            <input type="number" class="form-control" name="bonuses" value="{{ old('bonuses', $payroll->bonuses) }}" required>
                            @error('bonuses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="" class="form-label">Pay Date</label>
                            {{-- Class 'date' memicu Flatpickr date picker --}}
                            <input type="text" class="form-control date" name="pay_date" value="{{ old('pay_date', $payroll->pay_date) }}" required>
                            @error('pay_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('payrolls.index') }}" class="btn btn-secondary">Back to List</a>
                    </form>

                </div>
            </div>
        </section>
    </div>

@endsection
