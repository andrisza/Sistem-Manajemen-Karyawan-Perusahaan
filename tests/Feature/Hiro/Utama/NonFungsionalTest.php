<?php

/*
|--------------------------------------------------------------------------
| Test Case Utama — Kebutuhan non-fungsional (TCU-N-01 s.d. TCU-N-17)
|--------------------------------------------------------------------------
| Skenario yang butuh browser sungguhan, perangkat, jaringan, atau infrastruktur
| ditandai skip dengan alasan MANUAL / ALAT LAIN agar tetap tercatat di laporan.
*/

use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses()->group('hiro', 'tcu', 'tcu-non-fungsional');

/* ---------------------------------- Keamanan ---------------------------------- */

describe('TCU-N-01 Enkripsi password (bcrypt/argon2 dengan salt)', function () {
    test('dua akun dengan password sama menyimpan hash berbeda', function () {
        $a = buatKaryawan();
        $b = buatKaryawan();

        $hashA = DB::table('users')->where('id', $a->id)->value('password');
        $hashB = DB::table('users')->where('id', $b->id)->value('password');

        expect($hashA)->not->toBe(PASSWORD_UJI)
            ->and($hashA)->toMatch('/^\$(2y|argon2id|argon2i)\$/')
            ->and($hashB)->toMatch('/^\$(2y|argon2id|argon2i)\$/')
            ->and($hashA)->not->toBe($hashB)
            ->and(Hash::check(PASSWORD_UJI, $hashA))->toBeTrue()
            ->and(Hash::check(PASSWORD_UJI, $hashB))->toBeTrue();
    });
});

describe('TCU-N-02 HTTPS dan atribut cookie sesi', function () {
    test('cookie sesi ber-atribut HttpOnly', function () {
        $user = buatKaryawan();

        $cookie = $this->post('/login', ['email' => $user->email, 'password' => PASSWORD_UJI])
            ->getCookie(config('session.cookie'), false);

        expect($cookie)->not->toBeNull()
            ->and($cookie->isHttpOnly())->toBeTrue();
    });

    test('redirect http ke https, sertifikat valid, cookie Secure', fn () => null)
        ->skip('MANUAL/STAGING: diatur web server & .env produksi (SESSION_SECURE_COOKIE=true). Uji dengan curl -I http://domain dan cek sertifikat.');
});

describe('TCU-N-03 SQL injection', function () {
    test('payload di form login tidak melewati autentikasi', function (string $email, string $password) {
        buatKaryawan('HR', ['email' => 'admin@hiro.test']);

        $respons = $this->post('/login', compact('email', 'password'));

        $this->assertGuest();
        expect($respons->status())->toBe(302)
            ->and($respons->getContent())->not->toContain('SQLSTATE');
    })->with([
        'di email'    => ["' OR '1'='1", 'apa saja'],
        'di email 2'  => ["admin@hiro.test' --", 'apa saja'],
        'di password' => ['admin@hiro.test', "' OR '1'='1"],
    ]);

    test('payload di form data disimpan sebagai teks biasa tanpa merusak data', function () {
        $this->actingAs(buatHR());
        $payload = "Keuangan'; DROP TABLE employees; --";

        $this->post('/departments', ['name' => $payload, 'status' => 'active'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('departments', ['name' => $payload]);
        expect(Employee::count())->toBe(1);
        $this->get('/departments')->assertOk();
    });

    test('payload di parameter URL tidak menimbulkan error SQL', function (string $url) {
        config(['app.debug' => false]);
        $this->actingAs(buatHR());

        $respons = $this->get($url);

        expect($respons->status())->toBeLessThan(500)
            ->and($respons->getContent())->not->toContain('SQLSTATE');
    })->with([
        "/employees/1' OR '1'='1",
        '/payroll/1 OR 1=1/download',
        "/leave-requests/confirm/1'",
    ]);
});

describe('TCU-N-04 Proteksi XSS', function () {
    $xss = '<script>alert(1)</script>';

    // Parameter bertipe Closure tidak di-resolve Pest, sehingga dijalankan setelah login HR
    test('payload tampil sebagai teks, bukan dieksekusi', function (Closure $simpan, Closure $halaman) use ($xss) {
        $this->actingAs(buatHR());
        $simpan($xss);

        foreach ($halaman() as $tujuan) {
            $this->get($tujuan)->assertOk()
                ->assertDontSee($xss, false)
                ->assertSee(e($xss), false);
        }
    })->with([
        'nama departemen' => [fn ($x) => Department::create(['name' => $x, 'status' => 'active']), fn () => ['/departments']],
        'nama jabatan'    => [fn ($x) => Role::create(['title' => $x]), fn () => ['/roles']],
        'judul tugas'     => [fn ($x) => buatTugas(Employee::first(), ['title' => $x, 'description' => $x]), fn () => ['/tasks', '/tasks/'.Task::first()->id, '/dashboard']],
        'nama karyawan'   => [fn ($x) => buatKaryawan('Staff', ['fullname' => $x]), fn () => ['/employees', '/employees/'.Employee::latest('id')->first()->id]],
    ]);
});

describe('TCU-N-05 Token sesi lama setelah logout', function () {
    test('cookie sesi lama ditolak setelah logout', function () {
        config(['session.driver' => 'database']);
        $user = buatKaryawan();

        $login = $this->post('/login', ['email' => $user->email, 'password' => PASSWORD_UJI]);
        $cookieLama = $login->getCookie(config('session.cookie'), false)->getValue();

        // Kontrol: cookie tersebut memang memberi akses sebelum logout
        Auth::forgetGuards();
        $this->withUnencryptedCookie(config('session.cookie'), $cookieLama)->get('/dashboard')->assertOk();

        $this->withUnencryptedCookie(config('session.cookie'), $cookieLama)->post('/logout');

        // Kirim ulang request memakai token/cookie lama
        Auth::forgetGuards();
        $this->withUnencryptedCookie(config('session.cookie'), $cookieLama)
            ->get('/dashboard')
            ->assertRedirect('/login');
    });
});

/* -------------------------------- Kapabilitas --------------------------------- */

describe('TCU-N-06 Uji volume', function () {
    test('daftar, dashboard, dan grafik tetap benar dan termuat <= 3 detik', function (string $url) {
        $hr = buatHR();
        $jumlahKaryawan = 200;
        $hariKerja = 22;

        $deptId = Department::first()->id;
        $roleId = Role::firstOrCreate(['title' => 'Staff'])->id;
        $karyawan = collect(range(1, $jumlahKaryawan))->map(fn ($i) => [
            'fullname' => "Karyawan $i", 'email' => "k$i@volume.test", 'phone_number' => '0812', 'address' => 'Alamat',
            'birth_date' => '1990-01-01', 'hire_date' => '2024-01-01', 'department_id' => $deptId, 'role_id' => $roleId,
            'status' => 'active', 'salary' => 5000000, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $karyawan->chunk(100)->each(fn ($c) => DB::table('employees')->insert($c->all()));
        $ids = Employee::where('email', 'like', '%@volume.test')->pluck('id');

        $presensi = $ids->flatMap(fn ($id) => collect(range(1, $hariKerja))->map(fn ($d) => [
            'employee_id' => $id, 'date' => sprintf('2026-03-%02d', $d), 'check_in' => sprintf('2026-03-%02d 08:00:00', $d),
            'check_out' => sprintf('2026-03-%02d 17:00:00', $d), 'status' => 'present', 'created_at' => now(), 'updated_at' => now(),
        ]));
        $presensi->chunk(500)->each(fn ($c) => DB::table('presences')->insert($c->all()));
        $ids->chunk(100)->each(fn ($c) => DB::table('payroll')->insert($c->map(fn ($id) => [
            'employee_id' => $id, 'salary' => 5000000, 'bonuses' => 0, 'deductions' => 0, 'net_salary' => 5000000,
            'pay_date' => '2026-03-25', 'created_at' => now(), 'updated_at' => now(),
        ])->all()));

        $mulai = microtime(true);
        $respons = $this->actingAs($hr)->get($url);
        $durasi = microtime(true) - $mulai;

        $respons->assertOk();
        expect($durasi)->toBeLessThanOrEqual(3.0, sprintf('%s termuat %.2f detik', $url, $durasi));
    })->with(['/employees', '/presences', '/payrolls', '/dashboard', '/dashboard/presences'])->group('performa');
});

test('TCU-N-07 Alur inti pada spesifikasi perangkat minimum', fn () => null)
    ->skip('MANUAL: jalankan alur inti di perangkat spesifikasi minimum sesuai SKPL dan catat waktu respons.');

/* -------------------------------- Kompatibilitas ------------------------------- */

test('TCU-N-08 Alur inti di Chrome, Firefox, dan Edge', fn () => null)
    ->skip('ALAT LAIN: butuh browser testing (mis. pestphp/pest-plugin-browser + Playwright) atau uji manual.');

test('TCU-N-09 Alur inti di Windows, macOS, Linux, Android 8, iOS 13', fn () => null)
    ->skip('MANUAL: uji di perangkat/emulator nyata (BrowserStack atau perangkat fisik).');

test('TCU-N-10 UI responsif pada viewport 360/768/1366/1920 px', fn () => null)
    ->skip('ALAT LAIN: butuh browser testing untuk mengukur layout per viewport.');

test('TCU-N-11 Bandwidth 1 Mbps', fn () => null)
    ->skip('MANUAL: gunakan network throttling DevTools (custom 1 Mbps) dan ukur waktu muat halaman inti.');

/* --------------------------------- Reliabilitas -------------------------------- */

describe('TCU-N-12 Penanganan error tanpa detail teknis', function () {
    beforeEach(fn () => config(['app.debug' => false])); // simulasi mode produksi

    $pastikanTanpaDetailInternal = function ($respons) {
        expect($respons->getContent())
            ->not->toContain('SQLSTATE')
            ->not->toContain('Stack trace')
            ->not->toContain(base_path())
            ->not->toContain('vendor'.DIRECTORY_SEPARATOR.'laravel');
    };

    test('404 untuk halaman dan data yang tidak ada', function (string $url) use ($pastikanTanpaDetailInternal) {
        $respons = $this->actingAs(buatHR())->get($url)->assertNotFound();
        $pastikanTanpaDetailInternal($respons);
    })->with(['/halaman-tidak-ada', '/employees/99999', '/payroll/99999/download', '/payrolls/99999']);

    test('URL detail yang tidak disediakan tidak menimbulkan error server', function () use ($pastikanTanpaDetailInternal) {
        $staff = buatKaryawan();
        $presensi = buatPresensi(karyawanDari($staff));

        // PresenceController tidak punya method show(); route-nya tidak boleh berujung 500
        $respons = $this->actingAs($staff)->get("/presences/{$presensi->id}");

        expect($respons->status())->toBeIn([404, 405]);
        $pastikanTanpaDetailInternal($respons);
    });

    test('database tidak terjangkau ditangani dengan pesan informatif', function () use ($pastikanTanpaDetailInternal) {
        $hr = buatHR();
        $koneksiAsli = DB::getDefaultConnection();

        // Arahkan aplikasi ke database yang tidak ada, lalu kembalikan agar rollback test tetap jalan
        config(['database.connections.mati' => ['driver' => 'sqlite', 'database' => storage_path('tidak-ada/hiro.sqlite'), 'prefix' => '']]);
        DB::setDefaultConnection('mati');
        try {
            $respons = $this->actingAs($hr)->get('/dashboard');
        } finally {
            DB::setDefaultConnection($koneksiAsli);
        }

        expect($respons->status())->toBe(500);
        $pastikanTanpaDetailInternal($respons);
    });

    test('error database saat simpan tidak dibocorkan ke pengguna', function () {
        Mail::fake();
        $this->actingAs(buatHR());

        // department_id tidak ada -> pelanggaran foreign key di database
        $this->from('/employees/create')->post('/employees', dataKaryawanBaru(['department_id' => 99999]));

        $pesan = collect(session('errors')?->all() ?? [])->implode(' ');
        expect($pesan)->not->toBeEmpty()
            ->not->toContain('SQLSTATE')
            ->not->toContain('constraint');
    });
});

describe('TCU-N-13 Koneksi terputus saat submit', function () {
    test('tidak ada data setengah tersimpan dan aksi dapat diulang', function () {
        $this->actingAs(buatHR());
        $putus = true;
        Event::listen(MessageSending::class, function () use (&$putus) {
            if ($putus) {
                throw new RuntimeException('Koneksi terputus');
            }
        });
        $data = dataKaryawanBaru(['email' => 'putus@hiro.test']);

        $this->post('/employees', $data)->assertSessionHasErrors();
        expect(Employee::where('email', 'putus@hiro.test')->exists())->toBeFalse()
            ->and(User::where('email', 'putus@hiro.test')->exists())->toBeFalse();

        $putus = false;
        $this->post('/employees', $data)->assertSessionHasNoErrors();
        expect(Employee::where('email', 'putus@hiro.test')->count())->toBe(1)
            ->and(User::where('email', 'putus@hiro.test')->count())->toBe(1);
    });
});

test('TCU-N-14 Ketersediaan 30 hari', fn () => null)
    ->skip('MONITORING: pantau dengan uptime monitor (UptimeRobot/Better Stack) selama 30 hari terhadap endpoint /up.');

/* -------------------------------- Skalabilitas -------------------------------- */

test('TCU-N-15 Load test 200 pengguna bersamaan', fn () => null)
    ->skip('ALAT LAIN: gunakan k6/JMeter dengan 200 virtual user ke /login dan alur inti; target p95 <= 3 detik, error < 1%.');

describe('TCU-N-16 Dua pengguna mengubah/menghapus data yang sama', function () {
    test('ubah payroll yang sudah dihapus pengguna lain ditolak tanpa korupsi', function () {
        $hrA = buatHR();
        $hrB = buatHR();
        $payroll = buatPayroll(karyawanDari(buatKaryawan()));

        $this->actingAs($hrA)->delete("/payrolls/{$payroll->id}");
        $respons = $this->actingAs($hrB)->put("/payrolls/{$payroll->id}", [
            'employee_id' => $payroll->employee_id, 'salary' => 1, 'bonuses' => 0, 'deductions' => 0, 'pay_date' => '2026-10-01',
        ]);

        $respons->assertNotFound();
        expect(Payroll::count())->toBe(0)
            ->and(Payroll::withTrashed()->find($payroll->id)->salary)->toEqual($payroll->salary);
    });

    test('dua HR mengubah karyawan yang sama: data karyawan dan akun tetap sinkron', function () {
        Mail::fake();
        $employee = karyawanDari(buatKaryawan());

        $this->actingAs(buatHR())->put("/employees/{$employee->id}", dataKaryawanBaru(['fullname' => 'Versi A', 'email' => 'a@hiro.test']));
        $this->actingAs(buatHR())->put("/employees/{$employee->id}", dataKaryawanBaru(['fullname' => 'Versi B', 'email' => 'b@hiro.test']));

        $employee->refresh();
        $akun = User::where('employee_id', $employee->id)->first();
        expect($employee->fullname)->toBe('Versi B')
            ->and($akun->email)->toBe($employee->email)
            ->and($akun->name)->toBe($employee->fullname);
    });

    test('ubah karyawan yang sudah dihapus tidak memunculkan error tak tertangani', function () {
        Mail::fake();
        $employee = karyawanDari(buatKaryawan());
        $this->actingAs(buatHR())->delete("/employees/{$employee->id}");

        $respons = $this->actingAs(buatHR())->put("/employees/{$employee->id}", dataKaryawanBaru());

        expect($respons->status())->toBeLessThan(500);
    });
});

/* ---------------------------------- Antarmuka --------------------------------- */

test('TCU-N-17 Kesesuaian semua halaman dengan spesifikasi UI', fn () => null)
    ->skip('MANUAL: bandingkan tiap halaman dengan mockup/spesifikasi UI pada SKPL.');
