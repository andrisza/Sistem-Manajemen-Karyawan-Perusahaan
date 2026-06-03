<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Model Task: menyimpan data tugas yang diberikan kepada karyawan
class Task extends Model
{
    // SoftDeletes: data tugas tidak dihapus permanen dari database
    use HasFactory, SoftDeletes;

    // Daftar kolom yang boleh diisi secara mass-assignment
    protected $fillable = [
        'title',       // Judul singkat tugas
        'description', // Deskripsi detail tentang tugas yang harus diselesaikan
        'assigned_to', // ID karyawan yang bertanggung jawab atas tugas ini (FK ke employees)
        'due_date',    // Batas waktu penyelesaian tugas
        'status',      // Status tugas: 'pending', 'on progress', atau 'done'
    ];

    // Relasi ke Employee: menggunakan FK 'assigned_to' karena nama kolomnya bukan 'employee_id' (default)
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }
}
