<?php

/*
|--------------------------------------------------------------------------
| Test Case Tambahan HIRO — Tugas dan penghapusan karyawan (TCT-HIR-F-27 s.d. TCT-HIR-F-30)
|--------------------------------------------------------------------------
*/

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

uses()->group('hiro', 'tct-hir', 'tct-hir-fungsional');

beforeEach(function () {
    $this->hr = buatHR();
    $this->budi = buatKaryawan('Staff', ['fullname' => 'Budi Santoso']);
    $this->ani = buatKaryawan('Staff', ['fullname' => 'Ani Lestari']);
});

describe('TCT-HIR-F-27 HR Admin memberi tugas ke karyawan', function () {
    test('tersimpan dengan penerima benar dan hanya tampil di daftar karyawan itu', function () {
        $this->actingAs($this->hr)->post('/tasks', [
            'title' => 'Susun laporan bulanan', 'description' => 'Oktober 2026',
            'assigned_to' => $this->budi->employee_id, 'due_date' => '2026-10-31', 'status' => 'pending',
        ])->assertRedirect(route('tasks.index'))->assertSessionHas('success');

        $tugas = Task::sole();
        expect($tugas->assigned_to)->toBe($this->budi->employee_id);

        $this->actingAs($this->budi)->get('/tasks')->assertSee('Susun laporan bulanan');
        $this->actingAs($this->ani)->get('/tasks')->assertDontSee('Susun laporan bulanan');
    });

    test('penerima tugas harus karyawan yang ada', function () {
        $this->actingAs($this->hr)->post('/tasks', [
            'title' => 'Tugas yatim', 'assigned_to' => 99999, 'due_date' => '2026-10-31', 'status' => 'pending',
        ])->assertSessionHasErrors('assigned_to');

        expect(Task::count())->toBe(0);
    });
});

describe('TCT-HIR-F-28 Karyawan membuka daftar tugasnya', function () {
    beforeEach(function () {
        buatTugas(karyawanDari($this->budi), ['title' => 'Tugas milik Budi']);
        buatTugas(karyawanDari($this->ani), ['title' => 'Tugas milik Ani']);
    });

    test('halaman tugas hanya menampilkan tugas miliknya', function () {
        $this->actingAs($this->budi)->get('/tasks')
            ->assertViewHas('tasks', fn ($d) => $d->every(fn ($t) => $t->assigned_to === $this->budi->employee_id))
            ->assertSee('Tugas milik Budi')
            ->assertDontSee('Tugas milik Ani');
    });

    test('dashboard karyawan hanya menampilkan tugas miliknya', function () {
        $this->actingAs($this->budi)->get('/dashboard')
            ->assertViewHas('tasks', fn ($d) => $d->every(fn ($t) => $t->assigned_to === $this->budi->employee_id))
            ->assertDontSee('Tugas milik Ani');
    });
});

describe('TCT-HIR-F-29 Karyawan menandai tugas selesai', function () {
    test('status selesai terlihat oleh HR Admin', function () {
        $tugas = buatTugas(karyawanDari($this->budi));

        $this->actingAs($this->budi)->get("/tasks/done/{$tugas->id}")->assertSessionHas('success');

        expect($tugas->fresh()->status)->toBe('done');
        $this->actingAs($this->hr)->get('/tasks')
            ->assertViewHas('tasks', fn ($d) => $d->firstWhere('id', $tugas->id)->status === 'done');
    });

    test('tugas yang tidak ada tidak menimbulkan error server', function () {
        expect($this->actingAs($this->budi)->get('/tasks/done/99999')->status())->toBe(404);
    });
});

describe('TCT-HIR-F-30 HR Admin menghapus karyawan', function () {
    beforeEach(function () {
        $budi = karyawanDari($this->budi);
        buatPresensi($budi, ['date' => '2026-10-01']);
        buatPayroll($budi);
    });

    test('karyawan dan akunnya terhapus tanpa error', function () {
        $id = $this->budi->employee_id;

        $this->actingAs($this->hr)->delete("/employees/$id")
            ->assertRedirect(route('employees.index'))
            ->assertSessionHasNoErrors();

        expect(Employee::find($id))->toBeNull()
            ->and(User::find($this->budi->id))->toBeNull();
    });

    test('akun yang dihapus tidak bisa login', function () {
        $this->actingAs($this->hr)->delete("/employees/{$this->budi->employee_id}");
        Auth::logout();

        $this->post('/login', ['email' => $this->budi->email, 'password' => PASSWORD_UJI]);
        $this->assertGuest();
    });

    test('riwayat absensi dan gaji lama ditangani tanpa error', function () {
        $this->actingAs($this->hr)->delete("/employees/{$this->budi->employee_id}");

        $this->get('/presences')->assertOk();
        $this->get('/payrolls')->assertOk();
        $this->get('/dashboard')->assertOk();
    });
});
