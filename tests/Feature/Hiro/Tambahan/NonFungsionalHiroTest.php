<?php

/*
|--------------------------------------------------------------------------
| Test Case Tambahan HIRO — Non-fungsional (TCT-HIR-N-01 s.d. TCT-HIR-N-10)
|--------------------------------------------------------------------------
*/

use App\Mail\AccountInvitation;
use App\Models\Employee;
use App\Models\Presence;
use App\Models\User;
use Dotenv\Dotenv;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

uses()->group('hiro', 'tct-hir', 'tct-hir-non-fungsional');

describe('TCT-HIR-N-01 Log aktivitas untuk audit trail', function () {
    test('tersedia penyimpanan log aktivitas (pengguna, aksi, waktu)', function () {
        $tabelAudit = collect(Schema::getTables())->pluck('name')
            ->filter(fn ($nama) => preg_match('/audit|activity|log/i', $nama))
            ->values();

        expect($tabelAudit)->not->toBeEmpty('Tidak ada tabel audit trail; login/ubah/hapus/absensi tidak tercatat');
    });
});

describe('TCT-HIR-N-02 Backup otomatis harian', function () {
    test('perintah backup terjadwal setiap hari', function () {
        Artisan::call('schedule:list');

        expect(Artisan::output())->toMatch('/backup/i');
    });

    test('hasil backup 7 hari berturut-turut', fn () => null)
        ->skip('MANUAL/INFRA: periksa folder/storage backup di server selama 7 hari (satu file per hari, ukuran wajar).');
});

test('TCT-HIR-N-03 Restore backup ke lingkungan uji', fn () => null)
    ->skip('MANUAL/INFRA: restore dump ke DB uji, bandingkan jumlah baris tiap tabel, catat waktu pemulihan.');

test('TCT-HIR-N-04 Auto-restart service setelah crash', fn () => null)
    ->skip('MANUAL/INFRA: kill proses PHP-FPM/queue worker, pastikan supervisor/systemd menghidupkan ulang <= 2 menit.');

describe('TCT-HIR-N-05 Membuat 200 akun karyawan dalam satu hari', function () {
    test('semua akun terbuat, semua email aktivasi terkirim, performa stabil', function () {
        Mail::fake();
        $this->actingAs(buatHR());
        $durasi = [];

        foreach (range(1, 200) as $i) {
            $mulai = microtime(true);
            $this->post('/employees', dataKaryawanBaru(['email' => "massal$i@hiro.test"]))->assertSessionHasNoErrors();
            $durasi[] = microtime(true) - $mulai;
        }

        expect(Employee::where('email', 'like', 'massal%@hiro.test')->count())->toBe(200)
            ->and(User::where('email', 'like', 'massal%@hiro.test')->count())->toBe(200);
        Mail::assertSentCount(200);
        Mail::assertSent(AccountInvitation::class, fn ($m) => $m->hasTo('massal200@hiro.test'));

        // Stabil: 50 request terakhir tidak jauh lebih lambat dari 50 pertama
        $awal = array_sum(array_slice($durasi, 0, 50)) / 50;
        $akhir = array_sum(array_slice($durasi, -50)) / 50;
        expect($akhir)->toBeLessThan(max($awal * 3, 0.5))
            ->and(persentil($durasi, 95))->toBeLessThanOrEqual(3.0);
    })->group('performa');
});

describe('TCT-HIR-N-06 200 karyawan check-in pada jam masuk', function () {
    // Simulasi beruntun di level aplikasi; konkurensi nyata diuji dengan k6/JMeter
    test('semua tercatat tepat sekali dan p95 <= 3 detik', function () {
        $this->travelTo(now()->setDate(2026, 10, 14)->setTime(7, 58));
        $karyawan = collect(range(1, 200))->map(fn () => buatKaryawan('Staff'));
        $durasi = [];

        foreach ($karyawan as $user) {
            $mulai = microtime(true);
            $this->actingAs($user)->post('/presences', ['latitude' => '-7.2575', 'longitude' => '112.7521']);
            $durasi[] = microtime(true) - $mulai;
        }

        expect(Presence::count())->toBe(200)
            ->and(Presence::distinct()->count('employee_id'))->toBe(200)
            ->and(persentil($durasi, 95))->toBeLessThanOrEqual(3.0);
    })->group('performa');
});

describe('TCT-HIR-N-07 Layanan email mati saat menambah karyawan', function () {
    test('pesan gagal jelas, data tidak rusak, dan bisa dikirim ulang', function () {
        $this->actingAs(buatHR());
        $emailMati = true;
        Event::listen(MessageSending::class, function () use (&$emailMati) {
            if ($emailMati) {
                throw new Symfony\Component\Mailer\Exception\TransportException('Connection could not be established with host "smtp.hiro.test:587"');
            }
        });
        $data = dataKaryawanBaru(['email' => 'email.mati@hiro.test']);

        $this->from('/employees/create')->post('/employees', $data)
            ->assertRedirect('/employees/create')
            ->assertSessionHasErrors('error');

        expect(session('errors')->first('error'))->toContain('Gagal')
            ->and(Employee::where('email', 'email.mati@hiro.test')->exists())->toBeFalse()
            ->and(User::where('email', 'email.mati@hiro.test')->exists())->toBeFalse();

        $emailMati = false;
        $this->post('/employees', $data)->assertSessionHasNoErrors();
        expect(User::where('email', 'email.mati@hiro.test')->count())->toBe(1);
    });
});

test('TCT-HIR-N-08 Absensi dari browser mobile Android 8 dan iOS 13', fn () => null)
    ->skip('MANUAL: uji di perangkat/emulator (izin lokasi diberi & ditolak, GPS dalam ruangan).');

describe('TCT-HIR-N-09 Konfigurasi infrastruktur', function () {
    test('mesin database aplikasi adalah MySQL/MariaDB atau PostgreSQL', function () {
        $berkasEnv = base_path('.env');
        if (! File::exists($berkasEnv)) {
            $this->markTestSkipped('.env tidak ditemukan');
        }

        $env = Dotenv::parse(File::get($berkasEnv));

        expect($env['DB_CONNECTION'] ?? null)->toBeIn(['mysql', 'mariadb', 'pgsql']);
    });

    test('hosting cloud dengan penyimpanan 50 GB', fn () => null)
        ->skip('MANUAL/INFRA: verifikasi kapasitas di panel penyedia cloud.');
});

describe('TCT-HIR-N-10 Arsitektur MVC (tinjauan statis)', function () {
    test('view tidak menjalankan query database langsung', function () {
        $pelanggaran = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($berkas) => preg_match('/DB::|::(where|all|find|query|create)\(/', $berkas->getContents()))
            ->map(fn ($berkas) => $berkas->getRelativePathname())
            ->values();

        expect($pelanggaran)->toBeEmpty();
    });

    test('controller tidak mencetak HTML langsung', function () {
        $pelanggaran = collect(File::allFiles(app_path('Http/Controllers')))
            ->filter(fn ($berkas) => preg_match('/\becho\b|print\s*\(|<html|<div/i', $berkas->getContents()))
            ->map(fn ($berkas) => $berkas->getRelativePathname())
            ->values();

        expect($pelanggaran)->toBeEmpty();
    });

    test('setiap entitas punya model terpisah di app/Models', function () {
        expect(File::isDirectory(app_path('Models')))->toBeTrue()
            ->and(File::isDirectory(resource_path('views')))->toBeTrue();

        foreach (['Employee', 'Department', 'Role', 'Payroll', 'Presence', 'Task', 'LeaveRequest', 'User'] as $model) {
            expect(class_exists("App\\Models\\$model"))->toBeTrue();
        }
    });
});
