<?php

/*
|--------------------------------------------------------------------------
| Test Case Tambahan HIRO — Absensi berbasis GPS (TCT-HIR-F-08 s.d. TCT-HIR-F-14)
|--------------------------------------------------------------------------
*/

use App\Models\Presence;

uses()->group('hiro', 'tct-hir', 'tct-hir-fungsional');

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 14)->setTime(7, 55, 12));
    $this->staff = buatKaryawan('Staff');
    $this->karyawan = karyawanDari($this->staff);
    $this->koordinat = ['latitude' => '-7.2574719', 'longitude' => '112.7520883'];
});

describe('TCT-HIR-F-08 Check-in dengan GPS aktif', function () {
    test('tercatat dengan waktu server, tanggal, latitude, longitude dan tampil di riwayat', function () {
        $this->actingAs($this->staff)
            ->post('/presences', $this->koordinat + ['check_in' => '01:00:00']) // jam dari klien harus diabaikan
            ->assertRedirect(route('presences.index'))
            ->assertSessionHas('success');

        $presensi = Presence::where('employee_id', $this->karyawan->id)->sole();
        expect($presensi->date)->toBe('2026-10-14')
            ->and($presensi->check_in)->toBe('2026-10-14 07:55:12')
            ->and($presensi->latitude)->toBe('-7.2574719')
            ->and($presensi->longitude)->toBe('112.7520883');

        $this->get('/presences')->assertViewHas('presences', fn ($daftar) => $daftar->contains('id', $presensi->id));
    });
});

describe('TCT-HIR-F-09 Check-out setelah check-in', function () {
    test('tercatat pada rekam hari yang sama', function () {
        $this->actingAs($this->staff)->post('/presences', $this->koordinat);
        $this->travel(9)->hours();

        $this->post('/presences/checkout', $this->koordinat)->assertSessionHas('success');

        $presensi = Presence::where('employee_id', $this->karyawan->id)->sole();
        expect($presensi->check_in)->toBe('2026-10-14 07:55:12')
            ->and($presensi->check_out)->toBe('2026-10-14 16:55:12');
    });
});

describe('TCT-HIR-F-10 Izin lokasi ditolak / GPS mati', function () {
    test('absen tanpa koordinat tidak tercatat dan ada pesan jelas', function (array $payload) {
        $this->actingAs($this->staff)->post('/presences', $payload);

        expect(Presence::where('employee_id', $this->karyawan->id)->count())->toBe(0, 'Presensi tercatat tanpa koordinat GPS');
        expect(session('errors') ?? session('error'))->not->toBeNull();
    })->with([
        'tanpa field'     => [[]],
        'field kosong'    => [['latitude' => '', 'longitude' => '']],
        'bukan koordinat' => [['latitude' => 'abc', 'longitude' => 'xyz']],
    ]);
});

describe('TCT-HIR-F-11 Check-in dua kali di hari yang sama', function () {
    test('check-in kedua ditolak dan rekam pertama tetap', function () {
        $this->actingAs($this->staff)->post('/presences', $this->koordinat);
        $pertama = Presence::where('employee_id', $this->karyawan->id)->sole();

        $this->travel(2)->hours();
        $this->post('/presences', ['latitude' => '0', 'longitude' => '0'])
            ->assertSessionHas('error', 'Anda sudah Check In hari ini.');

        expect(Presence::where('employee_id', $this->karyawan->id)->count())->toBe(1)
            ->and($pertama->fresh()->only(['check_in', 'latitude']))->toBe($pertama->only(['check_in', 'latitude']));
    });
});

describe('TCT-HIR-F-12 Check-out tanpa check-in', function () {
    test('ditolak dengan pesan', function () {
        $this->actingAs($this->staff)
            ->post('/presences/checkout', $this->koordinat)
            ->assertSessionHas('error', 'Data Check In hari ini tidak ditemukan.');

        expect(Presence::count())->toBe(0);
    });
});

test('TCT-HIR-F-13 Check-in di luar area kerja', fn () => null)
    ->skip('BLOCKED: titik kantor dan radius belum ditentukan di SKPL; isi aturan lalu tulis assertion ditolak/ditandai.');

describe('TCT-HIR-F-14 Riwayat sendiri vs rekap semua', function () {
    test('karyawan hanya melihat miliknya, HR Admin melihat semua', function () {
        $lain = karyawanDari(buatKaryawan('Staff'));
        buatPresensi($this->karyawan, ['date' => '2026-10-13']);
        buatPresensi($lain, ['date' => '2026-10-13']);
        buatPresensi($lain, ['date' => '2026-10-12']);

        $this->actingAs($this->staff)->get('/presences')
            ->assertViewHas('presences', fn ($d) => $d->count() === 1 && $d->every(fn ($p) => $p->employee_id === $this->karyawan->id))
            ->assertDontSee($lain->fullname);

        $this->actingAs(buatHR())->get('/presences')
            ->assertViewHas('presences', fn ($d) => $d->count() === Presence::count())
            ->assertSee($lain->fullname)
            ->assertSee($this->karyawan->fullname);
    });
});
