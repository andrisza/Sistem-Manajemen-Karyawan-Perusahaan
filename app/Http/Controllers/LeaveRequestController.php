<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Http\Request;

// Controller untuk mengelola Pengajuan Cuti (Leave Request) — semua role dapat mengakses
class LeaveRequestController extends Controller
{
    // Menampilkan daftar pengajuan cuti sesuai role pengguna
    public function index()
    {
        if (session('role') == 'HR') {
            // HR melihat semua pengajuan cuti dari seluruh karyawan
            $leaveRequests = LeaveRequest::all();
        } else {
            // Karyawan biasa hanya melihat pengajuan cuti miliknya sendiri
            $leaveRequests = LeaveRequest::where('employee_id', session('employee_id'))->get();
        }
        return view('leave-requests.index', compact('leaveRequests'));
    }

    // Menampilkan form untuk membuat pengajuan cuti baru
    public function create()
    {
        $employees = Employee::all(); // Daftar karyawan untuk dropdown (digunakan HR)
        return view('leave-requests.create', compact('employees'));
    }

    // Memproses dan menyimpan pengajuan cuti baru
    public function store(Request $request)
    {
        if (session('role') == 'HR') {
            // HR bisa mengajukan cuti untuk karyawan manapun, employee_id wajib dipilih
            $request->validate([
                'employee_id' => 'required',
                'leave_type'  => 'required|string',
                'start_date'  => 'required|date',
                'end_date'    => 'required|date',
            ]);
            // Status diset 'pending' secara otomatis saat dibuat
            $request->merge(['status' => 'pending']);
            LeaveRequest::create($request->all());
        } else {
            // Karyawan biasa tidak bisa memilih employee_id — diambil dari session login
            LeaveRequest::create([
                'employee_id' => session('employee_id'), // ID karyawan dari session
                'leave_type'  => $request->leave_type,
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date,
                'status'      => 'pending' // Semua pengajuan baru selalu berstatus pending
            ]);
        }
        return redirect()->route('leave-requests.index')->with('success', 'Leave Request Created Successfully');
    }

    // Menampilkan form edit untuk pengajuan cuti tertentu (Route Model Binding)
    public function edit(LeaveRequest $leaveRequest)
    {
        $employees = Employee::all(); // Daftar karyawan untuk dropdown
        return view('leave-requests.edit', compact('leaveRequest', 'employees'));
    }

    // Memproses dan menyimpan perubahan data pengajuan cuti
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        // Validasi input perubahan
        $request->validate([
            'employee_id' => 'required',
            'leave_type'  => 'required|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date',
        ]);
        $leaveRequest->update($request->all()); // Terapkan perubahan

        return redirect()->route('leave-requests.index')->with('success', 'Leave Request updated Successfully');
    }

    // Mengubah status pengajuan cuti menjadi 'confirm' (disetujui) — hanya HR
    public function confirm(int $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update(['status' => 'confirm']); // Set status menjadi disetujui

        return redirect()->route('leave-requests.index')->with('success', 'Leave Request confirmed Successfully');
    }

    // Mengubah status pengajuan cuti menjadi 'reject' (ditolak) — hanya HR
    public function reject(int $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update(['status' => 'reject']); // Set status menjadi ditolak

        return redirect()->route('leave-requests.index')->with('success', 'Leave Request rejected Successfully');
    }

    // Menghapus pengajuan cuti dari database (SoftDelete)
    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete(); // Soft delete: mengisi deleted_at

        return redirect()->route('leave-requests.index')->with('success', 'Leave Request deleted Successfully');
    }
}
