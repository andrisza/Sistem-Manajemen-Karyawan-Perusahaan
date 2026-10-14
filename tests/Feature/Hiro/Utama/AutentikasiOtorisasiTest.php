<?php

/*
|--------------------------------------------------------------------------
| Test Case Utama — Autentikasi, otorisasi, dan sesi (TCU-F-01 s.d. TCU-F-08)
|--------------------------------------------------------------------------
| Peran di HIRO: 'HR' (HR Admin) dan jabatan lain, mis. 'Staff' (Karyawan).
*/

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;

uses()->group('hiro', 'tcu', 'tcu-fungsional');

describe('TCU-F-01 Login dengan kredensial valid untuk tiap peran', function () {
    test('masuk ke dashboard dengan peran yang benar', function (string $jabatan) {
        $user = buatKaryawan($jabatan);

        $this->post('/login', ['email' => $user->email, 'password' => PASSWORD_UJI])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSessionHas('role', $jabatan)
            ->assertSessionHas('employee_id', $user->employee_id);
    })->with(['HR Admin' => 'HR', 'Karyawan' => 'Staff']);
});

describe('TCU-F-02 Login dengan kredensial salah', function () {
    test('ditolak, tanpa sesi', function (string $email, string $password) {
        buatKaryawan('Staff', ['email' => 'terdaftar@hiro.test']);

        $this->post('/login', compact('email', 'password'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    })->with([
        'password salah'        => ['terdaftar@hiro.test', 'password-salah'],
        'email tidak terdaftar' => ['tidak.ada@hiro.test', PASSWORD_UJI],
        'keduanya salah'        => ['tidak.ada@hiro.test', 'password-salah'],
    ]);

    test('pesan error identik untuk semua kasus sehingga tidak membocorkan keberadaan akun', function () {
        buatKaryawan('Staff', ['email' => 'terdaftar@hiro.test']);

        $pesan = [];
        foreach ([
            ['terdaftar@hiro.test', 'password-salah'],
            ['tidak.ada@hiro.test', PASSWORD_UJI],
            ['tidak.ada@hiro.test', 'password-salah'],
        ] as [$email, $password]) {
            $this->post('/login', compact('email', 'password'));
            $pesan[] = session('errors')->first('email');
        }

        expect(array_unique($pesan))->toHaveCount(1)
            ->and($pesan[0])->not->toBeEmpty();
    });
});

describe('TCU-F-03 Validasi input login', function () {
    test('input tidak valid ditolak per field tanpa crash', function (string $email, string $password, array $fieldError) {
        $this->post('/login', compact('email', 'password'))
            ->assertStatus(302)
            ->assertSessionHasErrors($fieldError);

        $this->assertGuest();
    })->with([
        'semua kosong'   => ['', '', ['email', 'password']],
        'hanya spasi'    => ['     ', '     ', ['email']],
        'sangat panjang' => [str_repeat('a', 5000).'@hiro.test', str_repeat('p', 10000), ['email']],
    ]);
});

describe('TCU-F-04 Logout, tombol Back, lalu buka ulang URL terproteksi', function () {
    test('sesi berakhir dan URL terproteksi diarahkan ke login', function () {
        $user = buatKaryawan();
        $this->post('/login', ['email' => $user->email, 'password' => PASSWORD_UJI]);
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/payrolls')->assertRedirect('/login');
    });

    test('halaman terproteksi tidak boleh disimpan cache browser (Cache-Control: no-store)', function () {
        $this->actingAs(buatKaryawan());

        $cacheControl = $this->get('/dashboard')->headers->get('Cache-Control');

        expect($cacheControl)->toContain('no-store');
    });
});

describe('TCU-F-05 Buka URL terproteksi tanpa login', function () {
    test('dialihkan ke halaman login', function (string $url) {
        $this->get($url)->assertRedirect('/login');
    })->with([
        '/dashboard', '/profile', '/employees', '/departments', '/roles', '/tasks', '/presences',
        '/presences/create', '/payrolls', '/payroll/1/download', '/leave-requests', '/leave-requests/create',
    ]);

    test('request API tanpa login dijawab 401 tanpa data', function () {
        $this->getJson('/dashboard/presences')
            ->assertUnauthorized()
            ->assertJsonMissingPath('0');
    });
});

describe('TCU-F-06 Otorisasi vertikal: karyawan membuka menu/endpoint HR', function () {
    test('ditolak 403 dan tidak ada data yang berubah', function (string $method, string $url, array $payload = []) {
        $staff = buatKaryawan('Staff');
        $sebelum = snapshotData();

        $this->actingAs($staff)->call($method, $url, $payload)->assertForbidden();

        expect(snapshotData())->toBe($sebelum, 'Data berubah oleh peran yang tidak berhak');
    })->with([
        // Menu dan endpoint yang sudah dibatasi middleware role:HR
        'daftar karyawan'     => ['GET', '/employees'],
        'form tambah karyawan' => ['GET', '/employees/create'],
        'detail karyawan'     => ['GET', fn () => '/employees/'.karyawanDari(buatKaryawan())->id],
        'simpan karyawan'     => ['POST', '/employees', fn () => dataKaryawanBaru()],
        'ubah karyawan'       => ['PUT', fn () => '/employees/'.karyawanDari(buatKaryawan())->id, fn () => dataKaryawanBaru()],
        'hapus karyawan'      => ['DELETE', fn () => '/employees/'.karyawanDari(buatKaryawan())->id],
        'daftar departemen'   => ['GET', '/departments'],
        'simpan departemen'   => ['POST', '/departments', ['name' => 'Ilegal', 'status' => 'active']],
        'ubah departemen'     => ['PUT', fn () => '/departments/'.Department::create(['name' => 'Keuangan', 'status' => 'active'])->id, ['name' => 'Diretas', 'status' => 'inactive']],
        'hapus departemen'    => ['DELETE', fn () => '/departments/'.Department::create(['name' => 'Keuangan', 'status' => 'active'])->id],
        'daftar jabatan'      => ['GET', '/roles'],
        'simpan jabatan'      => ['POST', '/roles', ['title' => 'Ilegal']],
        'hapus jabatan'       => ['DELETE', fn () => '/roles/'.Role::create(['title' => 'Manager'])->id],
        'setujui cuti'        => ['GET', fn () => '/leave-requests/confirm/'.buatCuti(karyawanDari(buatKaryawan()))->id],
        'tolak cuti'          => ['GET', fn () => '/leave-requests/reject/'.buatCuti(karyawanDari(buatKaryawan()))->id],
        // SKPL: payroll "semua role dapat melihat, hanya HR yang bisa kelola"
        'form tambah payroll' => ['GET', '/payrolls/create'],
        'simpan payroll'      => ['POST', '/payrolls', fn () => ['employee_id' => karyawanDari(buatKaryawan())->id, 'salary' => 9000000, 'bonuses' => 0, 'deductions' => 0, 'pay_date' => '2026-10-01']],
        'ubah payroll'        => ['PUT', fn () => '/payrolls/'.buatPayroll(karyawanDari(buatKaryawan()))->id, fn () => ['employee_id' => Employee::first()->id, 'salary' => 1, 'bonuses' => 0, 'deductions' => 0, 'pay_date' => '2026-10-01']],
        'hapus payroll'       => ['DELETE', fn () => '/payrolls/'.buatPayroll(karyawanDari(buatKaryawan()))->id],
        // SKPL: absensi manual/koreksi hanya oleh HR
        'ubah presensi'       => ['PUT', fn () => '/presences/'.buatPresensi(karyawanDari(buatKaryawan()))->id, fn () => ['employee_id' => Employee::first()->id, 'check_in' => '07:00:00', 'check_out' => '18:00:00', 'date' => '2026-10-01', 'status' => 'present']],
        'hapus presensi'      => ['DELETE', fn () => '/presences/'.buatPresensi(karyawanDari(buatKaryawan()))->id],
        // SKPL: "HR Admin memberi tugas"
        'buat tugas'          => ['POST', '/tasks', fn () => ['title' => 'Ilegal', 'assigned_to' => karyawanDari(buatKaryawan())->id, 'due_date' => '2026-12-31', 'status' => 'pending']],
        'hapus tugas'         => ['DELETE', fn () => '/tasks/'.buatTugas(karyawanDari(buatKaryawan()))->id],
    ]);
});

describe('TCU-F-07 Otorisasi horizontal: ubah ID pada URL ke data karyawan lain', function () {
    test('ditolak 403/404 dan data karyawan lain tidak tampil atau berubah', function (string $method, string $url, array $payload = []) {
        $pemilik = Employee::firstOrFail(); // dibuat oleh closure dataset
        $penyerang = buatKaryawan('Staff');
        $sebelum = snapshotData();

        $respons = $this->actingAs($penyerang)->call($method, $url, $payload);

        expect($respons->status())->toBeIn([403, 404], "Akses data milik karyawan lain dijawab HTTP {$respons->status()}, seharusnya 403/404");
        expect($respons->getContent())->not->toContain($pemilik->fullname);
        expect(snapshotData())->toBe($sebelum, 'Data milik karyawan lain berubah');
    })->with([
        'unduh slip gaji'      => ['GET', fn () => '/payroll/'.buatPayroll(karyawanDari(buatKaryawan()))->id.'/download'],
        'detail payroll'       => ['GET', fn () => '/payrolls/'.buatPayroll(karyawanDari(buatKaryawan()))->id],
        'detail tugas'         => ['GET', fn () => '/tasks/'.buatTugas(karyawanDari(buatKaryawan()))->id],
        'form edit tugas'      => ['GET', fn () => '/tasks/'.buatTugas(karyawanDari(buatKaryawan()))->id.'/edit'],
        'ubah tugas'           => ['PUT', fn () => '/tasks/'.buatTugas(karyawanDari(buatKaryawan()))->id, fn () => ['title' => 'Diretas', 'assigned_to' => Employee::first()->id, 'due_date' => '2026-12-31', 'status' => 'done']],
        'tandai tugas selesai' => ['GET', fn () => '/tasks/done/'.buatTugas(karyawanDari(buatKaryawan()))->id],
        'form edit cuti'       => ['GET', fn () => '/leave-requests/'.buatCuti(karyawanDari(buatKaryawan()))->id.'/edit'],
        'ubah cuti'            => ['PUT', fn () => '/leave-requests/'.buatCuti(karyawanDari(buatKaryawan()))->id, fn () => ['employee_id' => Employee::first()->id, 'leave_type' => 'Sick Leave', 'start_date' => '2026-11-01', 'end_date' => '2026-11-30']],
        'hapus cuti'           => ['DELETE', fn () => '/leave-requests/'.buatCuti(karyawanDari(buatKaryawan()))->id],
        'form edit presensi'   => ['GET', fn () => '/presences/'.buatPresensi(karyawanDari(buatKaryawan()))->id.'/edit'],
    ]);
});

describe('TCU-F-08 Menu navigasi tiap peran', function () {
    $menuUmum = ['/dashboard', '/tasks', '/presences', '/payrolls', '/leave-requests'];
    $menuHR = ['/employees', '/departments', '/roles'];

    test('HR Admin melihat menu umum dan menu manajemen', function () use ($menuUmum, $menuHR) {
        $respons = $this->actingAs(buatHR())->get('/dashboard')->assertOk();

        foreach ([...$menuUmum, ...$menuHR] as $menu) {
            $respons->assertSee('href="'.url($menu).'"', false);
        }
    });

    test('Karyawan hanya melihat menu umum, tanpa menu HR', function () use ($menuUmum, $menuHR) {
        $respons = $this->actingAs(buatKaryawan('Staff'))->get('/dashboard')->assertOk();

        foreach ($menuUmum as $menu) {
            $respons->assertSee('href="'.url($menu).'"', false);
        }
        foreach ($menuHR as $menu) {
            $respons->assertDontSee('href="'.url($menu).'"', false);
        }
    });
});
