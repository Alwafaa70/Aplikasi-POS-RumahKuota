@extends('layouts.app')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Dashboard Monitoring Aset</h1>

    <!-- Filter Form -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form action="{{ route('admin.monitoring.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-1/3">
                <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi Aset</label>
                <select name="lokasi" id="lokasi" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="all" {{ $lokasi === 'all' ? 'selected' : '' }}>Semua Lokasi (Pusat + Cabang)</option>
                    <option value="pusat" {{ $lokasi === 'pusat' ? 'selected' : '' }}>Hanya Gudang/Pusat</option>
                    @foreach($cabangList as $cabang)
                        <option value="{{ $cabang->id }}" {{ $lokasi == $cabang->id ? 'selected' : '' }}>Cabang: {{ $cabang->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-1/3">
                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori Aset</label>
                <select name="kategori" id="kategori" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="all" {{ $kategori === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    <option value="barang_fisik" {{ $kategori === 'barang_fisik' ? 'selected' : '' }}>Barang Fisik Saja</option>
                    <option value="cash" {{ $kategori === 'cash' ? 'selected' : '' }}>Uang Cash Saja</option>
                    <option value="saldo_digital" {{ $kategori === 'saldo_digital' ? 'selected' : '' }}>Saldo Digital Saja</option>
                </select>
            </div>

            <div class="w-full md:w-1/3">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        @if($kategori === 'all' || $kategori === 'barang_fisik')
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-indigo-500">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Kuantitas Fisik</h3>
            <p class="mt-2 text-3xl font-extrabold text-gray-900">{{ number_format($totalQtyFisik, 0, ',', '.') }} Pcs</p>
        </div>
        @endif

        @if($kategori === 'all' || $kategori === 'cash')
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Uang Cash Fisik</h3>
            <p class="mt-2 text-3xl font-extrabold text-gray-900">Rp {{ number_format($totalCash, 0, ',', '.') }}</p>
        </div>
        @endif

        @if($kategori === 'all' || $kategori === 'saldo_digital')
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Saldo Apk Layanan</h3>
            <p class="mt-2 text-3xl font-extrabold text-gray-900">Rp {{ number_format($totalSaldoDigital, 0, ',', '.') }}</p>
        </div>
        @endif
        
    </div>

    <!-- Total Asset Values (Always show if category is all) -->
    @if($kategori === 'all')
    <div class="bg-gray-50 p-6 rounded-lg shadow-inner border border-gray-200">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Estimasi Total Aset (Rupiah)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-4 rounded-md shadow flex flex-col justify-center items-center">
                <span class="text-gray-500 text-sm font-medium mb-1">Total Aset (Berdasarkan HPP / Harga Modal)</span>
                <span class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalAsetModal, 0, ',', '.') }}</span>
                <span class="text-xs text-gray-400 mt-2 text-center">Rumus: (Qty Fisik * HPP Rata-rata) + Cash + Saldo Digital</span>
            </div>
            
            <div class="bg-white p-4 rounded-md shadow flex flex-col justify-center items-center">
                <span class="text-gray-500 text-sm font-medium mb-1">Total Aset (Berdasarkan Estimasi Harga Jual)</span>
                <span class="text-2xl font-bold text-green-600">Rp {{ number_format($totalAsetJual, 0, ',', '.') }}</span>
                <span class="text-xs text-gray-400 mt-2 text-center">Rumus: (Qty Fisik * Harga Jual) + Cash + Saldo Digital</span>
            </div>
        </div>
    </div>
    @elseif($kategori === 'barang_fisik')
    <div class="bg-gray-50 p-6 rounded-lg shadow-inner border border-gray-200">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Estimasi Nilai Barang Fisik (Rupiah)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-4 rounded-md shadow flex flex-col justify-center items-center">
                <span class="text-gray-500 text-sm font-medium mb-1">Berdasarkan HPP / Harga Modal</span>
                <span class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalNilaiFisikModal, 0, ',', '.') }}</span>
            </div>
            
            <div class="bg-white p-4 rounded-md shadow flex flex-col justify-center items-center">
                <span class="text-gray-500 text-sm font-medium mb-1">Berdasarkan Estimasi Harga Jual</span>
                <span class="text-2xl font-bold text-green-600">Rp {{ number_format($totalNilaiFisikJual, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
