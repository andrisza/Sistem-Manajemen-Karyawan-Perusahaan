<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Employee;
use Carbon\Carbon;

class PresenceController extends Controller
{
    public function index()
    {   
        if (session('role') == 'HR') {
            $presences = Presence::latest()->get();
        } else {
            $presences = Presence::where('employee_id', session('employee_id'))->latest()->get();
        }
        return view('presences.index', compact('presences'));
    }

    public function create()
    {
        $employees = Employee::all();
        
        // Cek apakah karyawan sudah check in hari ini
        $today = Carbon::now()->format('Y-m-d');
        $todayPresence = null;

        if (session('role') != 'HR') {
            $todayPresence = Presence::where('employee_id', session('employee_id'))
                                     ->where('date', $today)
                                     ->first();
        }

        // Kirim $todayPresence ke view agar tombol bisa menyesuaikan
        return view('presences.create', compact('employees', 'todayPresence'));
    }

    public function store(Request $request)
    {
        if (session('role') == 'HR') {
            $request->validate([
                'employee_id' => 'required',
                'check_in'    => 'required',
                'check_out'   => 'required',
                'date'        => 'required|date',
                'status'      => 'required|string',
            ]);

            // Gabungkan field date + time menjadi datetime lengkap
            $date     = $request->date;
            $checkIn  = $date . ' ' . $request->check_in;
            $checkOut = $date . ' ' . $request->check_out;

            Presence::create([
                'employee_id' => $request->employee_id,
                'check_in'    => $checkIn,
                'check_out'   => $checkOut,
                'date'        => $date,
                'status'      => $request->status,
            ]);
        } else {
            $now = Carbon::now()->format('Y-m-d H:i:s');
            $today = Carbon::now()->format('Y-m-d');

            // Validasi mencegah karyawan check-in 2x di hari yang sama
            $exists = Presence::where('employee_id', session('employee_id'))
                              ->where('date', $today)
                              ->exists();
            if($exists) {
                return redirect()->route('presences.index')->with('error', 'Anda sudah Check In hari ini.');
            }

            Presence::create ([
                'employee_id' => session('employee_id'),
                'check_in' => $now,
                'check_out' => $now, // Disamakan sementara dengan check_in untuk menghindari error database NOT NULL
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'date' => $today,
                'status' => 'present'
            ]);
        }
        return redirect()->route('presences.index')->with('success', 'Check In berhasil dicatat.');
    }

    // METHOD BARU UNTUK PROSES CHECKOUT
    public function checkout(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');
        $presence = Presence::where('employee_id', session('employee_id'))
                            ->where('date', $today)
                            ->first();

        if ($presence) {
            $presence->update([
                'check_out' => Carbon::now()->format('Y-m-d H:i:s') // Timpa nilai sementara dengan jam pulang asli
            ]);
            return redirect()->route('presences.index')->with('success', 'Check Out berhasil dicatat.');
        }

        return redirect()->route('presences.index')->with('error', 'Data Check In hari ini tidak ditemukan.');
    }

    public function edit(Presence $presence)
    {
        $employees = Employee::all();
        return view('presences.edit', compact('presence', 'employees'));
    }

    public function update(Request $request, Presence $presence)
    {
        $request->validate([
            'employee_id' => 'required',
            'check_in'    => 'required',
            'check_out'   => 'required',
            'date'        => 'required|date',
            'status'      => 'required|string',
        ]);

        $date = $request->date;

        $presence->update([
            'employee_id' => $request->employee_id,
            'check_in'    => $date . ' ' . $request->check_in,
            'check_out'   => $date . ' ' . $request->check_out,
            'date'        => $date,
            'status'      => $request->status,
        ]);

        return redirect()->route('presences.index')->with('success', 'Presence updated successfully.');
    }

    public function destroy(Presence $presence)
    {
        $presence->delete();
        return redirect()->route('presences.index')->with('success', 'Presence deleted successfully.');
    }
}