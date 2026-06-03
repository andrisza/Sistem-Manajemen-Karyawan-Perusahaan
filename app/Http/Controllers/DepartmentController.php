<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

// Controller untuk mengelola data Departemen (CRUD) — hanya dapat diakses oleh HR
class DepartmentController extends Controller
{
    // Menampilkan daftar semua departemen yang ada
    public function index()
    {
        $departments = Department::all(); // Ambil semua data departemen dari database
        return view('departments.index', compact('departments'));
    }

    // Menampilkan form untuk membuat departemen baru
    public function create()
    {
        return view('departments.create');
    }

    // Memproses dan menyimpan data departemen baru ke database
    public function store(Request $request)
    {
        // Validasi input dari form sebelum disimpan
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|string|max:50', // 'active' atau 'inactive'
        ]);

        // Simpan data departemen baru ke tabel departments
        Department::create($request->all());

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    // Menampilkan form edit untuk departemen tertentu berdasarkan ID
    public function edit($id)
    {
        $department = Department::findOrFail($id); // Cari departemen, error 404 jika tidak ada
        return view('departments.edit', compact('department'));
    }

    // Memproses dan menyimpan perubahan data departemen ke database
    public function update(Request $request, $id)
    {
        // Validasi input perubahan
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|string|max:50',
        ]);

        $department = Department::findOrFail($id); // Cari departemen yang akan diupdate
        $department->update($request->all());       // Terapkan perubahan

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    // Menghapus departemen dari database (SoftDelete — hanya menandai deleted_at)
    public function destroy($id)
    {
        $department = Department::findOrFail($id); // Cari departemen yang akan dihapus
        $department->delete(); // Soft delete: kolom deleted_at diisi, bukan dihapus permanen

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
