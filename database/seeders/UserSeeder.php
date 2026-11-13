<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cabang;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada setidaknya 1 cabang untuk kasir
        $cabang = Cabang::first();
        if (!$cabang) {
            $cabang = Cabang::create([
                'nama_cabang' => 'Cabang Utama',
                'alamat' => 'Jl. Pusat Kota No. 1'
            ]);
        }

        // 1. Akun Owner
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Bapak Owner',
                'password' => Hash::make('password123'),
                'role' => 'owner',
            ]
        );

        // 2. Akun Admin Gudang
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Pusat',
                'password' => Hash::make('password123'),
                'role' => 'admin_gudang',
            ]
        );

        // 3. Akun Kasir
        User::updateOrCreate(
            ['username' => 'kasir1'],
            [
                'name' => 'Kasir Cabang 1',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'cabang_id' => $cabang->id,
            ]
        );
    }
}
