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
                <p class="text-subtitle text-muted">Handle Employee Data or Profile</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Employees</li>
                        <li class="breadcrumb-item active" aria-current="page">Index</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    Employee List
                </h5>
            </div>
            <div class="card-body">

            <div class="d-flex">
            <a href="{{ route('employees.create') }}" class="btn btn-primary mb-3 ms-auto">New Employees</a>  
            </div>

                <table class="table table-striped" id="table1">
                    <thead>
                        <tr>
                            <th>Fullname</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Salary</th>
                            <th>Option</th>          
                        </tr>
                    </thead>
            <tbody>
                @foreach($employees as $employee)

                <tr>
                    <td>{{ $employee->fullname }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->department->name }}</td> 
                    <td>{{ $employee->role->title }}</td>             
                    <td>
                        @if(strtolower($employee->status) == 'active')
                            <span class="text-success">{{ ucfirst($employee->status) }}</span>    
                        @else
                            <span class="text-warning">{{ ucfirst($employee->status) }}</span> 
                        @endif
                    </td>
                    <td>{{ "Rp" . number_format ($employee->salary) }}</td>

                    <td>
                        <a href="{{ route('employees.show', $employee->id) }}" target="_blank" class="btn btn-info btn-sm" rel="noopener">View</a>
                        
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning btn-sm">Edit</a>

                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" >Delete</button>
                        </form>
                    </td>
                </tr>
    @endforeach
</tbody>
@endsection