<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Employee;
use Carbon\Carbon;

// Controller untuk mengelola data Presensi/Absensi (check-in & check-out) karyawan
class PresenceController extends Controller
{
    // Menampilkan daftar presensi sesuai role pengguna
    public function index()
    {
        if (session('role') == 'HR') {
            // HR melihat seluruh data presensi semua karyawan, diurutkan terbaru
            $presences = Presence::latest()->get();
        } else {
            // Karyawan biasa hanya melihat riwayat presensi miliknya sendiri
            $presences = Presence::where('employee_id', session('employee_id'))->latest()->get();
        }
        return view('presences.index', compact('presences'));
    }

    // Menampilkan halaman check-in / form presensi baru
    public function create()
    {
        $employees = Employee::all(); // Daftar karyawan (digunakan saat HR input manual)

        // Cek apakah karyawan yang login sudah melakukan check-in hari ini
        $today = Carbon::now()->format('Y-m-d');
        $todayPresence = null;

        if (session('role') != 'HR') {
            // Cari record presensi hari ini milik karyawan yang sedang login
            $todayPresence = Presence::where('employee_id', session('employee_id'))
                                     ->where('date', $today)
                                     ->first();
        }

        // Kirim $todayPresence ke view: jika ada = tampilkan tombol Check Out, jika null = tampilkan Check In
        return view('presences.create', compact('employees', 'todayPresence'));
    }

    // Memproses check-in atau input presensi manual (HR)
    public function store(Request $request)
    {
        if (session('role') == 'HR') {
            // Mode HR: input manual dengan memilih karyawan, tanggal, jam masuk & keluar
            $request->validate([
                'employee_id' => 'required',
                'check_in'    => 'required',
                'check_out'   => 'required',
                'date'        => 'required|date',
                'status'      => 'required|string',
            ]);

            // Gabungkan tanggal dan jam menjadi format datetime penuh (Y-m-d H:i:s)
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
            // Mode Karyawan: check-in otomatis berdasarkan waktu saat ini
            $now   = Carbon::now()->format('Y-m-d H:i:s'); // Timestamp check-in real-time
            $today = Carbon::now()->format('Y-m-d');

            // Cegah karyawan check-in lebih dari satu kali per hari
            $exists = Presence::where('employee_id', session('employee_id'))
                              ->where('date', $today)
                              ->exists();
            if ($exists) {
                return redirect()->route('presences.index')->with('error', 'Anda sudah Check In hari ini.');
            }

            Presence::create([
                'employee_id' => session('employee_id'),
                'check_in'    => $now,
                'check_out'   => $now,  // Disamakan sementara agar tidak NULL — akan diperbarui saat checkout
                'latitude'    => $request->latitude,  // Koordinat GPS dari browser
                'longitude'   => $request->longitude, // Koordinat GPS dari browser
                'date'        => $today,
                'status'      => 'present'
            ]);
        }
        return redirect()->route('presences.index')->with('success', 'Check In berhasil dicatat.');
    }

    // Memproses check-out karyawan — memperbarui check_out pada record presensi hari ini
    public function checkout(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');

        // Cari data presensi karyawan ini yang dibuat hari ini
        $presence = Presence::where('employee_id', session('employee_id'))
                            ->where('date', $today)
                            ->first();

        if ($presence) {
            // Timpa nilai check_out sementara dengan waktu checkout asli
            $presence->update([
                'check_out' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
            return redirect()->route('presences.index')->with('success', 'Check Out berhasil dicatat.');
        }

        // Jika tidak ada data check-in hari ini, tampilkan error
        return redirect()->route('presences.index')->with('error', 'Data Check In hari ini tidak ditemukan.');
    }

    // Menampilkan form edit untuk data presensi tertentu (hanya HR)
    public function edit(Presence $presence)
    {
        $employees = Employee::all(); // Daftar karyawan untuk dropdown
        return view('presences.edit', compact('presence', 'employees'));
    }

    // Memproses dan menyimpan perubahan data presensi
    public function update(Request $request, Presence $presence)
    {
        // Validasi input perubahan
        $request->validate([
            'employee_id' => 'required',
            'check_in'    => 'required',
            'check_out'   => 'required',
            'date'        => 'required|date',
            'status'      => 'required|string',
        ]);

        $date = $request->date;

        // Gabungkan tanggal dan jam input menjadi datetime penuh sebelum disimpan
        $presence->update([
            'employee_id' => $request->employee_id,
            'check_in'    => $date . ' ' . $request->check_in,
            'check_out'   => $date . ' ' . $request->check_out,
            'date'        => $date,
            'status'      => $request->status,
        ]);

        return redirect()->route('presences.index')->with('success', 'Presence updated successfully.');
    }

    // Menghapus data presensi (SoftDelete)
    public function destroy(Presence $presence)
    {
        $presence->delete(); // Soft delete: mengisi deleted_at

        return redirect()->route('presences.index')->with('success', 'Presence deleted successfully.');
    }
}
