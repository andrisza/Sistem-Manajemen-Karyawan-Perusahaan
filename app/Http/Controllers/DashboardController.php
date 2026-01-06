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
        $employee = Employee::count();
        $department = Department::count();
        $payroll = Payroll::count();
        $presence = Presence::count();
        $tasks = Task::all();

        return view('dashboard.index', compact('employee', 'department', 'payroll', 'presence', 'tasks'));
    }
    public function presence() {
        // 1. Ambil data, group berdasarkan bulan, jadikan key-value pair [bulan => total]
        // Pastikan filter berdasarkan tahun ini agar data tahun lalu tidak ikut
        $presences = Presence::where('status', 'present')
                    ->whereYear('date', date('Y')) 
                    ->selectRaw('MONTH(date) as month, count(*) as total_present')
                    ->groupBy('month')
                    ->pluck('total_present', 'month') // Hasil: [3 => 10, 12 => 5]
                    ->toArray();

        $data = [];

        // 2. Looping paksa dari bulan 1 sampai 12
        for ($i = 1; $i <= 12; $i++) {
            // Jika di database ada datanya untuk bulan $i, ambil nilainya.
            // Jika tidak ada, isi dengan 0.
            $data[] = isset($presences[$i]) ? $presences[$i] : 0;
        }

        // Hasil array sekarang: [0, 0, 10, 0, ..., 5] (Maret ada isinya, Des ada isinya)
        return response()->json($data);
    }
}
