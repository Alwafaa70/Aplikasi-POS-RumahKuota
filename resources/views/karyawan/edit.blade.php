<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karyawan - Owner Dashboard</title>
    <style>
        :root { --primary: #8e44ad; --bg: #f4f7f6; --card: #ffffff; --text: #333; --border: #ddd; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: var(--bg); color: var(--text); }
        .header { background: var(--primary); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .container { padding: 30px; max-width: 600px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;}
        .card { background: var(--card); padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid var(--border); }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;}
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #f39c12; color: white; border: none; padding: 12px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%;}
        .btn-submit:hover { background: #e67e22; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; font-size: 14px; font-weight: bold;}
        
        .alert { padding: 15px; border-radius: 4px; font-weight: bold; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Edit Karyawan: {{ $karyawan->name }}</h1>
    </div>

    <div class="container">
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="card">
            <form method="POST" action="{{ route('karyawan.update', $karyawan->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" value="{{ $karyawan->name }}" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="{{ $karyawan->username }}" required>
                </div>
                <div class="form-group">
                    <label>Password (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="role-select" required onchange="toggleCabang()">
                        <option value="admin_gudang" {{ $karyawan->role == 'admin_gudang' ? 'selected' : '' }}>Admin Gudang</option>
                        <option value="kasir" {{ $karyawan->role == 'kasir' ? 'selected' : '' }}>Kasir</option>
                    </select>
                </div>
                <div class="form-group" id="cabang-group" style="{{ $karyawan->role == 'kasir' ? 'display: block;' : 'display: none;' }}">
                    <label>Penempatan Cabang (Hanya untuk Kasir)</label>
                    <select name="cabang_id" id="cabang-select" {{ $karyawan->role == 'kasir' ? 'required' : '' }}>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id }}" {{ $karyawan->cabang_id == $cabang->id ? 'selected' : '' }}>{{ $cabang->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-submit">UPDATE KARYAWAN</button>
                <a href="{{ route('karyawan.index') }}" class="btn-back">Kembali ke Daftar Karyawan</a>
            </form>
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
