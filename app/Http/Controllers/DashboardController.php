<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Payroll;
use App\Models\Presence;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
    {
        // Pengecekan Role Pengguna
        if (session('role') == 'HR') {
            // KODE LAMA ANDA: Tetap dipertahankan untuk HRD (Melihat Seluruh Data)
            $employee = Employee::count();
            $department = Department::count();
            $payroll = Payroll::count();
            $presence = Presence::count();
            $tasks = Task::all();
        } else {
            // TAMBAHAN BARU: Untuk Karyawan (Hanya Melihat Data Pribadi)
            /** @var \App\Models\User $user */
            $user = auth()->user();
            $employeeId = $user->employee_id;

            // Ambil data spesifik dari karyawan yang sedang login
            $employeeData = Employee::with('department')->find($employeeId);

            // Ganti Angka menjadi Data Pribadi (Akan otomatis menyesuaikan kotak di Blade Anda)
            $employee   = $employeeData ? $employeeData->fullname : '-';
            
            // Catatan: Jika error, cek apakah nama kolom tabel departemen Anda 'title' atau 'name', sesuaikan di bawah:
            $department = $employeeData && $employeeData->department ? $employeeData->department->title ?? $employeeData->department->name : '-'; 
            
            $payroll    = Payroll::where('employee_id', $employeeId)->count();
            $presence   = Presence::where('employee_id', $employeeId)->where('status', 'present')->count();
            
            // Tampilkan Tasks yang hanya ditugaskan untuk dia (maksimal 5 agar rapi)
            $tasks = Task::latest()->take(5)->get();
        }

        return view('dashboard.index', compact('employee', 'department', 'payroll', 'presence', 'tasks'));
    }
    
    public function presence() {
        $query = Presence::where('status', 'present')
                         ->whereYear('date', date('Y'));

        if (session('role') != 'HR') {
            $query->where('employee_id', session('employee_id'));
        }

        $presences = $query->selectRaw('MONTH(date) as month, count(*) as total_present')
                    ->groupBy('month')
                    ->pluck('total_present', 'month')
                    ->toArray();

        $data = [];

        for ($i = 1; $i <= 12; $i++) {
            $data[] = isset($presences[$i]) ? $presences[$i] : 0;
        }

        return response()->json($data);
    }
}