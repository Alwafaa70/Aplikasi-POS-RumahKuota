@extends('layouts.app')
@section('title', 'Laporan Aset & Stok Barang')

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
            <h2 class="text-2xl font-black mb-1">📦 Laporan Aset & Stok</h2>
            <p class="text-gray-500 text-sm">Pemantauan stok dan valuasi aset barang berdasarkan modal masuk.</p>
        </div>
        <div>
            <a href="{{ route('owner.laporan_pusat.aset_stok.pdf') }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded shadow font-bold hover:bg-red-700">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </a>
            <a href="{{ route('owner.laporan_pusat.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded shadow font-bold hover:bg-gray-300 ml-2">
                Kembali
            </a>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-indigo-600 text-white p-6 rounded-xl shadow">
            <h3 class="text-sm font-bold opacity-80 uppercase tracking-wider mb-2">Total Nilai Aset Seluruhnya</h3>
            <p class="text-3xl font-black">Rp {{ number_format($semuaAset->sum('nilai_aset'), 0, ',', '.') }}</p>
            <p class="text-xs opacity-75 mt-2">Gudang Pusat & Cabang</p>
        </div>
        <div class="bg-blue-600 text-white p-6 rounded-xl shadow">
            <h3 class="text-sm font-bold opacity-80 uppercase tracking-wider mb-2">Nilai Aset Gudang Pusat</h3>
            <p class="text-3xl font-black">Rp {{ number_format($semuaAset->where('lokasi', 'Gudang Pusat')->sum('nilai_aset'), 0, ',', '.') }}</p>
        </div>
        <div class="bg-teal-600 text-white p-6 rounded-xl shadow">
            <h3 class="text-sm font-bold opacity-80 uppercase tracking-wider mb-2">Nilai Aset Tersebar di Cabang</h3>
            <p class="text-3xl font-black">Rp {{ number_format($semuaAset->where('lokasi', '!=', 'Gudang Pusat')->sum('nilai_aset'), 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow border">
        <h3 class="text-lg font-bold mb-4">Rincian Stok Barang per Lokasi & Batch</h3>
        
        <!-- Form Filter -->
        <form method="GET" action="{{ route('owner.laporan_pusat.aset_stok') }}" class="mb-6 flex flex-wrap gap-4 items-end bg-gray-50 p-4 rounded-lg border">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Filter Lokasi</label>
                <select name="lokasi" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border bg-white">
                    <option value="">Semua Lokasi</option>
                    <option value="pusat" {{ request('lokasi') == 'pusat' ? 'selected' : '' }}>Gudang Pusat</option>
                    @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" {{ request('lokasi') == $cabang->id ? 'selected' : '' }}>Cabang: {{ $cabang->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Urutkan Usia</label>
                <select name="sort" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border bg-white">
                    <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Paling Baru (Terkecil)</option>
                    <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Paling Lama (Terbesar)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">Terapkan</button>
                <a href="{{ route('owner.laporan_pusat.aset_stok') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold ml-2">Reset</a>
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <th class="p-3">Lokasi</th>
                        <th class="p-3">Nama Barang</th>
                        <th class="p-3">Batch / Tgl Masuk</th>
                        <th class="p-3 text-center">Usia Barang</th>
                        <th class="p-3 text-right">Sisa Stok</th>
                        <th class="p-3 text-right">Harga Modal (HPP)</th>
                        <th class="p-3 text-right">Total Nilai Aset</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($semuaAset as $aset)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 font-semibold {{ $aset->lokasi == 'Gudang Pusat' ? 'text-blue-600' : 'text-teal-600' }}">
                            {{ $aset->lokasi }}
                        </td>
                        <td class="p-3 font-bold">{{ $aset->nama_barang }}</td>
                        <td class="p-3 text-xs text-gray-500">
                            {{ $aset->batch_id }}<br>
                            {{ date('d/m/Y', strtotime($aset->tanggal_masuk)) }}
                        </td>
                        <td class="p-3 text-center">
                            @if($aset->usia_hari > 90)
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-bold">{{ $aset->usia_hari }} hari</span>
                            @else
                                {{ $aset->usia_hari }} hari
                            @endif
                        </td>
                        <td class="p-3 text-right font-bold">{{ number_format($aset->stok, 0, ',', '.') }}</td>
                        <td class="p-3 text-right text-gray-600">Rp {{ number_format($aset->hpp, 0, ',', '.') }}</td>
                        <td class="p-3 text-right font-black text-indigo-700">Rp {{ number_format($aset->nilai_aset, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-500">Belum ada data stok/aset.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
