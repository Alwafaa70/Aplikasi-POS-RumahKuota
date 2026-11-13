@extends('layouts.app')
@section('title', 'Pusat Laporan & Historis')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar { z-index: 50; }
        .main-content { min-width: 0; }
    </style>
@endpush

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <!-- HEADER -->
    <div class="mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-800">Pusat Laporan & Historis</h1>
            <p class="text-sm text-gray-500 mt-1">Konsolidasi seluruh log aktivitas operasional dari Admin Gudang dan Kasir Cabang (Akses Read-Only).</p>
        </div>
        <div class="hidden md:block">
            <svg class="w-16 h-16 text-indigo-500 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Riwayat Transaksi Kasir -->
        <a href="{{ route('owner.laporan_pusat.transaksi') }}" class="group block bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-indigo-500 hover:shadow-md transition">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Riwayat Transaksi Kasir</h3>
            <p class="text-sm text-gray-500">Melihat daftar seluruh transaksi penjualan dari kasir tiap cabang beserta akses ke struk pelanggan.</p>
        </a>

        <!-- Riwayat Pembelian Supplier -->
        <a href="{{ route('owner.laporan_pusat.pembelian') }}" class="group block bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-orange-500 hover:shadow-md transition">
            <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-orange-600 group-hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Riwayat Pembelian PO</h3>
            <p class="text-sm text-gray-500">Melihat histori penerimaan barang dari supplier (PO) yang dilakukan oleh Admin Pusat.</p>
        </a>

        <!-- Riwayat Mutasi Stok -->
        <a href="{{ route('owner.laporan_pusat.mutasi') }}" class="group block bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-md transition">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-green-600 group-hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Riwayat Mutasi & Distribusi</h3>
            <p class="text-sm text-gray-500">Melihat histori pergerakan stok barang dari gudang pusat ke cabang atau antar cabang.</p>
        </a>

        <!-- Riwayat Request Operasional -->
        <a href="{{ route('owner.laporan_pusat.request_operasional') }}" class="group block bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-red-500 hover:shadow-md transition">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-red-600 group-hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Riwayat Request Operasional</h3>
            <p class="text-sm text-gray-500">Melihat daftar permintaan bantuan kasir (isi saldo, restok kas, dll) yang diselesaikan Admin.</p>
        </a>

        <!-- Riwayat Laporan Closing -->
        <a href="{{ route('owner.laporan_pusat.closing') }}" class="group block bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-purple-500 hover:shadow-md transition">
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Riwayat Laporan Closing</h3>
            <p class="text-sm text-gray-500">Melihat histori penutupan shift (closing) harian dari setiap cabang (setoran uang kas dsb).</p>
        </a>
        </a>

        <!-- Riwayat Setoran -->
        <a href="{{ route('owner.laporan_pusat.setoran') }}" class="group block bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-pink-500 hover:shadow-md transition">
            <div class="w-12 h-12 bg-pink-100 text-pink-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-pink-600 group-hover:text-white transition">
                <i class="fas fa-money-bill-transfer fa-lg"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Riwayat Setoran</h3>
            <p class="text-sm text-gray-500">Melihat daftar permintaan dan mutasi setoran uang fisik/digital antara Admin dan Owner.</p>
        </a>

        <!-- Keuangan Global -->
        <a href="{{ route('owner.laporan_pusat.keuangan_global') }}" class="group block bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-blue-500 hover:shadow-md transition">
            <div class="w-12 h-12 bg-blue-100 text-blue-700 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-700 group-hover:text-white transition">
                <i class="fas fa-globe fa-lg"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Laporan Keuangan Global</h3>
            <p class="text-sm text-gray-500">Melihat rekapitulasi arus kas fisik dan saldo digital pusat secara global per periode.</p>
        </a>

        <!-- Aset & Stok Barang -->
        <a href="{{ route('owner.laporan_pusat.aset_stok') }}" class="group block bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:border-teal-500 hover:shadow-md transition">
            <div class="w-12 h-12 bg-teal-100 text-teal-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-teal-600 group-hover:text-white transition">
                <i class="fas fa-boxes fa-lg"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Laporan Aset & Stok Barang</h3>
            <p class="text-sm text-gray-500">Melihat perincian jumlah stok dan valuasi total nilai aset barang di Gudang Pusat maupun Cabang.</p>
        </a>


    </div>
</div>
@endsection
