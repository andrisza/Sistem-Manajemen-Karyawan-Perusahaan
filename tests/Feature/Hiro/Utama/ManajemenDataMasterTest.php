<?php

/*
|--------------------------------------------------------------------------
| Test Case Utama — Manajemen data master / CRUD (TCU-F-09 s.d. TCU-F-15, TCU-F-20, TCU-F-21)
|--------------------------------------------------------------------------
| Entitas HIRO: karyawan, departemen, jabatan (role), payroll.
*/

use App\Mail\AccountInvitation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

uses()->group('hiro', 'tcu', 'tcu-fungsional');

beforeEach(function () {
    Mail::fake();
    $this->hr = buatHR();
    $this->actingAs($this->hr);
});

describe('TCU-F-09 Tambah data master dengan data valid', function () {
    test('departemen tersimpan dan tampil di daftar dengan nilai yang sama', function () {
        $this->post('/departments', ['name' => 'Keuangan', 'description' => 'Divisi keuangan', 'status' => 'active'])
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', ['name' => 'Keuangan', 'description' => 'Divisi keuangan', 'status' => 'active']);
        $this->get('/departments')->assertSee('Keuangan')->assertSee('Divisi keuangan');
    });

    test('jabatan tersimpan dan tampil di daftar dengan nilai yang sama', function () {
        $this->post('/roles', ['title' => 'Supervisor', 'description' => 'Mengawasi tim'])
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', ['title' => 'Supervisor', 'description' => 'Mengawasi tim']);
        $this->get('/roles')->assertSee('Supervisor');
    });

    test('karyawan tersimpan, akun login terbentuk, dan tampil di daftar', function () {
        $data = dataKaryawanBaru(['fullname' => 'Siti Aminah', 'email' => 'siti@hiro.test']);

        $this->post('/employees', $data)->assertRedirect(route('employees.index'));

        $employee = Employee::where('email', 'siti@hiro.test')->firstOrFail();
        expect($employee->only(['fullname', 'address', 'status']))
            ->toBe(['fullname' => 'Siti Aminah', 'address' => $data['address'], 'status' => 'active'])
            ->and((float) $employee->salary)->toBe((float) $data['salary'])
            ->and(User::where('email', 'siti@hiro.test')->value('employee_id'))->toBe($employee->id);

        $this->get('/employees')->assertSee('Siti Aminah');
    });
});

describe('TCU-F-10 Tambah data dengan input tidak valid', function () {
    test('karyawan ditolak dengan pesan per field dan tidak ada data parsial', function (array $override, string $field) {
        $karyawanSebelum = Employee::count();
        $userSebelum = User::count();

        $this->from('/employees/create')
            ->post('/employees', dataKaryawanBaru($override))
            ->assertRedirect('/employees/create')
            ->assertSessionHasErrors($field);

        expect(Employee::count())->toBe($karyawanSebelum)
            ->and(User::count())->toBe($userSebelum);
        Mail::assertNothingSent();
    })->with([
        'nama kosong'              => [['fullname' => ''], 'fullname'],
        'nama melebihi 255'        => [['fullname' => str_repeat('a', 256)], 'fullname'],
        'email kosong'             => [['email' => ''], 'email'],
        'email format salah'       => [['email' => 'bukan-email'], 'email'],
        'tanggal lahir bukan tgl'  => [['birth_date' => 'kemarin sore'], 'birth_date'],
        'gaji bukan angka'         => [['salary' => 'lima juta'], 'salary'],
        'departemen tidak ada'     => [['department_id' => 99999], 'department_id'],
        'jabatan tidak ada'        => [['role_id' => 99999], 'role_id'],
        'nomor telepon kosong'     => [['phone_number' => ''], 'phone_number'],
    ]);

    test('departemen ditolak dengan pesan per field', function (array $payload, string $field) {
        $this->from('/departments/create')->post('/departments', $payload)
            ->assertSessionHasErrors($field);

        expect(Department::where('name', '!=', 'Umum')->count())->toBe(0);
    })->with([
        'nama kosong'       => [['name' => '', 'status' => 'active'], 'name'],
        'nama melebihi 255' => [['name' => str_repeat('x', 256), 'status' => 'active'], 'name'],
        'status kosong'     => [['name' => 'Legal', 'status' => ''], 'status'],
    ]);
});

describe('TCU-F-11 Tambah data dengan nilai unik yang sudah ada', function () {
    test('email karyawan duplikat ditolak dan data lama tidak berubah', function () {
        $lama = karyawanDari(buatKaryawan('Staff', ['email' => 'ganda@hiro.test', 'fullname' => 'Pemilik Asli']));

        $this->post('/employees', dataKaryawanBaru(['email' => 'ganda@hiro.test', 'fullname' => 'Penyusup']))
            ->assertSessionHasErrors('email');

        expect(Employee::where('email', 'ganda@hiro.test')->count())->toBe(1)
            ->and($lama->fresh()->fullname)->toBe('Pemilik Asli');
    });

    test('email yang sudah dipakai akun login (tabel users) ditolak', function () {
        User::factory()->create(['email' => 'akun@hiro.test']);

        $this->post('/employees', dataKaryawanBaru(['email' => 'akun@hiro.test']))
            ->assertSessionHasErrors('email');

        expect(Employee::where('email', 'akun@hiro.test')->exists())->toBeFalse();
    });

    // Asumsi aturan bisnis: nama departemen dan nama jabatan bersifat unik
    test('nama departemen duplikat ditolak', function () {
        Department::create(['name' => 'Keuangan', 'status' => 'active']);

        $this->post('/departments', ['name' => 'Keuangan', 'status' => 'active'])
            ->assertSessionHasErrors('name');

        expect(Department::where('name', 'Keuangan')->count())->toBe(1);
    });

    test('nama jabatan duplikat ditolak', function () {
        Role::create(['title' => 'Supervisor']);

        $this->post('/roles', ['title' => 'Supervisor'])->assertSessionHasErrors('title');

        expect(Role::where('title', 'Supervisor')->count())->toBe(1);
    });
});

describe('TCU-F-12 Lihat daftar dan detail', function () {
    test('daftar karyawan sama persis dengan database', function () {
        collect(range(1, 5))->each(fn () => buatKaryawan());

        $this->get('/employees')
            ->assertOk()
            ->assertViewHas('employees', fn ($daftar) => $daftar->pluck('id')->sort()->values()->all()
                === Employee::orderBy('id')->pluck('id')->all());
    });

    test('daftar departemen dan jabatan tidak menampilkan data yang sudah dihapus', function () {
        Department::create(['name' => 'Dihapus', 'status' => 'active'])->delete();
        Role::create(['title' => 'Dihapus'])->delete();

        $this->get('/departments')->assertViewHas('departments', fn ($d) => $d->count() === Department::count())
            ->assertDontSee('Dihapus');
        $this->get('/roles')->assertViewHas('roles', fn ($r) => $r->count() === Role::count())
            ->assertDontSee('Dihapus');
    });

    test('detail karyawan menampilkan nilai sesuai database', function () {
        $employee = karyawanDari(buatKaryawan('Staff', ['fullname' => 'Rina Kartika', 'email' => 'rina@hiro.test']));

        $this->get("/employees/{$employee->id}")
            ->assertOk()
            ->assertSee('Rina Kartika')
            ->assertSee('rina@hiro.test')
            ->assertSee('Staff')
            ->assertSee('Umum');
    });

    test('detail data yang tidak ada dijawab 404', function () {
        $this->get('/employees/99999')->assertNotFound();
    });

    test('pencarian, filter, dan paginasi', fn () => null)
        ->skip('MANUAL/BROWSER: pencarian & paginasi dilakukan di sisi klien (simple-datatables), tidak ada parameter server-side yang bisa diuji lewat HTTP.');
});

describe('TCU-F-13 Ubah data lalu cek semua halaman terkait', function () {
    test('perubahan karyawan konsisten di daftar, detail, akun login, dan payroll', function () {
        $employee = karyawanDari(buatKaryawan('Staff', ['fullname' => 'Nama Lama', 'email' => 'lama@hiro.test']));
        buatPayroll($employee);

        $this->put("/employees/{$employee->id}", dataKaryawanBaru(['fullname' => 'Nama Baru', 'email' => 'baru@hiro.test']))
            ->assertRedirect(route('employees.index'))
            ->assertSessionHasNoErrors();

        expect($employee->fresh()->fullname)->toBe('Nama Baru');
        $this->assertDatabaseHas('users', ['employee_id' => $employee->id, 'name' => 'Nama Baru', 'email' => 'baru@hiro.test']);

        $this->get('/employees')->assertSee('Nama Baru')->assertDontSee('Nama Lama');
        $this->get("/employees/{$employee->id}")->assertSee('baru@hiro.test');
        $this->get('/payrolls')->assertSee('Nama Baru');
    });

    test('karyawan dapat login memakai email barunya', function () {
        $employee = karyawanDari(buatKaryawan('Staff', ['email' => 'lama@hiro.test']));

        $this->put("/employees/{$employee->id}", dataKaryawanBaru(['email' => 'baru@hiro.test']));
        auth()->logout();

        $this->post('/login', ['email' => 'baru@hiro.test', 'password' => PASSWORD_UJI]);
        $this->assertAuthenticated();
    });
});

describe('TCU-F-14 Hapus data dengan konfirmasi', function () {
    test('setiap tombol hapus di halaman daftar meminta konfirmasi', function (string $url) {
        $staff = karyawanDari(buatKaryawan());
        Department::create(['name' => 'Legal', 'status' => 'active']);
        Role::create(['title' => 'Supervisor']);
        buatPayroll($staff);
        buatPresensi($staff);
        buatTugas($staff);
        buatCuti($staff);

        $html = $this->get($url)->assertOk()->getContent();

        expect($html)->toContain('value="DELETE"');
        expect(preg_match('/confirm\(|Swal\.fire|data-confirm|onsubmit/i', $html))
            ->toBe(1, "Halaman $url tidak memiliki dialog konfirmasi hapus");
    })->with(['/departments', '/roles', '/employees', '/payrolls', '/presences', '/tasks', '/leave-requests']);

    test('konfirmasi hapus menghilangkan data dari daftar; tanpa konfirmasi data tetap', function () {
        $tetap = Department::create(['name' => 'Tetap Ada', 'status' => 'active']);
        $hapus = Department::create(['name' => 'Akan Dihapus', 'status' => 'active']);

        $this->delete("/departments/{$hapus->id}")->assertRedirect(route('departments.index'));

        $this->assertSoftDeleted($hapus);
        $this->assertNotSoftDeleted($tetap);
        $this->get('/departments')->assertSee('Tetap Ada')->assertDontSee('Akan Dihapus');
    });
});

describe('TCU-F-15 Hapus data yang dipakai data lain', function () {
    test('departemen yang masih berisi karyawan tidak boleh terhapus', function () {
        $employee = karyawanDari(buatKaryawan());
        $departemen = $employee->department;

        $this->delete("/departments/{$departemen->id}");

        $this->assertNotSoftDeleted($departemen);
        $this->get("/employees/{$employee->id}")->assertOk();
    });

    test('jabatan yang masih dipakai karyawan tidak boleh terhapus', function () {
        $employee = karyawanDari(buatKaryawan('Analis'));

        $this->delete("/roles/{$employee->role_id}");

        $this->assertNotSoftDeleted('roles', ['id' => $employee->role_id]);
        $this->get("/employees/{$employee->id}")->assertOk();
    });

    test('hapus karyawan yang punya tugas dan cuti ditangani tanpa data yatim', function () {
        $employee = karyawanDari(buatKaryawan());
        buatTugas($employee);
        buatCuti($employee);

        $respons = $this->delete("/employees/{$employee->id}");

        expect($respons->status())->toBeLessThan(500);
        if (Employee::find($employee->id)) {
            // Ditolak: wajib ada pesan yang menjelaskan
            $respons->assertSessionHasErrors();
        } else {
            expect(Task::where('assigned_to', $employee->id)->count())->toBe(0)
                ->and(LeaveRequest::where('employee_id', $employee->id)->count())->toBe(0);
        }
    });
});

describe('TCU-F-20 Umpan balik pada setiap aksi', function () {
    test('aksi sukses menampilkan pesan yang jelas', function (string $method, string $url, array $payload, string $pesan) {
        $this->followingRedirects()->call($method, $url, $payload)->assertOk()->assertSee($pesan);
    })->with([
        'tambah departemen' => ['POST', '/departments', ['name' => 'Legal', 'status' => 'active'], 'Department created successfully.'],
        'ubah departemen'   => ['PUT', fn () => '/departments/'.Department::create(['name' => 'Lama', 'status' => 'active'])->id, ['name' => 'Baru', 'status' => 'active'], 'Department updated successfully.'],
        'hapus departemen'  => ['DELETE', fn () => '/departments/'.Department::create(['name' => 'Lama', 'status' => 'active'])->id, [], 'Department deleted successfully.'],
        'tambah jabatan'    => ['POST', '/roles', ['title' => 'Auditor'], 'Role created successfully.'],
        'tambah payroll'    => ['POST', '/payrolls', fn () => ['employee_id' => karyawanDari(buatKaryawan())->id, 'salary' => 5000000, 'bonuses' => 0, 'deductions' => 0, 'pay_date' => '2026-10-01'], 'Payroll created successfully'],
        'hapus payroll'     => ['DELETE', fn () => '/payrolls/'.buatPayroll(karyawanDari(buatKaryawan()))->id, [], 'Payroll deleted successfully'],
        'tambah tugas'      => ['POST', '/tasks', fn () => ['title' => 'Rekap', 'assigned_to' => karyawanDari(buatKaryawan())->id, 'due_date' => '2026-12-31', 'status' => 'pending'], 'Task created successfully.'],
    ]);

    test('aksi gagal menampilkan pesan validasi', function () {
        $this->followingRedirects()
            ->from('/departments/create')
            ->post('/departments', ['name' => '', 'status' => ''])
            ->assertSee('The name field is required.');
    });
});

describe('TCU-F-21 Klik ganda cepat pada tombol simpan', function () {
    // Simulasi dua request identik yang terkirim beruntun sebelum respons pertama diterima
    test('hanya satu data tersimpan', function (string $url, Closure $payload, Closure $hitung) {
        $data = $payload();

        $this->post($url, $data);
        $this->post($url, $data);

        expect($hitung())->toBe(1);
    })->with([
        // Parameter bertipe Closure tidak di-resolve Pest, sehingga dievaluasi di dalam test
        'karyawan'   => ['/employees', fn () => dataKaryawanBaru(['email' => 'klik@hiro.test']), fn () => Employee::where('email', 'klik@hiro.test')->count()],
        'departemen' => ['/departments', fn () => ['name' => 'Legal', 'status' => 'active'], fn () => Department::where('name', 'Legal')->count()],
        'payroll'    => ['/payrolls', fn () => ['employee_id' => Employee::first()->id, 'salary' => 5000000, 'bonuses' => 0, 'deductions' => 0, 'pay_date' => '2026-10-01'], fn () => Payroll::count()],
    ]);
});
