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
use App\Models\User; // PENTING: Memanggil Model User
use Illuminate\Support\Facades\Hash; // PENTING: Untuk enkripsi password
use Illuminate\Support\Facades\DB; // PENTING: Untuk transaksi database


class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        return view('employees.index', compact('employees'));
    }

    public function create ()
    {
        $departments = Department::all();
        $roles = Role::all();
        return view('employees.create', compact('departments', 'roles'));
    }

    public function store(Request $request)
    {
        // 1. Validasi: Pastikan email belum terdaftar di tabel employees DAN users
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'hire_date' => 'required|date',
            'department_id' => 'required',
            'role_id' => 'required',
            'status' => 'required|string',
            'salary' => 'nullable|numeric',
        ]);

        // Gunakan DB Transaction agar jika satu gagal, semuanya batal (tidak ada data setengah jadi)
        DB::beginTransaction();
        
        try {
            // 2. Buat data Karyawan dan SIMPAN ke dalam variabel $employee
            $employee = Employee::create($request->all());

            // 3. Buat Akun Login (User) Otomatis dengan Password Acak dan Relasi Role yang Benar
            $user = User::create([
                'name'        => $employee->fullname,
                'email'       => $employee->email,
                'password'    => Hash::make(Str::random(32)), // Kata sandi diacak, HR pun tidak tahu
                'employee_id' => $employee->id, 
                'role_id'     => $employee->role_id, // KUNCI: Menyimpan ID role (angka) agar sesuai database
            ]);

            // 4. Membuat Tautan Rahasia yang akan expired dalam 24 jam
            $activationLink = URL::temporarySignedRoute(
                'activation.form', now()->addHours(24), ['user' => $user->id]
            );

            // 5. Mengirim Email ke karyawan yang baru dibuat
            Mail::to($user->email)->send(new AccountInvitation($user, $activationLink));

            DB::commit(); // Simpan permanen ke database
            
            return redirect()->route('employees.index')
                             ->with('success', 'Employee created successfully. Invitation email has been sent!');
                             
        } catch (\Exception $e) {
            DB::rollback(); // Batalkan semua jika terjadi error
            return back()->withInput()->withErrors(['error' => 'Gagal membuat data: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.show', compact('employee'));
    }

    public function edit($id)
    {
        $employee = Employee::find($id);
        $departments = Department::all();
        $roles = Role::all();
        return view('employees.edit', compact('employee', 'departments', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'birth_date' => 'required|date',
            'hire_date' => 'required|date',
            'department_id' => 'required',
            'role_id' => 'required',
            'status' => 'required|string',
            'salary' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);
            $oldEmail = $employee->email; 

            // Update data employee
            $employee->update($request->all());

            // Cari User berdasarkan email lama dan update jika ada perubahan nama/email
            $user = User::where('email', $oldEmail)->first();
            if ($user) {
                $user->update([
                    'name' => $request->fullname,
                    'email' => $request->email,
                    'role_id' => $employee->role_id, // Memastikan jika HR mengubah role, akun user juga ikut ter-update
                ]);
            }

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate data: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $employee = Employee::findOrFail($id);
            $email = $employee->email;
            
            // 1. HAPUS DATA RELASI (Sapu Bersih) AGAR TIDAK ERROR #1451
            \App\Models\Payroll::where('employee_id', $employee->id)->delete();
            \App\Models\Presence::where('employee_id', $employee->id)->delete();
            // (Kita tidak memasukkan penghapusan tabel Task di sini karena Task Anda bersifat umum/tidak memiliki employee_id)
            
            // 2. Hapus Akun User (Login)
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->delete();
            }

            // 3. Terakhir, Hapus Karyawan
            // Sekarang dijamin aman karena data relasinya di tabel lain sudah kosong
            $employee->delete();
            
            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee dan data terkait berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Gagal menghapus data: ' . $e->getMessage()]);
        }
    }
}