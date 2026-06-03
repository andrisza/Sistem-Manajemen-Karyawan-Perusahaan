<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Payroll;
use App\Models\Presence;
use App\Models\Task;

// Controller untuk mengelola halaman Dashboard utama aplikasi
class DashboardController extends Controller
{
    // Menampilkan halaman dashboard dengan data ringkasan sesuai role pengguna yang login
    public function index()
    {
        // Cek role pengguna dari session untuk menentukan data apa yang ditampilkan
        if (session('role') == 'HR') {
            // HR melihat statistik seluruh perusahaan
            $employee = Employee::count();    // Total semua karyawan
            $department = Department::count(); // Total semua departemen
            $payroll = Payroll::count();       // Total semua data payroll
            $presence = Presence::count();     // Total semua catatan presensi
            $tasks = Task::all();              // Semua tugas yang ada
        } else {
            // Karyawan biasa hanya melihat data pribadinya
            /** @var \App\Models\User $user */
            $user = auth()->user();           // Ambil data user yang sedang login
            $employeeId = $user->employee_id; // Ambil ID karyawan yang terhubung ke akun ini

            // Ambil data karyawan beserta relasi departemennya
            $employeeData = Employee::with('department')->find($employeeId);

            // Tampilkan nama karyawan sendiri di card "Employees"
            $employee   = $employeeData ? $employeeData->fullname : '-';

            // Tampilkan nama departemen karyawan (cek kolom 'title' atau 'name')
            $department = $employeeData && $employeeData->department ? $employeeData->department->title ?? $employeeData->department->name : '-';

            // Hitung jumlah slip gaji milik karyawan ini
            $payroll    = Payroll::where('employee_id', $employeeId)->count();
            // Hitung total hari hadir karyawan ini di tahun berjalan
            $presence   = Presence::where('employee_id', $employeeId)->where('status', 'present')->count();

            // Ambil 5 tugas terbaru (dibatasi agar tampilan dashboard tidak terlalu panjang)
            $tasks = Task::latest()->take(5)->get();
        }

        // Kirim semua variabel ke view dashboard untuk ditampilkan
        return view('dashboard.index', compact('employee', 'department', 'payroll', 'presence', 'tasks'));
    }

    // Mengembalikan data jumlah kehadiran per bulan sebagai JSON untuk grafik Chart.js di dashboard
    public function presence() {
        // Mulai query presensi berstatus 'present' pada tahun berjalan
        $query = Presence::where('status', 'present')
                         ->whereYear('date', date('Y'));

        // Jika bukan HR, batasi query hanya untuk karyawan yang sedang login
        if (session('role') != 'HR') {
            $query->where('employee_id', session('employee_id'));
        }

        // Kelompokkan per bulan dan hitung total kehadiran setiap bulannya
        $presences = $query->selectRaw('MONTH(date) as month, count(*) as total_present')
                    ->groupBy('month')
                    ->pluck('total_present', 'month') // Hasil: [bulan => total]
                    ->toArray();

        // Siapkan array 12 elemen (Januari-Desember), isi 0 jika tidak ada data pada bulan tersebut
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = isset($presences[$i]) ? $presences[$i] : 0;
        }

        // Kembalikan sebagai JSON — dikonsumsi oleh Chart.js di layouts/dashboard.blade.php
        return response()->json($data);
    }
}
