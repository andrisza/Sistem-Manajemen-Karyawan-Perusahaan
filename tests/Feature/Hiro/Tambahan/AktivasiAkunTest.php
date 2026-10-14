<?php

/*
|--------------------------------------------------------------------------
| Test Case Tambahan HIRO — Aktivasi akun (TCT-HIR-F-01 s.d. TCT-HIR-F-07)
|--------------------------------------------------------------------------
| Alur: HR menambah karyawan -> email undangan berisi signed URL (24 jam)
| -> karyawan membuat password sendiri -> login.
*/

use App\Mail\AccountInvitation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses()->group('hiro', 'tct-hir', 'tct-hir-fungsional');

// HR mengundang karyawan baru lewat form, lalu mengambil tautan aktivasi dari email terkirim
function undangKaryawan($test, string $email = 'calon@hiro.test'): array
{
    Mail::fake();
    $test->actingAs(buatHR())->post('/employees', dataKaryawanBaru(['email' => $email]))->assertSessionHasNoErrors();
    Auth::logout();

    $user = User::where('email', $email)->firstOrFail();
    $link = Mail::sent(AccountInvitation::class)->last()->activationLink;

    return [$user, $link];
}

// URL tujuan form aktivasi diambil dari halaman, sama seperti yang dikirim browser
function aksiFormAktivasi(string $html): string
{
    preg_match('/<form[^>]+action="([^"]+)"/', $html, $cocok);

    return html_entity_decode($cocok[1]);
}

function hashPassword(User $user): string
{
    return User::whereKey($user->id)->value('password');
}

describe('TCT-HIR-F-01 HR menambah karyawan baru dengan email valid', function () {
    test('data tersimpan, akun belum aktif, email aktivasi terkirim', function () {
        $this->travelTo(now()->setTime(10, 0));
        [$user, $link] = undangKaryawan($this, 'baru@hiro.test');

        expect(Employee::where('email', 'baru@hiro.test')->exists())->toBeTrue()
            ->and($user->employee_id)->toBe(Employee::where('email', 'baru@hiro.test')->value('id'))
            ->and($user->email_verified_at)->toBeNull();

        Mail::assertSent(AccountInvitation::class, fn ($mail) => $mail->hasTo('baru@hiro.test'));

        parse_str(parse_url($link, PHP_URL_QUERY), $query);
        expect($link)->toContain('/activate/'.$user->id)
            ->and($query)->toHaveKeys(['expires', 'signature'])
            ->and((int) $query['expires'])->toBe(now()->addHours(24)->getTimestamp());

        // Akun belum aktif: belum ada password yang diketahui siapa pun
        $this->post('/login', ['email' => 'baru@hiro.test', 'password' => 'password']);
        $this->assertGuest();
    });
});

describe('TCT-HIR-F-02 Buka tautan valid dalam 24 jam, buat password, login', function () {
    test('password tersimpan terenkripsi, akun aktif, login berhasil', function () {
        [$user, $link] = undangKaryawan($this);
        $this->travel(23)->hours();

        $form = $this->get($link)->assertOk();
        $this->post(aksiFormAktivasi($form->getContent()), [
            'password' => 'PasswordBaru#1', 'password_confirmation' => 'PasswordBaru#1',
        ])->assertRedirect('/dashboard');

        expect(hashPassword($user))->not->toBe('PasswordBaru#1')
            ->and(Hash::check('PasswordBaru#1', hashPassword($user)))->toBeTrue();

        Auth::logout();
        $this->post('/login', ['email' => $user->email, 'password' => 'PasswordBaru#1'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    });
});

describe('TCT-HIR-F-03 Buka tautan setelah lewat 24 jam', function () {
    test('ditolak karena kedaluwarsa dan akun tetap nonaktif', function () {
        [$user, $link] = undangKaryawan($this);
        $form = $this->get($link)->getContent();
        $hashAwal = hashPassword($user);

        $this->travel(24)->hours();
        $this->travel(1)->minutes();

        expect($this->get($link)->status())->toBeIn([401, 403, 410]);

        // Form yang sudah terbuka sebelumnya dikirim setelah kedaluwarsa
        $this->post(aksiFormAktivasi($form), ['password' => 'Telat#12345', 'password_confirmation' => 'Telat#12345']);
        expect(hashPassword($user))->toBe($hashAwal, 'Password berubah walau tautan sudah kedaluwarsa');
    });
});

describe('TCT-HIR-F-04 Buka lagi tautan yang sudah dipakai', function () {
    test('tautan sekali pakai: ditolak dan password tidak bisa diganti lewat tautan itu', function () {
        [$user, $link] = undangKaryawan($this);
        $form = $this->get($link)->getContent();
        $this->post(aksiFormAktivasi($form), ['password' => 'PasswordBaru#1', 'password_confirmation' => 'PasswordBaru#1']);
        Auth::logout();
        $hashSetelahAktivasi = hashPassword($user);

        expect($this->get($link)->status())->toBeIn([401, 403, 404, 410], 'Tautan aktivasi masih bisa dibuka setelah dipakai');

        $this->post(aksiFormAktivasi($form), ['password' => 'DiambilAlih#1', 'password_confirmation' => 'DiambilAlih#1']);
        expect(hashPassword($user))->toBe($hashSetelahAktivasi, 'Tautan lama berhasil mengubah password');
    });
});

describe('TCT-HIR-F-05 Token pada tautan dimanipulasi', function () {
    test('tautan GET yang diubah ditolak', function (Closure $ubah) {
        [$user, $link] = undangKaryawan($this);
        $lain = buatKaryawan();

        expect($this->get($ubah($link, $user, $lain))->status())->toBeIn([401, 403]);
    })->with([
        'signature dipotong' => [fn ($link) => substr($link, 0, -10)],
        'signature diacak'   => [fn ($link) => preg_replace('/signature=\w+/', 'signature='.str_repeat('a', 64), $link)],
        'signature dihapus'  => [fn ($link) => preg_replace('/&signature=\w+/', '', $link)],
        'ID user ditukar'    => [fn ($link, $user, $lain) => str_replace('/activate/'.$user->id, '/activate/'.$lain->id, $link)],
        'expires diperpanjang' => [fn ($link) => preg_replace('/expires=\d+/', 'expires='.now()->addYear()->getTimestamp(), $link)],
    ]);

    test('POST aktivasi tanpa tautan sah tidak boleh mengubah password akun mana pun', function (string $jabatan) {
        $korban = buatKaryawan($jabatan);
        $hashAwal = hashPassword($korban);

        $this->post(route('activation.submit', $korban->id), [
            'password' => 'DiambilAlih#1', 'password_confirmation' => 'DiambilAlih#1',
        ]);

        expect(hashPassword($korban))->toBe($hashAwal, "Password akun $jabatan bisa diganti tanpa tautan aktivasi");
        $this->assertGuest();
    })->with(['HR Admin' => 'HR', 'Karyawan' => 'Staff']);
});

describe('TCT-HIR-F-06 Buat password tidak valid', function () {
    test('ditolak dengan pesan dan akun tetap belum aktif', function (string $password, string $konfirmasi) {
        [$user, $link] = undangKaryawan($this);
        $hashAwal = hashPassword($user);
        $form = $this->get($link)->getContent();

        $this->from($link)
            ->post(aksiFormAktivasi($form), ['password' => $password, 'password_confirmation' => $konfirmasi])
            ->assertSessionHasErrors('password');

        expect(hashPassword($user))->toBe($hashAwal);
        $this->assertGuest();
    })->with([
        'konfirmasi beda' => ['PasswordBaru#1', 'PasswordLain#1'],
        'kosong'          => ['', ''],
        'sangat pendek'   => ['abc', 'abc'],
    ]);
});

describe('TCT-HIR-F-07 Login dengan akun yang belum diaktivasi', function () {
    test('ditolak', function (string $password) {
        [$user] = undangKaryawan($this);

        $this->post('/login', ['email' => $user->email, 'password' => $password])->assertSessionHasErrors('email');
        $this->assertGuest();
    })->with(['password umum' => 'password', 'password akun uji lain' => PASSWORD_UJI]);
});
