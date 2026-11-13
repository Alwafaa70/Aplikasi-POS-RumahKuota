@extends('layouts.app')
@section('title', 'Laporan Riwayat Setoran')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { z-index: 50; }
        .main-content { min-width: 0; }
    </style>
@endpush

@section('content')
<div class="p-6">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-black mb-1">📋 Riwayat Setoran</h2>
            <p class="text-gray-500 text-sm">Laporan permintaan dan mutasi setoran Pusat.</p>
        </div>
        <div>
            <a href="{{ route('owner.laporan_pusat.setoran.pdf', request()->all()) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded shadow font-bold hover:bg-red-700">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </a>
            <a href="{{ route('owner.laporan_pusat.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded shadow font-bold hover:bg-gray-300 ml-2">
                Kembali
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-xl shadow mb-6 border">
        <form action="{{ route('owner.laporan_pusat.setoran') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border p-2 rounded text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border p-2 rounded text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Status</label>
                <select name="status" class="border p-2 rounded text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending_admin" {{ request('status') == 'pending_admin' ? 'selected' : '' }}>Pending Admin</option>
                    <option value="pending_owner" {{ request('status') == 'pending_owner' ? 'selected' : '' }}>Pending Owner</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="batal" {{ request('status') == 'batal' ? 'selected' : '' }}>Batal</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-700 text-sm">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded-xl shadow border">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Sumber</th>
                        <th class="p-3">Nominal</th>
                        <th class="p-3">Pengaju</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($setorans as $setoran)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ $setoran->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-3 font-semibold">
                            @if($setoran->pusat_aplikasi_layanan_id === null)
                                <span class="text-green-600">Kas Fisik Pusat</span>
                            @else
                                <span class="text-blue-600">{{ $setoran->dompet->masterAplikasiLayanan->nama_layanan ?? 'Digital' }}</span>
                            @endif
                        </td>
                        <td class="p-3 font-bold">Rp {{ number_format($setoran->nominal, 0, ',', '.') }}</td>
                        <td class="p-3">
                            {{ $setoran->jenis_pengaju === 'owner' ? 'Owner' : 'Admin' }}
                        </td>
                        <td class="p-3">
                            @if($setoran->status === 'pending_admin')
                                <span class="text-yellow-600 font-bold">Pending (Admin)</span>
                            @elseif($setoran->status === 'pending_owner')
                                <span class="text-yellow-600 font-bold">Pending (Owner)</span>
                            @elseif($setoran->status === 'selesai')
                                <span class="text-green-600 font-bold">Selesai</span>
                            @elseif($setoran->status === 'ditolak')
                                <span class="text-red-600 font-bold">Ditolak</span>
                            @else
                                <span class="text-gray-500 font-bold">Batal</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="text-xs text-gray-600">Pengaju: {{ $setoran->catatan_pengaju ?? '-' }}</span><br>
                            <span class="text-xs text-red-600">Tolak: {{ $setoran->catatan_konfirmasi ?? '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-500">Tidak ada data setoran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $setorans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
