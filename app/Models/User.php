<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Model User: merepresentasikan akun login yang dimiliki oleh setiap karyawan
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Daftar kolom yang boleh diisi secara mass-assignment (misal saat User::create([...]))
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id', // Menghubungkan akun ini ke data karyawan di tabel employees
        'role_id',     // Menyimpan ID jabatan untuk keperluan akses/izin
    ];

    // Kolom yang disembunyikan ketika data dikonversi ke JSON atau array (misal respons API)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Mendefinisikan konversi tipe data otomatis untuk kolom tertentu
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Otomatis diubah menjadi objek Carbon
            'password' => 'hashed',            // Password otomatis di-hash saat nilai baru disimpan
        ];
    }
}
