<?php

namespace App\Http\Controllers;

// Kelas dasar (base controller) yang diwarisi oleh semua controller di aplikasi ini.
abstract class Controller
{
    // Tolak (403) jika data bukan milik karyawan yang login — HR boleh mengakses semua data
    protected function pastikanMilikSendiriAtauHR(int $employeeId): void
    {
        abort_unless(
            session('role') === 'HR' || (int) session('employee_id') === $employeeId,
            403,
            'Anda tidak memiliki akses ke data ini.'
        );
    }
}
