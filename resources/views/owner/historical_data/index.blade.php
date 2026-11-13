@extends('layouts.app')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    .topbar { background-color: white !important; }
</style>
@endpush

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Import Data Historis</h1>
            <p class="text-gray-500 text-sm mt-1">Suntikkan data penjualan perusahaan Anda di masa lalu untuk menghidupkan algoritma SES.</p>
        </div>

        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded shadow-sm">
            <p class="text-green-700 font-bold">{{ session('success') }}</p>
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded shadow-sm">
            <p class="text-red-700 font-bold">{{ session('error') }}</p>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- 1. Upload Excel Card -->
            <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4 border-b pb-4">
                        <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Upload Massal (Excel)</h2>
                            <p class="text-xs text-gray-500">Direkomendasikan untuk ratusan/ribuan baris data.</p>
                        </div>
                    </div>

                    <div class="mb-6 text-sm text-gray-600 space-y-2 bg-gray-50 p-4 rounded-lg border">
                        <p class="font-bold text-gray-700">Langkah-langkah:</p>
                        <ol class="list-decimal pl-4 space-y-1">
                            <li>Download <a href="{{ route('owner.historical.download_template') }}" class="text-blue-600 font-bold hover:underline">Template_Import.csv</a>.</li>
                            <li>Buka Master Barang dan catat ID Barang yang ingin diisi datanya.</li>
                            <li>Isi template CSV dengan rekapan data penjualan masa lalu.</li>
                            <li>Upload file CSV yang sudah diisi ke kotak di bawah ini.</li>
                        </ol>
                    </div>

                    <form action="{{ route('owner.historical.upload_excel') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File (.csv / .xlsx)</label>
                            <input type="file" name="file_excel" accept=".csv, .xlsx, .xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-200 rounded-lg p-2" required>
                            @error('file_excel') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-md flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Mulai Upload & Suntikkan Data
                        </button>
                    </form>
                </div>
            </div>

            <!-- 2. Input Manual Card -->
            <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4 border-b pb-4">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Input Manual (Satu-per-Satu)</h2>
                        <p class="text-xs text-gray-500">Gunakan untuk testing atau input skala kecil.</p>
                    </div>
                </div>

                <form action="{{ route('owner.historical.store_manual') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Tanggal (Mundur)</label>
                        <input type="date" name="tanggal_transaksi" class="w-full border-2 border-gray-200 rounded-lg p-2.5 focus:border-blue-500 outline-none" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cabang</label>
                            <select name="cabang_id" class="w-full border-2 border-gray-200 rounded-lg p-2.5 focus:border-blue-500 outline-none bg-white" required>
                                <option value="">- Pilih Cabang -</option>
                                @foreach($cabangs as $cb)
                                    <option value="{{ $cb->id }}">{{ $cb->nama_cabang }} (ID: {{ $cb->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Barang</label>
                            <select name="barang_id" class="w-full border-2 border-gray-200 rounded-lg p-2.5 focus:border-blue-500 outline-none bg-white" required>
                                <option value="">- Pilih Barang -</option>
                                @foreach($barangs as $br)
                                    <option value="{{ $br->id }}">{{ $br->nama_barang }} (ID: {{ $br->id }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Qty Terjual</label>
                        <input type="number" name="kuantitas" min="1" class="w-full border-2 border-gray-200 rounded-lg p-2.5 focus:border-blue-500 outline-none" required>
                        <p class="text-[10px] text-gray-400 mt-1">*Harga Jual dan Harga Modal (HPP) akan ditarik otomatis dari data Master Barang dan Barang Masuk yang paling pertama (FIFO).</p>
                    </div>

                    <button type="submit" class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-md">
                        Simpan Transaksi Masa Lalu
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
