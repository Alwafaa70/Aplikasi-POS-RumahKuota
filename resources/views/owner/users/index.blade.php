@extends('layouts.app')
@section('title', 'Manajemen Akun Pegawai')

@push('styles')
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; color: #34495e; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
    .btn-submit { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; }
    .btn-submit:hover { background: #2980b9; }
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; }
    .badge-kasir { background: #2ecc71; }
    .badge-admin { background: #e74c3c; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    th { background: #f8f9fa; font-weight: bold; color: #2c3e50; }
</style>
@endpush

@section('content')
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    
    <!-- Form Tambah Pegawai -->
    <div class="card" style="flex: 1; min-width: 300px;">
        <h3 style="margin-top: 0; color: #2c3e50;">Buat Akun Baru</h3>
        <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 20px;">
            Pastikan untuk membedakan antara <b>Admin Gudang</b> (berada di Gudang Pusat, tidak terikat 1 cabang toko) dan <b>Kasir</b> (terikat pada 1 Cabang).
        </p>

        @if(session('success'))
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px;">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso">
            </div>
            <div class="form-group">
                <label>Username (Untuk Login)</label>
                <input type="text" name="username" required placeholder="Contoh: budi.kasir">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Biarkan kosong untuk default: password123">
            </div>
            
            <div class="form-group">
                <label>Peran (Role)</label>
                <select name="role" id="role_select" onchange="toggleCabang(this)" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="kasir">Kasir (Melayani Transaksi)</option>
                    <option value="admin_gudang">Admin Pusat (Mengelola Gudang & Mutasi)</option>
                </select>
            </div>
            
            <div class="form-group" id="cabang_wrapper" style="display: none; background: #eaf2f8; padding: 15px; border-radius: 6px; border-left: 4px solid var(--primary);">
                <label style="color: var(--primary);">Penempatan Cabang (Wajib untuk Kasir)</label>
                <p style="font-size: 12px; margin-top: 0; color: #666;">Pilih di cabang mana kasir ini akan bertugas. Seluruh transaksi kasir ini akan masuk ke laporan cabang tersebut.</p>
                <select name="cabang_id" id="cabang_select">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($cabangs as $cab)
                        <option value="{{ $cab->id }}">{{ $cab->nama_cabang }} ({{ $cab->lokasi }})</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-submit" style="width: 100%; margin-top: 10px;">Simpan Akun</button>
        </form>
    </div>

    <!-- Daftar Pegawai -->
    <div class="card" style="flex: 2; min-width: 400px; overflow-x: auto;">
        <h3 style="margin-top: 0; color: #2c3e50;">Daftar Akun Pegawai</h3>
        
        <table>
            <thead>
                <tr>
                    <th>Nama & Username</th>
                    <th>Role & Status</th>
                    <th>Penempatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div style="font-weight: bold; color: #2c3e50;">{{ $user->name }}</div>
                        <div style="font-size: 12px; color: #7f8c8d;">@ {{ $user->username }}</div>
                    </td>
                    <td>
                        <div style="margin-bottom: 5px;">
                            @if($user->role === 'kasir')
                                <span class="badge badge-kasir">KASIR</span>
                            @elseif($user->role === 'admin_gudang')
                                <span class="badge badge-admin">ADMIN PUSAT</span>
                            @else
                                <span class="badge" style="background: #95a5a6;">{{ strtoupper($user->role) }}</span>
                            @endif
                        </div>
                        <div>
                            @if($user->is_active)
                                <span class="badge" style="background: #27ae60; font-size: 10px;">Aktif</span>
                            @else
                                <span class="badge" style="background: #7f8c8d; font-size: 10px;">Nonaktif</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($user->role === 'kasir')
                            <div style="font-weight: bold; color: #34495e;">{{ $user->cabang->nama_cabang ?? '-' }}</div>
                        @else
                            <div style="font-size: 12px; color: #7f8c8d;"><i>Gudang Pusat (Tidak terikat cabang)</i></div>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <button type="button" onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->username }}', '{{ $user->role }}', '{{ $user->cabang_id }}')" style="background: #f39c12; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Edit</button>
                            
                            <form action="{{ route('users.toggle_active', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" style="background: {{ $user->is_active ? '#e67e22' : '#27ae60' }}; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun pegawai ini secara permanen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #888;">Belum ada akun pegawai yang didaftarkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit User -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div class="card" style="width: 400px; margin: 50px auto; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-top: 0;">Edit Akun Pegawai</h3>
        
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="edit_username" required>
            </div>
            <div class="form-group">
                <label>Password Baru (Opsional)</label>
                <input type="password" name="password" placeholder="Isi hanya jika ingin mengubah password">
            </div>
            
            <div class="form-group">
                <label>Peran (Role)</label>
                <select name="role" id="edit_role" onchange="toggleCabang(this, 'edit_cabang_wrapper', 'edit_cabang_select')" required>
                    <option value="kasir">Kasir</option>
                    <option value="admin_gudang">Admin Pusat (Gudang)</option>
                </select>
            </div>
            
            <div class="form-group" id="edit_cabang_wrapper" style="display: none; background: #eaf2f8; padding: 10px; border-radius: 6px; border-left: 4px solid var(--primary);">
                <label style="color: var(--primary);">Penempatan Cabang</label>
                <select name="cabang_id" id="edit_cabang_select">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($cabangs as $cab)
                        <option value="{{ $cab->id }}">{{ $cab->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-submit" style="flex: 1;">Simpan Perubahan</button>
                <button type="button" onclick="closeEditModal()" style="background: #ccc; border: none; padding: 10px; border-radius: 6px; cursor: pointer; flex: 1;">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleCabang(selectEl, wrapperId = 'cabang_wrapper', selectId = 'cabang_select') {
        const wrapper = document.getElementById(wrapperId);
        const selectCabang = document.getElementById(selectId);
        
        if (selectEl.value === 'kasir') {
            wrapper.style.display = 'block';
            selectCabang.required = true;
        } else {
            wrapper.style.display = 'none';
            selectCabang.required = false;
            selectCabang.value = "";
        }
    }

    function editUser(id, name, username, role, cabang_id) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_role').value = role;
        
        toggleCabang(document.getElementById('edit_role'), 'edit_cabang_wrapper', 'edit_cabang_select');
        
        if (role === 'kasir' && cabang_id) {
            document.getElementById('edit_cabang_select').value = cabang_id;
        }
        
        document.getElementById('editForm').action = `/owner/users/${id}`;
        document.getElementById('editModal').style.display = 'block';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>
@endpush
@endsection
