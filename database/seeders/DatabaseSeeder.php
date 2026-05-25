<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Data Role (ID 1 otomatis menjadi HR)
        $roleHR = Role::create([
            'title' => 'HR',
            'description' => 'Human Resources Department'
        ]);

        // Buat Role tambahan sebagai contoh
        Role::create([
            'title' => 'Sales',
            'description' => 'Sales and Marketing'
        ]);

        // 2. Buat Data Departemen
        $deptHR = Department::create([
            'name' => 'HR',
            'description' => 'Human Resources',
            'status' => 'active'
        ]);

        // 3. Buat Data Karyawan (Employee) sebagai Induk
        $employee = Employee::create([
            'fullname' => 'Test Example',
            'email' => 'test@example.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. HRD No. 1, Jakarta',
            'birth_date' => '1990-01-01',
            'hire_date' => Carbon::now()->format('Y-m-d'),
            'department_id' => $deptHR->id,
            'role_id' => $roleHR->id,
            'status' => 'active',
            'salary' => 8000000,
        ]);

        // 4. Buat Akun User untuk Login
        User::create([
            'name' => 'Test Example',
            'email' => 'test@example.com',
            'password' => Hash::make('password'), // Password standarnya adalah: password
            'employee_id' => $employee->id,
            'role_id' => $roleHR->id,
        ]);
    }
}