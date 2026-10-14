<?php

/*
|--------------------------------------------------------------------------
| Test Case Tambahan HIRO — Penggajian dan slip gaji ber-QR Code (TCT-HIR-F-19 s.d. TCT-HIR-F-26)
|--------------------------------------------------------------------------
| Rumus: gaji bersih = gaji + bonus - potongan
*/

use App\Models\Payroll;
use Illuminate\Support\Facades\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

uses()->group('hiro', 'tct-hir', 'tct-hir-fungsional');

beforeEach(function () {
    $this->hr = buatHR();
    $this->staff = buatKaryawan('Staff', ['fullname' => 'Budi Santoso']);
    $this->karyawan = karyawanDari($this->staff);
});

function payloadPayroll(int $employeeId, array $override = []): array
{
    return array_merge([
        'employee_id' => $employeeId, 'salary' => 5000000, 'bonuses' => 500000, 'deductions' => 250000, 'pay_date' => '2026-10-25',
    ], $override);
}

function urlVerifikasi(Payroll $payroll): string
{
    return route('payroll.verify', ['id' => $payroll->id, 'hash' => md5($payroll->created_at.$payroll->id)]);
}

// Menangkap data yang dikirim controller ke view slip PDF
function tangkapDataSlip($test, $user, Payroll $payroll): array
{
    $data = [];
    View::composer('pages.payroll.pdf_slip', function ($view) use (&$data) {
        $data = $view->getData();
    });
    $test->actingAs($user)->get("/payroll/{$payroll->id}/download")->assertOk();

    return $data;
}

describe('TCT-HIR-F-19 HR Admin menambah data penggajian', function () {
    test('tersimpan dan gaji bersih otomatis sesuai rumus', function () {
        $this->actingAs($this->hr)->post('/payrolls', payloadPayroll($this->karyawan->id))
            ->assertRedirect(route('payrolls.index'));

        expect((float) Payroll::sole()->net_salary)->toBe(5250000.0);
    });

    test('gaji bersih dihitung server walau klien mengirim nilai lain', function () {
        $this->actingAs($this->hr)->post('/payrolls', payloadPayroll($this->karyawan->id, ['net_salary' => 99999999]));

        expect((float) Payroll::sole()->net_salary)->toBe(5250000.0);
    });
});

describe('TCT-HIR-F-20 Nilai batas perhitungan gaji', function () {
    test('nilai batas valid dihitung benar', function (array $override, float $bersih) {
        $this->actingAs($this->hr)->post('/payrolls', payloadPayroll($this->karyawan->id, $override))->assertSessionHasNoErrors();

        expect((float) Payroll::sole()->net_salary)->toBe($bersih);
    })->with([
        'bonus 0'          => [['bonuses' => 0], 4750000.0],
        'potongan 0'       => [['deductions' => 0], 5500000.0],
        'bonus & potongan 0' => [['bonuses' => 0, 'deductions' => 0], 5000000.0],
        'desimal'          => [['salary' => 5000000.50, 'bonuses' => 0.25, 'deductions' => 0.75], 5000000.0],
    ]);

    test('nilai tidak valid ditolak', function (array $override, string $field) {
        $this->actingAs($this->hr)->from('/payrolls/create')
            ->post('/payrolls', payloadPayroll($this->karyawan->id, $override))
            ->assertSessionHasErrors($field);

        expect(Payroll::count())->toBe(0);
    })->with([
        'potongan melebihi gaji + bonus' => [['deductions' => 6000000], 'deductions'],
        'gaji negatif'                   => [['salary' => -5000000], 'salary'],
        'bonus negatif'                  => [['bonuses' => -1], 'bonuses'],
        'potongan negatif'               => [['deductions' => -100000], 'deductions'],
        'gaji bukan angka'               => [['salary' => 'lima juta'], 'salary'],
        'bonus bukan angka'              => [['bonuses' => 'NaN'], 'bonuses'],
        'karyawan tidak ada'             => [['employee_id' => 99999], 'employee_id'],
    ]);
});

describe('TCT-HIR-F-21 HR Admin mengubah data penggajian', function () {
    test('gaji bersih dihitung ulang dan slip memakai nilai baru', function () {
        $payroll = buatPayroll($this->karyawan);

        $this->actingAs($this->hr)->put("/payrolls/{$payroll->id}", payloadPayroll($this->karyawan->id, [
            'salary' => 7000000, 'bonuses' => 1000000, 'deductions' => 500000,
        ]))->assertSessionHasNoErrors();

        expect((float) $payroll->fresh()->net_salary)->toBe(7500000.0);

        $slip = tangkapDataSlip($this, $this->staff, $payroll);
        expect((float) $slip['payroll']->net_salary)->toBe(7500000.0)
            ->and(view('pages.payroll.pdf_slip', $slip)->render())->toContain('Rp 7.500.000');
    });
});

describe('TCT-HIR-F-22 HR Admin menghapus data penggajian', function () {
    test('setelah konfirmasi data hilang dan slip tidak bisa diunduh', function () {
        $payroll = buatPayroll($this->karyawan);

        $this->actingAs($this->hr)->delete("/payrolls/{$payroll->id}")->assertSessionHas('success');

        $this->assertSoftDeleted($payroll);
        $this->get('/payrolls')->assertViewHas('payrolls', fn ($d) => $d->isEmpty());
        $this->actingAs($this->staff)->get("/payroll/{$payroll->id}/download")->assertNotFound();
    });

    test('halaman daftar payroll meminta konfirmasi sebelum hapus', function () {
        buatPayroll($this->karyawan);

        expect($this->actingAs($this->hr)->get('/payrolls')->getContent())->toMatch('/confirm\(|Swal\.fire|onsubmit/');
    });
});

describe('TCT-HIR-F-23 Karyawan membuka riwayat gaji dan mengunduh slip PDF', function () {
    test('hanya melihat data miliknya', function () {
        $milikSendiri = buatPayroll($this->karyawan);
        buatPayroll(karyawanDari(buatKaryawan('Staff', ['fullname' => 'Orang Lain'])));

        $this->actingAs($this->staff)->get('/payrolls')
            ->assertViewHas('payrolls', fn ($d) => $d->pluck('id')->all() === [$milikSendiri->id])
            ->assertDontSee('Orang Lain');
    });

    test('PDF valid memuat nama, periode, gaji, bonus, potongan, gaji bersih, dan QR sesuai database', function () {
        $payroll = buatPayroll($this->karyawan, ['salary' => 5000000, 'bonuses' => 500000, 'deductions' => 250000, 'pay_date' => '2026-10-25']);

        $respons = $this->actingAs($this->staff)->get("/payroll/{$payroll->id}/download");
        $respons->assertOk()->assertHeader('content-type', 'application/pdf');
        expect(substr($respons->getContent(), 0, 4))->toBe('%PDF');

        $slip = tangkapDataSlip($this, $this->staff, $payroll);
        expect(view('pages.payroll.pdf_slip', $slip)->render())
            ->toContain('Budi Santoso')
            ->toContain('25 October 2026')
            ->toContain('Rp 5.000.000')
            ->toContain('Rp 500.000')
            ->toContain('Rp 250.000')
            ->toContain('Rp 5.250.000');
        expect($slip['qrcode'])->toBe(base64_encode(QrCode::format('svg')->size(100)->generate(urlVerifikasi($payroll))));
    });

    test('tidak bisa mengunduh slip karyawan lain', function () {
        $milikLain = buatPayroll(karyawanDari(buatKaryawan()));

        expect($this->actingAs($this->staff)->get("/payroll/{$milikLain->id}/download")->status())->toBeIn([403, 404]);
    });
});

describe('TCT-HIR-F-24 Pindai QR pada slip asli', function () {
    test('halaman verifikasi menyatakan valid dengan data sesuai database', function () {
        $payroll = buatPayroll($this->karyawan, ['pay_date' => '2026-10-25']);

        $this->actingAs($this->staff)->get(urlVerifikasi($payroll))
            ->assertOk()
            ->assertSee('DOKUMEN VALID')
            ->assertSee('Budi Santoso')
            ->assertSee('25 October 2026')
            ->assertSee('Rp 5.250.000');
    });
});

describe('TCT-HIR-F-25 QR slip dimodifikasi atau slip sudah dihapus', function () {
    test('dinyatakan tidak valid tanpa membocorkan data gaji', function (Closure $buatUrl) {
        $payroll = buatPayroll($this->karyawan);
        $lain = buatPayroll(karyawanDari(buatKaryawan()));

        $respons = $this->actingAs($this->hr)->get($buatUrl($payroll, $lain));

        expect($respons->status())->toBeIn([403, 404])
            ->and($respons->getContent())->not->toContain('DOKUMEN VALID')
            ->and($respons->getContent())->not->toContain('5.250.000');
    })->with([
        'hash diubah'         => [fn ($p) => substr(urlVerifikasi($p), 0, -1).'0'],
        'hash acak'           => [fn ($p) => route('payroll.verify', ['id' => $p->id, 'hash' => md5('palsu')])],
        'ID ditukar'          => [fn ($p, $lain) => route('payroll.verify', ['id' => $lain->id, 'hash' => md5($p->created_at.$p->id)])],
        'slip sudah dihapus'  => [function ($p) { $url = urlVerifikasi($p); $p->delete(); return $url; }],
    ]);

    test('hash QR tidak bisa ditebak dari data publik slip', function () {
        $payroll = buatPayroll($this->karyawan);

        // Hash md5(created_at.id) dapat dihitung siapa pun yang tahu ID & waktu cetak (tercetak di halaman verifikasi)
        $hashTebakan = md5($payroll->created_at->format('Y-m-d H:i:s').$payroll->id);

        // Gagal = hash QR dapat dipalsukan karena tidak memakai secret/HMAC
        expect(urlVerifikasi($payroll))->not->toContain($hashTebakan);
    });
});

describe('TCT-HIR-F-26 Buka tautan verifikasi QR tanpa login', function () {
    test('diarahkan ke login, atau jika publik hanya menampilkan data minimum', function () {
        $payroll = buatPayroll($this->karyawan, ['salary' => 5000000, 'bonuses' => 500000, 'deductions' => 250000]);

        $respons = $this->get(urlVerifikasi($payroll));

        if ($respons->isRedirect()) {
            $respons->assertRedirect('/login');
        } else {
            $respons->assertOk()
                ->assertDontSee('Rp 5.000.000')
                ->assertDontSee('Rp 500.000')
                ->assertDontSee('Rp 250.000');
        }
    });
});
