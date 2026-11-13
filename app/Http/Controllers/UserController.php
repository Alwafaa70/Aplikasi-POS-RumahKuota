<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cabang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Owner only route
        $users = User::with('cabang')->where('role', '!=', 'owner')->latest()->get();
        $cabangs = Cabang::all();
        
        return view('owner.users.index', compact('users', 'cabangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'role' => 'required|in:kasir,admin_gudang',
            'cabang_id' => [
                'nullable',
                Rule::requiredIf($request->role === 'kasir'),
                'exists:cabang,id'
            ],
            'password' => 'nullable|string|min:6',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->role = $request->role;
        
        // Kasir wajib punya cabang_id. Admin gudang tidak.
        if ($request->role === 'kasir') {
            $user->cabang_id = $request->cabang_id;
        } else {
            $user->cabang_id = null; // Admin gudang tidak spesifik cabang untuk transaksinya
        }

        // Default password if not provided
        $password = $request->password ?: 'password123';
        $user->password = Hash::make($password);
        
        $user->save();

        return back()->with('success', 'Akun pegawai berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent editing other owners unless it's yourself
        if ($user->role === 'owner') {
            return back()->with('error', 'Tidak dapat mengedit akun Owner.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'role' => 'required|in:kasir,admin_gudang',
            'cabang_id' => [
                'nullable',
                Rule::requiredIf($request->role === 'kasir'),
                'exists:cabang,id'
            ],
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->role = $request->role;
        
        if ($request->role === 'kasir') {
            $user->cabang_id = $request->cabang_id;
        } else {
            $user->cabang_id = null;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        return back()->with('success', 'Akun pegawai berhasil diperbarui.');
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role === 'owner') {
            return back()->with('error', 'Tidak dapat menonaktifkan akun Owner.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun pegawai berhasil $status.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting other owners
        if ($user->role === 'owner') {
            return back()->with('error', 'Tidak dapat menghapus akun Owner.');
        }

        $user->delete();
        return back()->with('success', 'Akun pegawai berhasil dihapus.');
    }
}
