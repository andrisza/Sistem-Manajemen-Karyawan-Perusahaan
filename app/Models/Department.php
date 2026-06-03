<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Model Department: menyimpan data departemen/divisi yang ada di perusahaan
class Department extends Model
{
    // SoftDeletes: saat delete dipanggil, data TIDAK dihapus permanen dari database,
    // melainkan hanya diisi kolom deleted_at. Data bisa dipulihkan kembali jika perlu.
    use HasFactory, SoftDeletes;

    // Daftar kolom yang boleh diisi secara mass-assignment
    protected $fillable = [
        'name',
        'description',
        'status', // Status departemen: 'active' atau 'inactive'
    ];
}
