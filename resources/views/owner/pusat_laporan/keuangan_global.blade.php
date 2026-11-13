@extends('layouts.app')
@section('title', 'Laporan Keuangan Global')

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
            <h2 class="text-2xl font-black mb-1">🌍 Laporan Keuangan Global</h2>
            <p class="text-gray-500 text-sm">Rekapitulasi perputaran arus kas Pusat (Fisik dan Digital).</p>
        </div>
        <div>
            <a href="{{ route('owner.laporan_pusat.keuangan_global.pdf', request()->all()) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded shadow font-bold hover:bg-red-700">
                <i class="fas fa-file-pdf"></i> Cetak Rincian (PDF)
            </a>
            <a href="{{ route('owner.laporan_pusat.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded shadow font-bold hover:bg-gray-300 ml-2">
                Kembali
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-xl shadow mb-6 border">
        <form action="{{ route('owner.laporan_pusat.keuangan_global') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="border p-2 rounded text-sm" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="border p-2 rounded text-sm" required>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-700 text-sm">
                    Tampilkan
                </button>
            </div>
        </form>
    </div>

    <!-- Ringkasan Keuangan Keseluruhan -->
    <h3 class="text-xl font-black text-gray-800 mb-4 border-b-2 border-gray-200 pb-2">Neraca Total (Fisik + Semua Digital)</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-800 text-white p-5 rounded-xl shadow">
            <h4 class="text-xs uppercase tracking-wide opacity-75 mb-1">Total Saldo Awal</h4>
            <p class="text-2xl font-bold">Rp {{ number_format($kasFisikAwal + $totalDompetAwal, 0, ',', '.') }}</p>
            <p class="text-[10px] opacity-75 mt-1">Sisa sebelum {{ date('d/m/Y', strtotime($startDate)) }}</p>
        </div>
        <div class="bg-green-600 text-white p-5 rounded-xl shadow">
            <h4 class="text-xs uppercase tracking-wide opacity-75 mb-1">Total Uang Masuk</h4>
            <p class="text-2xl font-bold">+ Rp {{ number_format($pemasukanFisik + $totalDompetPemasukan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-red-600 text-white p-5 rounded-xl shadow">
            <h4 class="text-xs uppercase tracking-wide opacity-75 mb-1">Total Uang Keluar</h4>
            <p class="text-2xl font-bold">- Rp {{ number_format($pengeluaranFisik + $totalDompetPengeluaran, 0, ',', '.') }}</p>
        </div>
        <div class="bg-blue-700 text-white p-5 rounded-xl shadow border-2 border-blue-400">
            <h4 class="text-xs uppercase tracking-wide opacity-75 mb-1">Total Saldo Akhir</h4>
            <p class="text-2xl font-bold">Rp {{ number_format($kasFisikAkhir + $totalDompetAkhir, 0, ',', '.') }}</p>
            <p class="text-[10px] opacity-75 mt-1">Saldo pada {{ date('d/m/Y', strtotime($endDate)) }}</p>
        </div>
    </div>

    <!-- Kas Fisik -->
    <h3 class="text-xl font-black text-green-700 mb-4 border-b-2 border-green-200 pb-2">Arus Kas Fisik (Uang Tunai)</h3>
    <div class="bg-white p-6 rounded-xl shadow border border-green-100 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-gray-50 p-4 rounded text-center border">
                <p class="text-xs text-gray-500 font-bold">Saldo Awal Fisik</p>
                <p class="text-lg font-bold text-gray-800">Rp {{ number_format($kasFisikAwal, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded text-center border border-green-200">
                <p class="text-xs text-green-600 font-bold">Uang Masuk Fisik</p>
                <p class="text-lg font-bold text-green-700">+ Rp {{ number_format($pemasukanFisik, 0, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded text-center border border-red-200">
                <p class="text-xs text-red-600 font-bold">Uang Keluar Fisik</p>
                <p class="text-lg font-bold text-red-700">- Rp {{ number_format($pengeluaranFisik, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-100 p-4 rounded text-center border border-green-300">
                <p class="text-xs text-green-800 font-bold">Saldo Akhir Fisik</p>
                <p class="text-lg font-bold text-green-800">Rp {{ number_format($kasFisikAkhir, 0, ',', '.') }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-500 italic">* Rincian transaksinya (seperti setoran closing cabang, bantuan fisik kasir) akan terlihat lengkap di cetakan PDF.</p>
    </div>

    <!-- Dompet Digital -->
    <h3 class="text-xl font-black text-blue-700 mb-4 border-b-2 border-blue-200 pb-2">Arus Saldo Dompet Digital</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($rekapDompet as $dompet)
        <div class="bg-white p-6 rounded-xl shadow border border-blue-100">
            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                <div>
                    <h4 class="font-black text-lg text-gray-800">{{ $dompet->nama }}</h4>
                    <p class="text-xs text-gray-500 font-mono">{{ $dompet->rekening }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-gray-500">Saldo Akhir</p>
                    <p class="text-xl font-black text-blue-700">Rp {{ number_format($dompet->akhir, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-sm">
                <div class="bg-gray-50 p-2 rounded">
                    <p class="text-[10px] font-bold text-gray-500">Awal</p>
                    <p class="font-semibold">{{ number_format($dompet->awal, 0, ',', '.') }}</p>
                </div>
                <div class="bg-green-50 p-2 rounded text-green-700">
                    <p class="text-[10px] font-bold text-green-600">Masuk</p>
                    <p class="font-semibold">+{{ number_format($dompet->masuk, 0, ',', '.') }}</p>
                </div>
                <div class="bg-red-50 p-2 rounded text-red-700">
                    <p class="text-[10px] font-bold text-red-600">Keluar</p>
                    <p class="font-semibold">-{{ number_format($dompet->keluar, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
