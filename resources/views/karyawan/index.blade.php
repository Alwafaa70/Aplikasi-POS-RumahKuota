<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Karyawan - Owner Dashboard</title>
    <style>
        :root { --primary: #8e44ad; --bg: #f4f7f6; --card: #ffffff; --text: #333; --border: #ddd; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: var(--bg); color: var(--text); }
        .header { background: var(--primary); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .header button { background: #e74c3c; border: none; color: white; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;}
        .container { padding: 30px; max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;}
        .card { background: var(--card); padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid var(--border); }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;}
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #2ecc71; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%;}
        .btn-submit:hover { background: #27ae60; }
        .btn-edit { background: #f39c12; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold;}
        .btn-delete { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px; font-weight: bold;}
        
        .alert { padding: 15px; border-radius: 4px; font-weight: bold; margin-bottom: 15px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid var(--border); padding: 10px; text-align: left; font-size: 14px; }
        .table th { background: #f9f9f9; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manajemen Karyawan</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="{{ route('owner.dashboard') }}" style="color: white; text-decoration: none; font-weight: bold; font-size: 14px;">Dashboard</a>
            <a href="{{ route('karyawan.index') }}" style="color: white; text-decoration: none; font-weight: bold; font-size: 14px;">Karyawan</a>
            <a href="{{ route('laporan.index') }}" style="color: white; text-decoration: none; font-weight: bold; font-size: 14px;">Laporan</a>
            <span>| Halo, {{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div style="display: flex; gap: 20px;">
            <!-- Form Tambah Karyawan -->
            <div class="card" style="flex: 1;">
                <h2 style="margin-top: 0; border-bottom: 2px solid var(--primary); padding-bottom: 10px;">Tambah Karyawan Baru</h2>
                <form method="POST" action="{{ route('karyawan.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="role-select" required onchange="toggleCabang()">
                            <option value="">-- Pilih Role --</option>
                            <option value="admin_gudang">Admin Gudang</option>
                            <option value="kasir">Kasir</option>
                        </select>
                    </div>
                    <div class="form-group" id="cabang-group" style="display: none;">
                        <label>Penempatan Cabang (Hanya untuk Kasir)</label>
                        <select name="cabang_id" id="cabang-select">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-submit">SIMPAN KARYAWAN</button>
                </form>
            </div>

            <!-- Tabel Daftar Karyawan -->
            <div class="card" style="flex: 2;">
                <h2 style="margin-top: 0; border-bottom: 2px solid var(--primary); padding-bottom: 10px;">Daftar Karyawan Aktif</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Cabang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($karyawans as $karyawan)
                        <tr>
                            <td>{{ $karyawan->name }}</td>
                            <td>{{ $karyawan->username }}</td>
                            <td>
                                @if($karyawan->role == 'admin_gudang')
                                    <span style="background: #3498db; color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px;">Admin Gudang</span>
                                @else
                                    <span style="background: #2ecc71; color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px;">Kasir</span>
                                @endif
                            </td>
                            <td>{{ $karyawan->cabang ? $karyawan->cabang->nama_cabang : '-' }}</td>
                            <td style="display: flex; gap: 5px;">
                                <a href="{{ route('karyawan.edit', $karyawan->id) }}" class="btn-edit">Edit</a>
                                <form method="POST" action="{{ route('karyawan.destroy', $karyawan->id) }}" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @if($karyawans->isEmpty())
                        <tr>
                            <td colspan="5" style="text-align: center;">Belum ada karyawan.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleCabang() {
            var role = document.getElementById('role-select').value;
            var cabangGroup = document.getElementById('cabang-group');
            var cabangSelect = document.getElementById('cabang-select');
            
            if (role === 'kasir') {
                cabangGroup.style.display = 'block';
                cabangSelect.setAttribute('required', 'true');
            } else {
                cabangGroup.style.display = 'none';
                cabangSelect.removeAttribute('required');
                cabangSelect.value = '';
            }
        }
    </script>
</body>
</html>
