<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Model Presence: menyimpan catatan absensi harian (check-in & check-out) karyawan
class Presence extends Model
{
    // SoftDeletes: data presensi tidak dihapus permanen
    use HasFactory, SoftDeletes;

    // Daftar kolom yang boleh diisi secara mass-assignment
    protected $fillable = [
        'employee_id', // FK ke tabel employees
        'check_in',    // Waktu masuk (format datetime: Y-m-d H:i:s)
        'check_out',   // Waktu keluar (format datetime: Y-m-d H:i:s)
        'date',        // Tanggal absensi (format: Y-m-d)
        'status',      // Status: 'present', 'absent', atau 'leave'
        'latitude',    // Koordinat latitude GPS saat karyawan check-in (validasi area kantor)
        'longitude'    // Koordinat longitude GPS saat karyawan check-in (validasi area kantor)
    ];

    // Relasi: setiap data presensi dimiliki oleh satu karyawan
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
