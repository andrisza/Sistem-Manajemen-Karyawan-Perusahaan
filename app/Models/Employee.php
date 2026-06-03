<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model Employee: menyimpan data profil lengkap setiap karyawan perusahaan
class Employee extends Model
{
    use HasFactory;

    // Daftar kolom yang boleh diisi secara mass-assignment
    protected $fillable = [
        'fullname',
        'email',
        'phone_number',
        'address',
        'birth_date',
        'hire_date',
        'department_id', // FK (Foreign Key) ke tabel departments
        'role_id',       // FK ke tabel roles
        'status',        // Status kerja: 'active' atau 'inactive'
        'salary',        // Gaji pokok karyawan
    ];

    // Relasi: satu karyawan hanya berada di satu departemen
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Relasi: satu karyawan hanya memiliki satu jabatan/role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
