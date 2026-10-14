<?php

/*
|--------------------------------------------------------------------------
| Test Case Utama — Dashboard dan pelaporan (TCU-F-16 s.d. TCU-F-19)
|--------------------------------------------------------------------------
*/

use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Presence;
use Illuminate\Support\Facades\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

uses()->group('hiro', 'tcu', 'tcu-fungsional');

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 6, 15)->setTime(9, 0));
});

describe('TCU-F-16 Angka dan grafik dashboard sesuai database', function () {
    beforeEach(function () {
        $this->hr = buatHR();
        $this->staff = buatKaryawan('Staff');
        $budi = karyawanDari($this->staff);
        $ani = karyawanDari(buatKaryawan('Staff'));
        Department::create(['name' => 'Keuangan', 'status' => 'active']);

        buatPayroll($budi);
        buatPayroll($budi, ['pay_date' => '2026-05-25']);
        buatPayroll($ani);

        buatPresensi($budi, ['date' => '2026-01-10']);
        buatPresensi($budi, ['date' => '2026-03-02']);
        buatPresensi($ani, ['date' => '2026-03-03']);
        buatPresensi($ani, ['date' => '2026-06-01']);
        buatPresensi($ani, ['date' => '2026-06-02', 'status' => 'absent']);
        buatPresensi($budi, ['date' => '2025-12-30']); // tahun lalu, tidak ikut grafik
    });

    test('kartu statistik HR Admin sama dengan hitungan database', function () {
        $this->actingAs($this->hr)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('employee', Employee::count())
            ->assertViewHas('department', Department::count())
            ->assertViewHas('payroll', Payroll::count())
            ->assertViewHas('presence', Presence::count());
    });

    test('grafik kehadiran HR Admin (per bulan, tahun berjalan) sama dengan query', function () {
        $harapan = array_fill(0, 12, 0);
        Presence::where('status', 'present')->whereYear('date', 2026)->get()
            ->each(function ($p) use (&$harapan) {
                $harapan[(int) date('n', strtotime($p->date)) - 1]++;
            });

        $this->actingAs($this->hr)->getJson('/dashboard/presences')
            ->assertOk()
            ->assertExactJson($harapan);

        expect($harapan)->toBe([1, 0, 2, 0, 0, 1, 0, 0, 0, 0, 0, 0]);
    });

    test('dashboard karyawan hanya menghitung data miliknya', function () {
        $budi = karyawanDari($this->staff);

        $this->actingAs($this->staff)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('payroll', Payroll::where('employee_id', $budi->id)->count())
            ->assertViewHas('presence', Presence::where('employee_id', $budi->id)->where('status', 'present')->count());

        $this->actingAs($this->staff)->getJson('/dashboard/presences')
            ->assertExactJson([1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0]);
    });
});

describe('TCU-F-17 Dashboard dan laporan saat data kosong', function () {
    test('HR Admin melihat nol tanpa error, NaN, atau null', function () {
        $this->actingAs(buatHR());

        $this->get('/dashboard')->assertOk()
            ->assertViewHas('payroll', 0)
            ->assertViewHas('presence', 0)
            ->assertDontSee('NaN');

        $this->getJson('/dashboard/presences')->assertExactJson(array_fill(0, 12, 0));
    });

    test('halaman daftar tetap tampil saat kosong', function (string $url) {
        $this->actingAs(buatKaryawan('Staff'))->get($url)->assertOk()->assertDontSee('NaN');
    })->with(['/dashboard', '/payrolls', '/presences', '/tasks', '/leave-requests']);
});

describe('TCU-F-18 Filter periode laporan', function () {
    test('rentang normal, batas, terbalik, dan kosong', fn () => null)
        ->skip('BLOCKED: HIRO belum menyediakan filter periode pada laporan/daftar (payroll, presensi, cuti).');
});

describe('TCU-F-19 Cetak atau unduh keluaran (slip gaji PDF)', function () {
    test('file PDF terbentuk dan bisa diunduh', function () {
        $staff = buatKaryawan('Staff', ['fullname' => 'Budi Santoso']);
        $payroll = buatPayroll(karyawanDari($staff));

        $respons = $this->actingAs($staff)->get("/payroll/{$payroll->id}/download");

        $respons->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('Slip-Gaji-Budi-Santoso.pdf');
        expect(substr($respons->getContent(), 0, 4))->toBe('%PDF');
    });

    test('isi PDF sama dengan data payroll dan memuat QR verifikasi', function () {
        $staff = buatKaryawan('Staff', ['fullname' => 'Budi Santoso']);
        $payroll = buatPayroll(karyawanDari($staff), ['salary' => 5000000, 'bonuses' => 750000, 'deductions' => 125000]);

        $dataSlip = null;
        View::composer('pages.payroll.pdf_slip', function ($view) use (&$dataSlip) {
            $dataSlip = $view->getData();
        });

        $this->actingAs($staff)->get("/payroll/{$payroll->id}/download")->assertOk();

        $html = view('pages.payroll.pdf_slip', $dataSlip)->render();
        expect($html)
            ->toContain('Budi Santoso')
            ->toContain('Rp 5.000.000')
            ->toContain('Rp 750.000')
            ->toContain('Rp 125.000')
            ->toContain('Rp 5.625.000')
            ->toContain('data:image/svg+xml;base64,');

        $urlVerifikasi = route('payroll.verify', ['id' => $payroll->id, 'hash' => md5($payroll->created_at.$payroll->id)]);
        expect($dataSlip['qrcode'])->toBe(base64_encode(QrCode::format('svg')->size(100)->generate($urlVerifikasi)));
    });
});
