<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Model Role: menyimpan data jabatan/posisi karyawan (misal: HR, Marketing, Developer)
class Role extends Model
{
    // SoftDeletes: penghapusan role tidak permanen, kolom deleted_at diisi sebagai penanda
    use HasFactory, SoftDeletes;

    // Daftar kolom yang boleh diisi secara mass-assignment
    protected $fillable = [
        'title',       // Nama jabatan (contoh: "HR", "Sales")
        'description'  // Deskripsi tugas/tanggung jawab jabatan
    ];
}
