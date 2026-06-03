<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

// Controller untuk mengelola data Role/Jabatan (CRUD) — hanya dapat diakses oleh HR
class RoleController extends Controller
{
    // Menampilkan daftar semua jabatan yang terdaftar
    public function index()
    {
        $roles = Role::all(); // Ambil semua data role dari database
        return view('roles.index', compact('roles'));
    }

    // Menampilkan form untuk membuat jabatan baru
    public function create()
    {
        return view('roles.create');
    }

    // Memproses dan menyimpan jabatan baru ke database
    public function store(Request $request)
    {
        // Validasi input sebelum disimpan
        $request->validate([
            'title'       => 'required|string|max:255', // Nama jabatan wajib diisi
            'description' => 'nullable|string',
        ]);

        Role::create($request->all()); // Simpan jabatan baru ke tabel roles

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    // Menampilkan form edit untuk jabatan tertentu (menggunakan Route Model Binding)
    public function edit(Role $role)
    {
        // Laravel otomatis mencari Role berdasarkan ID di URL — jika tidak ada, error 404
        return view('roles.edit', compact('role'));
    }

    // Memproses dan menyimpan perubahan data jabatan
    public function update(Request $request, Role $role)
    {
        // Validasi input perubahan
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $role->update($request->all()); // Terapkan perubahan ke record yang dipilih

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    // Menghapus jabatan dari database (SoftDelete)
    public function destroy(Role $role)
    {
        $role->delete(); // Soft delete: mengisi deleted_at, bukan menghapus permanen

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
