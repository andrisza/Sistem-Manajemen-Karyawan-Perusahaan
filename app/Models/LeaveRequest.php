<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Model LeaveRequest: menyimpan pengajuan cuti yang dibuat oleh karyawan
class LeaveRequest extends Model
{
    // SoftDeletes: pengajuan cuti tidak dihapus permanen dari database
    use HasFactory, SoftDeletes;

    // Daftar kolom yang boleh diisi secara mass-assignment
    protected $fillable = [
        'employee_id', // FK ke tabel employees — karyawan pengaju cuti
        'leave_type',  // Jenis cuti: 'Sick Leave', 'Vacation', 'Birth Leave'
        'start_date',  // Tanggal mulai cuti
        'end_date',    // Tanggal selesai cuti
        'status'       // Status pengajuan: 'pending', 'confirm', atau 'reject'
    ];

    // Relasi: setiap pengajuan cuti dimiliki oleh satu karyawan
    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
