{{-- Halaman form edit data karyawan — hanya dapat diakses oleh HR --}}
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
                <h3>Employees</h3>
                <p class="text-subtitle text-muted">Manage employees data.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                        <li class="breadcrumb-item">Employees</li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">

                {{-- Tampilkan semua pesan error validasi jika ada --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form dikirim ke EmployeeController@update via POST dengan method spoofing PUT --}}
                <form action="{{ route('employees.update', $employee->id) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Memberi tahu Laravel bahwa ini adalah request PUT (update) --}}

                    <div class="mb-3">
                        <label for="fullname" class="form-label">Fullname</label>
                        {{-- old('fullname', $employee->fullname): tampilkan nilai lama jika validasi gagal, atau nilai dari database --}}
                        <input type="text" name="fullname" class="form-control" id="fullname" value="{{ old('fullname', $employee->fullname) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $employee->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" id="phone_number" value="{{ old('phone_number', $employee->phone_number) }}">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" id="address" value="{{ old('address', $employee->address) }}">
                    </div>

                    <div class="mb-3">
                        <label for="birth_date" class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control" id="birth_date" value="{{ old('birth_date', $employee->birth_date) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="hire_date" class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" class="form-control" id="hire_date" value="{{ old('hire_date', $employee->hire_date) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        {{-- Dropdown departemen — tandai 'selected' pada departemen yang saat ini dimiliki karyawan --}}
                        <select name="department_id" class="form-control" id="department_id" required>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ ($department->id == $employee->department_id) ? 'selected' : ''}}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role</label>
                        {{-- Dropdown jabatan --}}
                        <select name="role_id" class="form-control" id="role_id" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        {{-- Pilih opsi yang sesuai dengan status karyawan saat ini --}}
                        <select name="status" id="status" class="form-control" required>
                            <option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="salary" class="form-label">Salary</label>
                        <input type="number" name="salary" class="form-control" id="salary" value="{{ old('salary', $employee->salary) }}" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Back to List</a>
                </form>

            </div>
        </div>
    </section>
</div>

@endsection
