<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. DEPARTMENTS (5) ──────────────────────────────────────
        $deptHR  = Department::create(['name' => 'Human Resources',       'description' => 'Rekrutmen, pengembangan SDM, dan kepatuhan ketenagakerjaan.', 'status' => 'active']);
        $deptIT  = Department::create(['name' => 'Information Technology', 'description' => 'Pengembangan software dan infrastruktur teknologi.',           'status' => 'active']);
        $deptFin = Department::create(['name' => 'Finance',               'description' => 'Pengelolaan keuangan, akuntansi, dan perpajakan.',             'status' => 'active']);
        $deptMkt = Department::create(['name' => 'Marketing',             'description' => 'Strategi pemasaran, branding, dan pengembangan pasar.',        'status' => 'active']);
        $deptOps = Department::create(['name' => 'Operations',            'description' => 'Operasional harian, logistik, dan pengadaan.',                 'status' => 'active']);

        // ── 2. ROLES (10) ────────────────────────────────────────────
        // title 'HR', 'Developer', 'Sales' = role sistem (bisa login)
        $roleHR    = Role::create(['title' => 'HR',              'description' => 'Akses penuh ke seluruh modul sistem.']);
        $roleDev   = Role::create(['title' => 'Developer',       'description' => 'Akses ke tugas, absensi, dan slip gaji.']);
        $roleSales = Role::create(['title' => 'Sales',           'description' => 'Akses ke tugas, absensi, dan slip gaji.']);
        $roleFin   = Role::create(['title' => 'Finance',         'description' => 'Staf keuangan dan akuntansi.']);
        $roleMkt   = Role::create(['title' => 'Marketing',       'description' => 'Staf pemasaran dan brand.']);
        $roleOps   = Role::create(['title' => 'Operations',      'description' => 'Staf operasional dan logistik.']);
        $roleAna   = Role::create(['title' => 'Analyst',         'description' => 'Analis data dan bisnis.']);
        $roleDes   = Role::create(['title' => 'Designer',        'description' => 'UI/UX Designer.']);
        $rolePM    = Role::create(['title' => 'Project Manager', 'description' => 'Manajemen proyek dan koordinasi tim.']);
        $roleLegal = Role::create(['title' => 'Legal',           'description' => 'Hukum dan kepatuhan regulasi.']);

        // ── 3. EMPLOYEES (20) ────────────────────────────────────────
        $empData = [
            // Human Resources
            ['fullname' => 'Dewi Anggraeni',    'email' => 'dewi.anggraeni@hiro.co.id',    'phone' => '08121100001', 'address' => 'Jl. Sudirman No. 88, Jakarta',       'birth' => '1985-04-12', 'hire' => '2018-01-15', 'dept' => $deptHR,  'role' => $roleHR,    'salary' => 15000000],
            ['fullname' => 'Bagas Permana',     'email' => 'bagas.permana@hiro.co.id',     'phone' => '08121100002', 'address' => 'Jl. Citarum No. 5, Bandung',         'birth' => '1990-09-03', 'hire' => '2019-06-01', 'dept' => $deptHR,  'role' => $roleHR,    'salary' => 9000000],
            ['fullname' => 'Yuliana Kristanti', 'email' => 'yuliana.kristanti@hiro.co.id', 'phone' => '08121100003', 'address' => 'Jl. Pemuda No. 23, Semarang',        'birth' => '1993-07-18', 'hire' => '2021-03-01', 'dept' => $deptHR,  'role' => $roleLegal, 'salary' => 7500000],
            ['fullname' => 'Fatchur Rohman',    'email' => 'fatchur.rohman@hiro.co.id',    'phone' => '08121100004', 'address' => 'Jl. Diponegoro No. 17, Surabaya',    'birth' => '1996-01-25', 'hire' => '2022-08-10', 'dept' => $deptHR,  'role' => $roleHR,    'salary' => 6500000],
            // Information Technology
            ['fullname' => 'Evan Prasetyo',     'email' => 'evan.prasetyo@hiro.co.id',     'phone' => '08121100005', 'address' => 'Jl. Mampang No. 41, Jakarta Sel.',   'birth' => '1989-11-07', 'hire' => '2017-06-01', 'dept' => $deptIT,  'role' => $roleDev,   'salary' => 20000000],
            ['fullname' => 'Priscilla Thalia',  'email' => 'priscilla.thalia@hiro.co.id',  'phone' => '08121100006', 'address' => 'Jl. Pluit No. 12, Jakarta Utara',    'birth' => '1994-03-22', 'hire' => '2019-07-15', 'dept' => $deptIT,  'role' => $roleDev,   'salary' => 16000000],
            ['fullname' => 'Reza Firdaus',      'email' => 'reza.firdaus@hiro.co.id',      'phone' => '08121100007', 'address' => 'Jl. Ciputat Raya No. 9, Tangsel',    'birth' => '1995-06-14', 'hire' => '2020-11-01', 'dept' => $deptIT,  'role' => $roleDev,   'salary' => 14000000],
            ['fullname' => 'Gita Cahyani',      'email' => 'gita.cahyani@hiro.co.id',      'phone' => '08121100008', 'address' => 'Jl. Kebayoran Lama No. 77, Jakarta', 'birth' => '1997-10-30', 'hire' => '2021-08-16', 'dept' => $deptIT,  'role' => $roleDes,   'salary' => 12000000],
            ['fullname' => 'Stevanus Halim',    'email' => 'stevanus.halim@hiro.co.id',    'phone' => '08121100009', 'address' => 'Jl. Mangga Dua No. 35, Jakarta Bar.', 'birth' => '1991-05-08', 'hire' => '2018-04-02', 'dept' => $deptIT,  'role' => $rolePM,    'salary' => 18000000],
            // Finance
            ['fullname' => 'Bambang Setiawan',  'email' => 'bambang.setiawan@hiro.co.id',  'phone' => '08121100010', 'address' => 'Jl. Kramat Raya No. 60, Jakarta',    'birth' => '1980-02-14', 'hire' => '2016-01-04', 'dept' => $deptFin, 'role' => $roleSales, 'salary' => 17000000],
            ['fullname' => 'Kartika Sari',      'email' => 'kartika.sari@hiro.co.id',      'phone' => '08121100011', 'address' => 'Jl. Tebet Barat No. 29, Jakarta',    'birth' => '1988-08-19', 'hire' => '2018-02-20', 'dept' => $deptFin, 'role' => $roleFin,   'salary' => 11000000],
            ['fullname' => 'Wahyu Nugroho',     'email' => 'wahyu.nugroho@hiro.co.id',     'phone' => '08121100012', 'address' => 'Jl. Jagakarsa No. 14, Jakarta',      'birth' => '1993-12-05', 'hire' => '2020-07-01', 'dept' => $deptFin, 'role' => $roleAna,   'salary' => 9500000],
            ['fullname' => 'Meisya Anjani',     'email' => 'meisya.anjani@hiro.co.id',     'phone' => '08121100013', 'address' => 'Jl. Lebak Bulus No. 8, Jakarta',     'birth' => '1998-04-27', 'hire' => '2023-01-16', 'dept' => $deptFin, 'role' => $roleFin,   'salary' => 7000000],
            // Marketing
            ['fullname' => 'Agus Hermawan',     'email' => 'agus.hermawan@hiro.co.id',     'phone' => '08121100014', 'address' => 'Jl. Raya Bogor No. 200, Jakarta',    'birth' => '1986-07-31', 'hire' => '2017-10-01', 'dept' => $deptMkt, 'role' => $roleSales, 'salary' => 15000000],
            ['fullname' => 'Rachel Oktaviani',  'email' => 'rachel.oktaviani@hiro.co.id',  'phone' => '08121100015', 'address' => 'Jl. Kelapa Gading No. 3, Jakarta',   'birth' => '1996-10-11', 'hire' => '2021-03-01', 'dept' => $deptMkt, 'role' => $roleMkt,   'salary' => 8500000],
            ['fullname' => 'Dodi Firmansyah',   'email' => 'dodi.firmansyah@hiro.co.id',   'phone' => '08121100016', 'address' => 'Jl. Condet No. 55, Jakarta Timur',   'birth' => '1991-03-16', 'hire' => '2019-01-07', 'dept' => $deptMkt, 'role' => $roleSales, 'salary' => 12000000],
            ['fullname' => 'Novia Rahayu',      'email' => 'novia.rahayu@hiro.co.id',      'phone' => '08121100017', 'address' => 'Jl. Pondok Labu No. 16, Jakarta',    'birth' => '1999-01-20', 'hire' => '2023-06-12', 'dept' => $deptMkt, 'role' => $roleDes,   'salary' => 7500000],
            // Operations
            ['fullname' => 'Suryo Prabowo',     'email' => 'suryo.prabowo@hiro.co.id',     'phone' => '08121100018', 'address' => 'Jl. Pahlawan No. 77, Bekasi',        'birth' => '1985-06-23', 'hire' => '2018-08-01', 'dept' => $deptOps, 'role' => $roleDev,   'salary' => 13000000],
            ['fullname' => 'Hadianti Putri',    'email' => 'hadianti.putri@hiro.co.id',    'phone' => '08121100019', 'address' => 'Jl. Raya Depok No. 34, Depok',       'birth' => '1994-09-09', 'hire' => '2021-11-01', 'dept' => $deptOps, 'role' => $roleOps,   'salary' => 8000000],
            ['fullname' => 'Leonardus Suwito',  'email' => 'leonardus.suwito@hiro.co.id',  'phone' => '08121100020', 'address' => 'Jl. Glodok No. 11, Jakarta Barat',   'birth' => '1990-12-15', 'hire' => '2019-09-23', 'dept' => $deptOps, 'role' => $roleOps,   'salary' => 9000000],
        ];

        $employees = [];
        foreach ($empData as $e) {
            $employees[] = Employee::create([
                'fullname'      => $e['fullname'],
                'email'         => $e['email'],
                'phone_number'  => $e['phone'],
                'address'       => $e['address'],
                'birth_date'    => $e['birth'],
                'hire_date'     => $e['hire'],
                'department_id' => $e['dept']->id,
                'role_id'       => $e['role']->id,
                'status'        => 'active',
                'salary'        => $e['salary'],
            ]);
        }

        // ── 4. USER ACCOUNTS (semua 20 employee) ─────────────────────
        foreach ($employees as $i => $emp) {
            \App\Models\User::create([
                'name'              => $emp->fullname,
                'email'             => $emp->email,
                'password'          => 'password',
                'employee_id'       => $emp->id,
                'role_id'           => $empData[$i]['role']->id,
                'email_verified_at' => Carbon::now(),
            ]);
        }

        // ── 5. PAYROLL (10 record — 1 per karyawan, 10 karyawan pertama) ──
        $payDate = Carbon::now()->startOfMonth()->addDays(24)->toDateString();
        $payrollRows = [];
        foreach (array_slice($employees, 0, 10) as $i => $emp) {
            $salary    = $empData[$i]['salary'];
            $bpjs      = min((int)($salary * 0.04), 480000);
            $bonus     = $i % 3 === 0 ? (int)($salary * 0.10) : 0;
            $deduction = $bpjs;
            $payrollRows[] = [
                'employee_id' => $emp->id,
                'salary'      => $salary,
                'bonuses'     => $bonus,
                'deductions'  => $deduction,
                'net_salary'  => $salary + $bonus - $deduction,
                'pay_date'    => $payDate,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ];
        }
        DB::table('payroll')->insert($payrollRows);

        // ── 6. PRESENCES (10 record — 1 per karyawan, 10 karyawan pertama, hari ini) ──
        $today = Carbon::now();
        // Pastikan hari kerja (jika weekend, pakai Jumat kemarin)
        if ($today->isWeekend()) {
            $today = $today->previous(Carbon::FRIDAY);
        }
        $presenceStatuses = ['present', 'present', 'present', 'present', 'present', 'present', 'late', 'present', 'sick', 'present'];
        $presenceRows = [];
        foreach (array_slice($employees, 0, 10) as $i => $emp) {
            $status = $presenceStatuses[$i];
            if ($status === 'sick') {
                $checkIn  = $today->copy()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $checkOut = $checkIn;
            } elseif ($status === 'late') {
                $checkIn  = $today->copy()->setTime(9, rand(31, 60), rand(0, 59))->format('Y-m-d H:i:s');
                $checkOut = $today->copy()->setTime(17, rand(0, 30), rand(0, 59))->format('Y-m-d H:i:s');
            } else {
                $checkIn  = $today->copy()->setTime(8, rand(0, 15), rand(0, 59))->format('Y-m-d H:i:s');
                $checkOut = $today->copy()->setTime(17, rand(0, 30), rand(0, 59))->format('Y-m-d H:i:s');
            }
            $presenceRows[] = [
                'employee_id' => $emp->id,
                'check_in'    => $checkIn,
                'check_out'   => $checkOut,
                'date'        => $today->toDateString(),
                'status'      => $status,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ];
        }
        DB::table('presences')->insert($presenceRows);

        // ── 7. LEAVE REQUESTS (5 data) ───────────────────────────────
        $leaveRows = [
            ['employee_id' => $employees[0]->id, 'leave_type' => 'annual leave',   'start_date' => Carbon::now()->addDays(7)->toDateString(),  'end_date' => Carbon::now()->addDays(9)->toDateString(),  'status' => 'pending'],
            ['employee_id' => $employees[4]->id, 'leave_type' => 'sick leave',     'start_date' => Carbon::now()->subDays(3)->toDateString(),   'end_date' => Carbon::now()->subDays(2)->toDateString(),  'status' => 'approved'],
            ['employee_id' => $employees[7]->id, 'leave_type' => 'personal leave', 'start_date' => Carbon::now()->addDays(14)->toDateString(), 'end_date' => Carbon::now()->addDays(14)->toDateString(), 'status' => 'pending'],
            ['employee_id' => $employees[9]->id, 'leave_type' => 'annual leave',   'start_date' => Carbon::now()->subDays(10)->toDateString(),  'end_date' => Carbon::now()->subDays(8)->toDateString(),  'status' => 'approved'],
            ['employee_id' => $employees[13]->id,'leave_type' => 'emergency leave','start_date' => Carbon::now()->subDays(1)->toDateString(),   'end_date' => Carbon::now()->toDateString(),              'status' => 'approved'],
        ];
        foreach ($leaveRows as &$row) {
            $row['created_at'] = Carbon::now();
            $row['updated_at'] = Carbon::now();
        }
        DB::table('leave_requests')->insert($leaveRows);

        // ── 8. TASKS (5 data) ────────────────────────────────────────
        $taskRows = [
            ['title' => 'Rekrutmen Junior Developer Q3',        'description' => 'Buka lowongan, screening CV, dan wawancara kandidat.',        'assigned_to' => $employees[0]->id,  'due_date' => Carbon::now()->addDays(14)->toDateString(), 'status' => 'in_progress'],
            ['title' => 'Migrasi Backend ke Microservices',     'description' => 'Pisahkan modul monolith menjadi 5 layanan terpisah.',          'assigned_to' => $employees[4]->id,  'due_date' => Carbon::now()->addDays(45)->toDateString(), 'status' => 'in_progress'],
            ['title' => 'Laporan Keuangan Q2 2026',             'description' => 'Susun laporan konsolidasi dan presentasikan ke direksi.',      'assigned_to' => $employees[9]->id,  'due_date' => Carbon::now()->addDays(8)->toDateString(),  'status' => 'pending'],
            ['title' => 'Kampanye Digital Marketing Produk',    'description' => 'Rancang kampanye multi-channel untuk meningkatkan leads 40%.', 'assigned_to' => $employees[13]->id, 'due_date' => Carbon::now()->addDays(20)->toDateString(), 'status' => 'pending'],
            ['title' => 'Audit Inventarisasi Aset Kantor',      'description' => 'Pendataan ulang seluruh aset dan perbarui sistem inventaris.',  'assigned_to' => $employees[17]->id, 'due_date' => Carbon::now()->addDays(10)->toDateString(), 'status' => 'completed'],
        ];
        foreach ($taskRows as &$row) {
            $row['created_at'] = Carbon::now();
            $row['updated_at'] = Carbon::now();
        }
        DB::table('tasks')->insert($taskRows);

        // ── OUTPUT ───────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅  Hiro — Data berhasil dibuat!');
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info('  Perusahaan  : PT. Nusantara Human Capital');
        $this->command->info('  Departments : 5  |  Roles : 10  |  Employees : 20');
        $this->command->info('  Users       : 20 akun  |  Password : password');
        $this->command->info('  Payroll     : 10  |  Presences : 10  |  Tasks : 5');
        $this->command->info('────────────────────────────────────────────────────────');
        $this->command->info('  Contoh login:');
        $this->command->info('  [HR]       dewi.anggraeni@hiro.co.id');
        $this->command->info('  [Dev]      evan.prasetyo@hiro.co.id');
        $this->command->info('  [Sales]    bambang.setiawan@hiro.co.id');
        $this->command->info('  [Finance]  kartika.sari@hiro.co.id');
        $this->command->info('  [Designer] gita.cahyani@hiro.co.id');
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info('');
    }
}
