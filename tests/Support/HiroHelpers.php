<?php

/*
|--------------------------------------------------------------------------
| Helper data uji untuk sistem HIRO (HIR)
|--------------------------------------------------------------------------
|
| Proyek belum memiliki factory untuk model HR (Employee, Role, dll), jadi
| helper di bawah ini membuat data uji secara eksplisit. Semua akun uji
| memakai password PASSWORD_UJI agar bisa dipakai untuk login sungguhan.
|
*/

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Presence;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

const PASSWORD_UJI = 'Rahasia#2026';

// Membuat data karyawan + akun login yang terhubung. Jabatan 'HR' = HR Admin.
function buatKaryawan(string $jabatan = 'Staff', array $atributKaryawan = []): User
{
    $role = Role::firstOrCreate(['title' => $jabatan]);
    $departemen = Department::firstOrCreate(['name' => 'Umum'], ['status' => 'active']);

    $employee = Employee::create(array_merge([
        'fullname'      => fake()->name(),
        'email'         => fake()->unique()->safeEmail(),
        'phone_number'  => '081234567890',
        'address'       => 'Jl. Pengujian No. 1, Surabaya',
        'birth_date'    => '1995-01-01',
        'hire_date'     => '2024-01-01',
        'department_id' => $departemen->id,
        'role_id'       => $role->id,
        'status'        => 'active',
        'salary'        => 5000000,
    ], $atributKaryawan));

    return User::create([
        'name'        => $employee->fullname,
        'email'       => $employee->email,
        'password'    => PASSWORD_UJI, // di-hash otomatis oleh cast 'hashed'
        'employee_id' => $employee->id,
        'role_id'     => $role->id,
    ]);
}

function buatHR(array $atributKaryawan = []): User
{
    return buatKaryawan('HR', $atributKaryawan);
}

function karyawanDari(User $user): Employee
{
    return Employee::findOrFail($user->employee_id);
}

// Payload form tambah/ubah karyawan yang valid
function dataKaryawanBaru(array $override = []): array
{
    $departemen = Department::firstOrCreate(['name' => 'Umum'], ['status' => 'active']);
    $role = Role::firstOrCreate(['title' => 'Staff']);

    return array_merge([
        'fullname'      => 'Budi Santoso',
        'email'         => fake()->unique()->safeEmail(),
        'phone_number'  => '081298765432',
        'address'       => 'Jl. Merdeka No. 10, Surabaya',
        'birth_date'    => '1996-05-17',
        'hire_date'     => '2025-01-02',
        'department_id' => $departemen->id,
        'role_id'       => $role->id,
        'status'        => 'active',
        'salary'        => 6000000,
    ], $override);
}

function buatPayroll(Employee $employee, array $atribut = []): Payroll
{
    $data = array_merge([
        'employee_id' => $employee->id,
        'salary'      => 5000000,
        'bonuses'     => 500000,
        'deductions'  => 250000,
        'pay_date'    => now()->format('Y-m-d'),
    ], $atribut);
    $data['net_salary'] ??= $data['salary'] + $data['bonuses'] - $data['deductions'];

    return Payroll::create($data);
}

function buatPresensi(Employee $employee, array $atribut = []): Presence
{
    $tanggal = $atribut['date'] ?? now()->format('Y-m-d');

    return Presence::create(array_merge([
        'employee_id' => $employee->id,
        'check_in'    => "$tanggal 08:00:00",
        'check_out'   => "$tanggal 17:00:00",
        'date'        => $tanggal,
        'status'      => 'present',
        'latitude'    => '-7.2575',
        'longitude'   => '112.7521',
    ], $atribut));
}

function buatTugas(Employee $employee, array $atribut = []): Task
{
    return Task::create(array_merge([
        'title'       => 'Tugas untuk '.$employee->fullname,
        'description' => 'Deskripsi tugas uji',
        'assigned_to' => $employee->id,
        'due_date'    => now()->addWeek()->format('Y-m-d'),
        'status'      => 'pending',
    ], $atribut));
}

function buatCuti(Employee $employee, array $atribut = []): LeaveRequest
{
    return LeaveRequest::create(array_merge([
        'employee_id' => $employee->id,
        'leave_type'  => 'Vacation',
        'start_date'  => now()->addDays(7)->format('Y-m-d'),
        'end_date'    => now()->addDays(9)->format('Y-m-d'),
        'status'      => 'pending',
    ], $atribut));
}

// Sidik jari seluruh data bisnis: dipakai untuk membuktikan "tidak ada data yang berubah"
function snapshotData(): string
{
    $tabel = ['users', 'employees', 'departments', 'roles', 'tasks', 'payroll', 'presences', 'leave_requests'];

    return md5(json_encode(
        collect($tabel)->mapWithKeys(fn ($nama) => [$nama => DB::table($nama)->orderBy('id')->get()])
    ));
}

// Persentil ke-p (0-100) dari kumpulan durasi
function persentil(array $nilai, int $p): float
{
    sort($nilai);
    $indeks = (int) ceil(($p / 100) * count($nilai)) - 1;

    return $nilai[max(0, $indeks)];
}
