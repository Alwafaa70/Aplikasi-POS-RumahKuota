<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cabang;
use Illuminate\Support\Facades\Hash;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = User::whereIn('role', ['admin_gudang', 'kasir'])->with('cabang')->get();
        $cabangs = Cabang::all();
        return view('karyawan.index', compact('karyawans', 'cabangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin_gudang,kasir',
            'cabang_id' => 'nullable|exists:cabang,id',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'cabang_id' => $request->role === 'kasir' ? $request->cabang_id : null,
        ]);

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(User $karyawan)
    {
        $cabangs = Cabang::all();
        return view('karyawan.edit', compact('karyawan', 'cabangs'));
    }

    public function update(Request $request, User $karyawan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $karyawan->id,
            'role' => 'required|in:admin_gudang,kasir',
            'cabang_id' => 'nullable|exists:cabang,id',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'role' => $request->role,
            'cabang_id' => $request->role === 'kasir' ? $request->cabang_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $karyawan->update($data);

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $karyawan)
    {
        $karyawan->delete();
        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil dihapus.');
    }
}
