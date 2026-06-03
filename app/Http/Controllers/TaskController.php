<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Employee;

// Controller untuk mengelola data Tugas (Task) — semua role dapat mengakses
class TaskController extends Controller
{
    // Menampilkan daftar tugas sesuai role pengguna yang login
    public function index()
    {
        if (session('role') == 'HR') {
            // HR melihat semua tugas di seluruh perusahaan
            $tasks = Task::all();
        } else {
            // Karyawan biasa hanya melihat tugas yang ditugaskan kepada dirinya
            $tasks = Task::where('assigned_to', session('employee_id'))->get();
        }
        return view('tasks.index', compact('tasks'));
    }

    // Menampilkan form untuk membuat tugas baru
    public function create()
    {
        $employees = Employee::all(); // Daftar karyawan untuk dropdown pilihan assignee
        return view('tasks.create', compact('employees'));
    }

    // Memproses dan menyimpan tugas baru ke database
    public function store(Request $request)
    {
        // Validasi input form tugas baru
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required',   // ID karyawan yang diberi tugas
            'due_date'    => 'required|date',
            'status'      => 'required|string', // 'pending', 'on progress', atau 'done'
        ]);

        Task::create($validated); // Simpan tugas baru menggunakan data yang sudah divalidasi

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    // Menampilkan detail tugas tertentu (Route Model Binding)
    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }

    // Menampilkan form edit untuk tugas tertentu
    public function edit(Task $task)
    {
        $employees = Employee::all(); // Daftar karyawan untuk dropdown assignee
        return view('tasks.edit', compact('task', 'employees'));
    }

    // Memproses dan menyimpan perubahan data tugas
    public function update(Request $request, Task $task)
    {
        // Validasi input perubahan tugas
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required',
            'due_date'    => 'required|date',
            'status'      => 'required|string',
        ]);

        $task->update($validated); // Terapkan perubahan ke tugas yang dipilih

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    // Mengubah status tugas menjadi 'done' (selesai) — dipanggil dari tombol "Mark as Done"
    public function done(int $id)
    {
        $task = Task::find($id);
        $task->update(['status' => 'done']); // Set status langsung ke 'done'

        return redirect()->route('tasks.index')->with('success', 'Task marked as done.');
    }

    // Mengubah status tugas menjadi 'pending' — dipanggil dari tombol "Mark as Pending"
    public function pending(int $id)
    {
        $task = Task::find($id);
        $task->update(['status' => 'pending']); // Kembalikan status ke 'pending'

        return redirect()->route('tasks.index')->with('success', 'Task marked as pending.');
    }

    // Menghapus tugas dari database (SoftDelete)
    public function destroy(Task $task)
    {
        $task->delete(); // Soft delete: mengisi deleted_at

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
