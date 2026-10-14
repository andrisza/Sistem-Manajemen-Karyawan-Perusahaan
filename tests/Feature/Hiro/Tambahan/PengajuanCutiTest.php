<?php

/*
|--------------------------------------------------------------------------
| Test Case Tambahan HIRO — Pengajuan cuti (TCT-HIR-F-15 s.d. TCT-HIR-F-18)
|--------------------------------------------------------------------------
*/

use App\Models\LeaveRequest;

uses()->group('hiro', 'tct-hir', 'tct-hir-fungsional');

beforeEach(function () {
    $this->staff = buatKaryawan('Staff');
    $this->karyawan = karyawanDari($this->staff);
    $this->hr = buatHR();
});

describe('TCT-HIR-F-15 Karyawan mengajukan cuti valid', function () {
    test('tersimpan berstatus menunggu dan muncul di daftar HR Admin', function () {
        $this->actingAs($this->staff)->post('/leave-requests', [
            'leave_type' => 'Vacation', 'start_date' => '2026-11-02', 'end_date' => '2026-11-04',
        ])->assertRedirect(route('leave-requests.index'))->assertSessionHas('success');

        $cuti = LeaveRequest::sole();
        expect($cuti->only(['employee_id', 'leave_type', 'status']))
            ->toBe(['employee_id' => $this->karyawan->id, 'leave_type' => 'Vacation', 'status' => 'pending']);

        $this->actingAs($this->hr)->get('/leave-requests')
            ->assertViewHas('leaveRequests', fn ($d) => $d->contains('id', $cuti->id));
    });

    test('karyawan tidak bisa mengajukan cuti atas nama orang lain', function () {
        $lain = karyawanDari(buatKaryawan());

        $this->actingAs($this->staff)->post('/leave-requests', [
            'employee_id' => $lain->id, 'leave_type' => 'Vacation', 'start_date' => '2026-11-02', 'end_date' => '2026-11-04',
        ]);

        expect(LeaveRequest::where('employee_id', $lain->id)->exists())->toBeFalse();
    });
});

describe('TCT-HIR-F-16 Pengajuan cuti tidak valid', function () {
    test('ditolak dengan pesan validasi', function (array $payload, string $field) {
        $respons = $this->actingAs($this->staff)->from('/leave-requests/create')->post('/leave-requests', $payload);

        expect($respons->status())->toBeLessThan(500);
        $respons->assertSessionHasErrors($field);
        expect(LeaveRequest::count())->toBe(0);
    })->with([
        'selesai sebelum mulai' => [['leave_type' => 'Vacation', 'start_date' => '2026-11-10', 'end_date' => '2026-11-05'], 'end_date'],
        'tanggal mulai kosong'  => [['leave_type' => 'Vacation', 'start_date' => '', 'end_date' => '2026-11-05'], 'start_date'],
        'tanggal selesai kosong' => [['leave_type' => 'Vacation', 'start_date' => '2026-11-05', 'end_date' => ''], 'end_date'],
        'jenis kosong'          => [['leave_type' => '', 'start_date' => '2026-11-02', 'end_date' => '2026-11-04'], 'leave_type'],
        'bukan tanggal'         => [['leave_type' => 'Vacation', 'start_date' => 'besok', 'end_date' => 'lusa'], 'start_date'],
    ]);

    test('HR Admin juga ditolak bila tanggal selesai sebelum mulai', function () {
        $this->actingAs($this->hr)->post('/leave-requests', [
            'employee_id' => $this->karyawan->id, 'leave_type' => 'Vacation', 'start_date' => '2026-11-10', 'end_date' => '2026-11-05',
        ])->assertSessionHasErrors('end_date');

        expect(LeaveRequest::count())->toBe(0);
    });
});

describe('TCT-HIR-F-17 dan F-18 HR Admin memproses cuti', function () {
    test('status berubah dan terlihat oleh karyawan pemohon', function (string $aksi, string $statusAkhir) {
        $cuti = buatCuti($this->karyawan);

        $this->actingAs($this->hr)->get("/leave-requests/$aksi/{$cuti->id}")
            ->assertRedirect(route('leave-requests.index'))
            ->assertSessionHas('success');

        expect($cuti->fresh()->status)->toBe($statusAkhir);
        $this->actingAs($this->staff)->get('/leave-requests')
            ->assertViewHas('leaveRequests', fn ($d) => $d->firstWhere('id', $cuti->id)?->status === $statusAkhir);
    })->with([
        'TCT-HIR-F-17 disetujui' => ['confirm', 'confirm'],
        'TCT-HIR-F-18 ditolak'   => ['reject', 'reject'],
    ]);

    test('pengajuan yang sudah diputuskan tidak bisa diproses ulang', function () {
        $cuti = buatCuti($this->karyawan, ['status' => 'reject']);

        $this->actingAs($this->hr)->get("/leave-requests/confirm/{$cuti->id}");

        expect($cuti->fresh()->status)->toBe('reject');
    });
});
