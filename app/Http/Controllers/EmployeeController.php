<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountInvitation;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Controller untuk mengelola data Karyawan (Employee) — hanya dapat diakses oleh HR
class EmployeeController extends Controller
{
    // Menampilkan daftar semua karyawan yang terdaftar
    public function index()
    {
        $employees = Employee::all(); // Ambil semua karyawan dari database
        return view('employees.index', compact('employees'));
    }

    // Menampilkan form untuk mendaftarkan karyawan baru
    public function create()
    {
        $departments = Department::all(); // Daftar departemen untuk dropdown
        $roles       = Role::all();       // Daftar jabatan untuk dropdown
        return view('employees.create', compact('departments', 'roles'));
    }

    // Memproses pendaftaran karyawan baru: simpan data, buat akun login, kirim email undangan
    public function store(Request $request)
    {
        // Validasi input: email harus unik di tabel employees DAN users sekaligus
        $validated = $request->validate([
            'fullname'      => 'required|string|max:255',
            'email'         => 'required|email|unique:employees,email|unique:users,email',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'required|string|max:255',
            'birth_date'    => 'required|date',
            'hire_date'     => 'required|date',
            'department_id' => 'required',
            'role_id'       => 'required',
            'status'        => 'required|string',
            'salary'        => 'nullable|numeric',
        ]);

        // Gunakan DB Transaction: jika salah satu langkah gagal, semua operasi dibatalkan (rollback)
        // Ini mencegah kondisi data setengah jadi (misal: karyawan tersimpan tapi akun tidak terbuat)
        DB::beginTransaction();
        try {
            // Langkah 1: Simpan data karyawan ke tabel employees
            $employee = Employee::create($request->all());

            // Langkah 2: Buat akun login (User) otomatis untuk karyawan tersebut
            $user = User::create([
                'name'        => $employee->fullname,
                'email'       => $employee->email,
                'password'    => Hash::make(Str::random(32)), // Password acak — karyawan akan set sendiri via link aktivasi
                'employee_id' => $employee->id,              // Hubungkan akun ke data karyawan
                'role_id'     => $employee->role_id,         // Salin role_id agar sinkron
            ]);

            // Langkah 3: Buat URL aktivasi bertanda tangan (signed URL) yang valid selama 24 jam
            $activationLink = URL::temporarySignedRoute(
                'activation.form', now()->addHours(24), ['user' => $user->id]
            );

            // Langkah 4: Kirim email undangan berisi link aktivasi ke karyawan baru
            Mail::to($user->email)->send(new AccountInvitation($user, $activationLink));

            DB::commit(); // Semua berhasil — simpan permanen ke database

            return redirect()->route('employees.index')
                            ->with('success', 'Employee created successfully. Invitation email has been sent!');
        } catch (\Exception $e) {
            DB::rollback(); // Ada error — batalkan semua perubahan
            return back()->withInput()->withErrors(['error' => 'Gagal membuat data: ' . $e->getMessage()]);
        }
    }

    // Menampilkan halaman detail profil karyawan tertentu
    public function show($id)
    {
        $employee = Employee::findOrFail($id); // Error 404 jika karyawan tidak ditemukan
        return view('employees.show', compact('employee'));
    }

    // Menampilkan form edit untuk karyawan tertentu
    public function edit($id)
    {
        $employee    = Employee::find($id);
        $departments = Department::all(); // Daftar departemen untuk dropdown
        $roles       = Role::all();       // Daftar jabatan untuk dropdown
        return view('employees.edit', compact('employee', 'departments', 'roles'));
    }

    // Memproses dan menyimpan perubahan data karyawan, sinkronkan juga akun login-nya
    public function update(Request $request, $id)
    {
        // Validasi input perubahan (abaikan email milik karyawan ini sendiri di cek unique)
        $request->validate([
            'fullname'      => 'required|string|max:255',
            'email'         => 'required|email|unique:employees,email,' . $id,
            'phone_number'  => 'required|string|max:20',
            'address'       => 'nullable|string|max:255',
            'birth_date'    => 'required|date',
            'hire_date'     => 'required|date',
            'department_id' => 'required',
            'role_id'       => 'required',
            'status'        => 'required|string',
            'salary'        => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);
            $oldEmail = $employee->email; // Simpan email lama untuk mencari akun User-nya

            // Perbarui data di tabel employees
            $employee->update($request->all());

            // Cari akun User berdasarkan email lama, lalu sinkronkan nama, email, dan role-nya
            $user = User::where('email', $oldEmail)->first();
            if ($user) {
                $user->update([
                    'name'    => $request->fullname,
                    'email'   => $request->email,
                    'role_id' => $employee->role_id, // Pastikan role di akun login ikut berubah
                ]);
            }

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate data: ' . $e->getMessage()]);
        }
    }

    // Menghapus karyawan beserta seluruh data relasinya secara aman
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);
            $email    = $employee->email;

            // Langkah 1: Hapus data relasi terlebih dahulu untuk menghindari error FK constraint (#1451)
            \App\Models\Payroll::where('employee_id', $employee->id)->delete();   // Hapus semua data payroll karyawan ini
            \App\Models\Presence::where('employee_id', $employee->id)->delete();  // Hapus semua data presensi karyawan ini

            // Langkah 2: Hapus akun login (User) yang terhubung ke karyawan ini
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->delete();
            }

            // Langkah 3: Terakhir baru hapus data karyawan (semua relasi sudah dibersihkan)
            $employee->delete();

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee dan data terkait berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Gagal menghapus data: ' . $e->getMessage()]);
        }
    }
}
