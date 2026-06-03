<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Model Payroll: menyimpan data penggajian bulanan untuk setiap karyawan
class Payroll extends Model
{
    // SoftDeletes: data gaji tidak dihapus permanen, hanya ditandai deleted_at
    use HasFactory, SoftDeletes;

    // Nama tabel ditentukan secara eksplisit karena default Laravel akan mencari 'payrolls' (plural)
    protected $table = 'payroll';

    // Daftar kolom yang boleh diisi secara mass-assignment
    protected $fillable = [
        'employee_id', // FK ke tabel employees — karyawan mana yang digaji
        'salary',      // Gaji pokok
        'bonuses',     // Tunjangan atau bonus tambahan
        'deductions',  // Potongan (BPJS, pajak, dll)
        'net_salary',  // Gaji bersih = salary + bonuses - deductions (dihitung di controller)
        'pay_date',    // Tanggal pembayaran gaji
    ];

    // Relasi: setiap data payroll dimiliki oleh satu karyawan
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
