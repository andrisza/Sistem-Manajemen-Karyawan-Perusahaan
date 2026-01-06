<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class HumanResourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Departments
        DB::table('departments')->insert([
            ['name' => 'HR', 'description' => 'Human Resources', 'status' => 'active', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'IT', 'description' => 'Departemen Information Tech', 'status' => 'active', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Sales', 'description' => 'Departemen Sales', 'status' => 'active', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // 2. Roles
        DB::table('roles')->insert([
            ['title' => 'HR', 'description' => 'Handling team', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['title' => 'Developer', 'description' => 'Handling codes', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['title' => 'Sales', 'description' => 'Handling selling', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // 3. Employees (FIX: Membuat 2 Employee agar employee_id 2 valid)
        DB::table('employees')->insert([
            [
                'fullname' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'birth_date' => $faker->dateTimeBetween('-40 years', '-20 years'),
                'hire_date' => Carbon::now(),
                'department_id' => 1,
                'role_id' => 1,
                'status' => 'active',
                'salary' => $faker->randomFloat(2, 6000, 30000), // Fix: Min < Max
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
            [
                'fullname' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'birth_date' => $faker->dateTimeBetween('-40 years', '-20 years'),
                'hire_date' => Carbon::now(),
                'department_id' => 2,
                'role_id' => 2,
                'status' => 'active',
                'salary' => $faker->randomFloat(2, 6000, 30000), // Fix: Min < Max
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ],
        ]);  
        
        // 4. Tasks
        DB::table('tasks')->insert([
            [
                'title' => $faker->sentence(3),
                'description' => $faker->paragraph,
                'assigned_to' => 1,
                'due_date' => Carbon::parse('2025-02-15'),
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => $faker->sentence(3),
                'description' => $faker->paragraph,
                'assigned_to' => 2, // Assign ke employee 2
                'due_date' => Carbon::parse('2025-02-15'),
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
        
        // 5. Payroll (FIX: Dibungkus dalam satu array parent [])
        DB::table('payroll')->insert([
            [
                'employee_id' => 1,
                'salary' => $faker->randomFloat(2, 6000, 30000),
                'bonuses' => $faker->randomFloat(2, 100, 5000),
                'deductions' => $faker->randomFloat(2, 500, 1000),
                'net_salary' => $faker->randomFloat(2, 6000, 30000),
                'pay_date' => Carbon::parse('2025-02-28'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()  
            ], 
            [
                'employee_id' => 2,
                'salary' => $faker->randomFloat(2, 6000, 30000),
                'bonuses' => $faker->randomFloat(2, 100, 5000),
                'deductions' => $faker->randomFloat(2, 500, 1000),
                'net_salary' => $faker->randomFloat(2, 6000, 30000),
                'pay_date' => Carbon::parse('2025-02-28'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now() 
            ]
        ]);  

        // 6. Presences (FIX: Struktur array diperbaiki)
        DB::table('presences')->insert([
            [
                'employee_id' => 1,
                'check_in' => Carbon::parse('2025-02-01 08:00:00'),
                'check_out' => Carbon::parse('2025-02-01 17:00:00'),
                'date' => Carbon::parse('2025-02-01'),
                'status' => 'present',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'employee_id' => 2,
                'check_in' => Carbon::parse('2025-02-01 08:00:00'),
                'check_out' => Carbon::parse('2025-02-01 17:00:00'),
                'date' => Carbon::parse('2025-02-01'),
                'status' => 'present',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
        
        // 7. Leave Requests (FIX: Dibungkus dalam satu array parent [])
        DB::table('leave_requests')->insert([
            [
                'employee_id' => 1,
                'leave_type' => 'sick leave',
                'start_date' => Carbon::parse('2025-03-01'),
                'end_date' => Carbon::parse('2025-03-05'),
                'status' => 'approved',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'employee_id' => 2,
                'leave_type' => 'annual leave',
                'start_date' => Carbon::parse('2025-04-10'),
                'end_date' => Carbon::parse('2025-04-15'),
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
    }
}