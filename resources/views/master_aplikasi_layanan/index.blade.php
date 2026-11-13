@extends('layouts.app')
@section('title', 'Master Aplikasi Layanan')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Master Aplikasi Layanan</h2>
        <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow font-bold hover:bg-blue-700">
            + Tambah Layanan
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700 border-b-2 border-gray-200">
                    <th class="p-4 font-bold">Nama Layanan</th>
                    <th class="p-4 font-bold">Jenis Layanan</th>
                    <th class="p-4 font-bold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($layanans as $layanan)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-4 font-bold text-gray-800">{{ $layanan->nama_layanan }}</td>
                    <td class="p-4">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs uppercase font-bold">{{ $layanan->jenis_layanan }}</span>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="openEditModal('{{ $layanan->id }}', '{{ $layanan->nama_layanan }}', '{{ $layanan->jenis_layanan }}')" class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-200 font-medium text-sm transition">
                                Edit
                            </button>
                            <form action="{{ route('master.aplikasi_layanan.destroy', $layanan->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus layanan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-100 text-red-600 px-3 py-1 rounded hover:bg-red-200 font-medium text-sm transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-6 text-center text-gray-500">Belum ada layanan yang ditambahkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Tambah Aplikasi Layanan</h3>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-gray-500 hover:text-gray-800 text-xl font-bold">&times;</button>
        </div>
        
        <form action="{{ route('master.aplikasi_layanan.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nama Layanan (Bank/E-Wallet)</label>
                <input type="text" name="nama_layanan" class="w-full border rounded-lg p-2 focus:border-blue-500 focus:outline-none" placeholder="Cth: BCA, Mandiri, DANA..." required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Jenis Layanan</label>
                <select name="jenis_layanan" class="w-full border rounded-lg p-2 bg-white" required>
                    <option value="bank">Bank Transfer</option>
                    <option value="ewallet">E-Wallet</option>
                </select>
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded font-bold hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Edit Aplikasi Layanan</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-500 hover:text-gray-800 text-xl font-bold">&times;</button>
        </div>
        
        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nama Layanan</label>
                <input type="text" id="edit_nama_layanan" name="nama_layanan" class="w-full border rounded-lg p-2 focus:border-blue-500 focus:outline-none" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Jenis Layanan</label>
                <select id="edit_jenis_layanan" name="jenis_layanan" class="w-full border rounded-lg p-2 bg-white" required>
                    <option value="bank">Bank Transfer</option>
                    <option value="ewallet">E-Wallet</option>
                </select>
            </div>
            
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded font-bold hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
function openEditModal(id, nama, jenis) {
    document.getElementById('formEdit').action = '/master/aplikasi-layanan/' + id;
    document.getElementById('edit_nama_layanan').value = nama;
    document.getElementById('edit_jenis_layanan').value = jenis;
    document.getElementById('modalEdit').classList.remove('hidden');
}
</script>
@endpush
@endsection
