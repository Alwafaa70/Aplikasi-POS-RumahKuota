@extends('layouts.app')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    .topbar { background-color: white !important; }
    [x-cloak] { display: none !important; }
    /* TomSelect Custom Tailwind Tweaks */
    .ts-control { border-radius: 0.5rem; border: 2px solid #e5e7eb; padding: 0.625rem; font-size: 0.875rem; box-shadow: none; }
    .ts-control.focus { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
    .ts-dropdown { border-radius: 0.5rem; font-size: 0.875rem; border-color: #e5e7eb; }
</style>
@endpush

@section('content')
<div class="p-6 bg-gray-50 min-h-screen" x-data="sesForecasting()" x-cloak>
    <div class="max-w-7xl mx-auto">
        <!-- Header Laboratorium Riset Skripsi -->
        <div class="mb-6 flex justify-between items-start flex-wrap gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-indigo-100 text-indigo-800 border border-indigo-200 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                        Engine Riset Utama Skripsi (Bab 3 & Bab 4)
                    </span>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Pemasok Tren Harian ke Buffer EWS
                    </span>
                </div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                    <span>🔬</span> Laboratorium Analisis & Validasi Algoritma SES
                </h1>
                <p class="text-gray-500 text-sm mt-1 max-w-3xl">
                    Pusat pengujian parameter konstanta pemulusan alpha (<code class="font-bold text-gray-700">α = 0.1 – 0.9</code>), evaluasi galat peramalan (MAD, MAPE), dan standardisasi akurasi model menurut kriteria ilmiah <b class="text-gray-800">C.D. Lewis (1982)</b>.
                </p>
            </div>
            <a href="{{ route('owner.ews.index') }}" class="bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white font-bold py-2.5 px-5 rounded-xl flex items-center gap-2 shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Buka Dashboard Monitor Buffer EWS</span>
                <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full font-mono">Hilir</span>
            </a>
        </div>

        <!-- Kartu Metodologi Ilmiah & Landasan Teori Skripsi -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-5 mb-8 shadow-md border border-indigo-900/60">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-indigo-600/30 rounded-xl border border-indigo-400/30 text-2xl shrink-0 hidden sm:block">
                    📐
                </div>
                <div class="w-full">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h3 class="text-sm font-extrabold text-white flex items-center gap-2 tracking-wide uppercase">
                            <span>Landasan Metodologi Ilmiah & Alur Riset Skripsi</span>
                            <span class="text-[10px] bg-indigo-500/40 text-indigo-200 border border-indigo-400/40 px-2.5 py-0.5 rounded-full font-mono tracking-normal normal-case">Standar Bab 3 & 4</span>
                        </h3>
                    </div>
                    <p class="text-xs text-indigo-200 mt-1 leading-relaxed">
                        Halaman ini merupakan <b>laboratorium penelitian</b> untuk membuktikan validitas model Single Exponential Smoothing (SES) sebelum diterapkan secara operasional di sistem bisnis (Buffer EWS).
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 mt-4 text-xs">
                        <div class="bg-white/5 hover:bg-white/10 transition rounded-xl p-3 border border-white/10">
                            <div class="text-indigo-300 font-bold mb-1 font-mono flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                                1. Model Matematis SES
                            </div>
                            <div class="bg-black/30 px-2.5 py-1.5 rounded-lg border border-indigo-500/20 text-indigo-200 font-mono text-[11px] mb-1.5">
                                F<sub>t+1</sub> = α · Y<sub>t</sub> + (1 - α) · F<sub>t</sub>
                            </div>
                            <p class="text-[11px] text-indigo-200/80 leading-normal">
                                Bobot data penjualan masa lalu meluruh secara eksponensial seiring bertambahnya usia data.
                            </p>
                        </div>

                        <div class="bg-white/5 hover:bg-white/10 transition rounded-xl p-3 border border-white/10">
                            <div class="text-indigo-300 font-bold mb-1 font-mono flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                2. Optimasi Parameter (Alpha)
                            </div>
                            <div class="bg-black/30 px-2.5 py-1.5 rounded-lg border border-indigo-500/20 text-emerald-300 font-mono text-[11px] mb-1.5">
                                Grid Search α ∈ {0.1, 0.2, ..., 0.9}
                            </div>
                            <p class="text-[11px] text-indigo-200/80 leading-normal">
                                Parameter optimal dipilih secara ilmiah berdasarkan Mean Absolute Deviation (MAD) terendah.
                            </p>
                        </div>

                        <div class="bg-white/5 hover:bg-white/10 transition rounded-xl p-3 border border-white/10">
                            <div class="text-indigo-300 font-bold mb-1 font-mono flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                3. Evaluasi MAPE (Lewis 1982)
                            </div>
                            <div class="grid grid-cols-2 gap-1 text-[10px] text-indigo-200/90 pt-0.5">
                                <div class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> &lt; 10%: Sangat Akurat</div>
                                <div class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> 10-20%: Baik / Akurat</div>
                                <div class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> 20-50%: Layak / Cukup</div>
                                <div class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> &gt; 50%: Kurang Akurat</div>
                            </div>
                        </div>

                        <div class="bg-white/5 hover:bg-white/10 transition rounded-xl p-3 border border-white/10">
                            <div class="text-indigo-300 font-bold mb-1 font-mono flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                                4. Libur Operasional & Zero Sales
                            </div>
                            <div class="bg-black/30 px-2.5 py-1.5 rounded-lg border border-cyan-500/20 text-cyan-200 font-mono text-[11px] mb-1.5">
                                Y<sub>t</sub> = 0 ⇒ F<sub>t+1</sub> = (1 - α) · F<sub>t</sub>
                            </div>
                            <p class="text-[11px] text-indigo-200/80 leading-normal">
                                Peramalan meluruh bertahap (halus) tanpa crash saat libur; MAD mengevaluasi seluruh hari, MAPE mengevaluasi hari aktif (bebas 0/0).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. Form Filter Dinamis (Otomatis & Bebas Input Manual Safety Stock) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-5 border-b border-gray-100 gap-2">
                <div>
                    <h2 class="text-base font-black text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-indigo-600"></i>
                        <span>Konfigurasi Pengujian Peramalan SES</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pilih cabang, barang, dan target horizon peramalan. Parameter Safety Stock & Batas Kritis EWS diatur secara otomatis oleh Sistem Buffer AI.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold shrink-0 self-start sm:self-auto shadow-2xs">
                    <i class="fa-solid fa-shield-halved text-[11px]"></i>
                    <span>Manajemen Buffer EWS Otomatis</span>
                </span>
            </div>

            <form @submit.prevent="fetchForecast" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                
                <div class="w-full">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-shop text-indigo-500"></i>
                        <span>Pilih Lokasi / Cabang</span>
                    </label>
                    <div wire:ignore>
                        <select x-ref="cabangSelect" class="w-full" required placeholder="-- Cari Cabang --">
                            <option value="">-- Cari Cabang --</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="w-full">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-box-open text-indigo-500"></i>
                        <span>Pilih Barang Fisik</span>
                    </label>
                    <div wire:ignore>
                        <select x-ref="barangSelect" class="w-full" required placeholder="-- Cari Barang --">
                            <option value="">-- Cari Barang --</option>
                            @foreach($barangs as $barang)
                                <option value="{{ $barang->id }}">{{ $barang->nama_barang }} ({{ $barang->tipe_barang }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="w-full">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar text-indigo-500"></i>
                        <span>Target Horizon Peramalan</span>
                    </label>
                    <input type="date" x-model="form.end_date" :min="minEndDate" :max="maxEndDate" @change="calculateRange" class="w-full border-2 border-gray-200 rounded-xl p-2.5 focus:border-indigo-500 focus:outline-none text-sm h-[44px] font-medium" required>
                    <p class="text-[11px] text-gray-500 mt-1 font-sans">Horizon: <span x-text="rangeDays" class="font-black text-indigo-600"></span> Hari ke Depan</p>
                </div>

                <div class="w-full">
                    <button type="submit" class="w-full h-[44px] bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-6 rounded-xl transition transform hover:-translate-y-0.5 active:scale-95 shadow-md flex items-center justify-center gap-2" :disabled="isLoading">
                        <span x-show="!isLoading" class="flex items-center gap-2">
                            <i class="fa-solid fa-flask"></i>
                            <span>Jalankan Uji Lab SES</span>
                        </span>
                        <span x-show="isLoading" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Mengkalkulasi Otomatis...</span>
                        </span>
                    </button>
                    <p class="text-[11px] text-gray-400 mt-1 text-center font-sans">Safety Stock & Batas EWS otomatis</p>
                </div>
            </form>
        </div>

        <!-- Alert Error -->
        <div x-show="errorMessage" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg shadow-sm" style="display: none;">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-medium" x-text="errorMessage"></p>
                </div>
            </div>
        </div>

        <!-- Wrapper Hasil (Tampil jika sudah fetch sukses) -->
        <div x-show="hasData" style="display: none;" x-transition>
            
            <!-- Toolbar Hasil & Aksi Cepat Restok -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6 bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status EWS:</span>
                    <span x-show="result.is_kritis" class="px-3 py-1 rounded-full text-xs font-extrabold bg-red-100 text-red-700 border border-red-300 flex items-center gap-1.5 animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>STOK KRITIS (≤ Batas EWS)</span>
                    </span>
                    <span x-show="!result.is_kritis" class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700 border border-emerald-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>STOK AMAN (&gt; Batas EWS)</span>
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200" x-text="result.klasifikasi_nama"></span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Quick Restock Button (Deep Link) -->
                    <template x-if="result.rekomendasi_restok > 0">
                        <div>
                            <template x-if="result.cabang_id == 0 || !result.cabang_id">
                                <a :href="'{{ route('admin.pembelian.create') }}?barang_id=' + result.barang_id + '&qty=' + result.rekomendasi_restok" 
                                   class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3.5 rounded-xl flex items-center gap-2 shadow-sm transition active:scale-95 text-xs">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    <span>Pesan PO ke Supplier (+<span x-text="result.rekomendasi_restok"></span> Pcs)</span>
                                </a>
                            </template>
                            <template x-if="result.cabang_id != 0 && result.cabang_id">
                                <a :href="'{{ route('distribusi.mutasi.index') }}?tab=mutasi&cabang_asal=&cabang_tujuan=' + result.cabang_id + '&barang_id=' + result.barang_id + '&qty=' + result.rekomendasi_restok" 
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3.5 rounded-xl flex items-center gap-2 shadow-sm transition active:scale-95 text-xs">
                                    <i class="fa-solid fa-truck"></i>
                                    <span>Kirim Mutasi Stok (+<span x-text="result.rekomendasi_restok"></span> Pcs)</span>
                                </a>
                            </template>
                        </div>
                    </template>

                    <form action="{{ route('owner.ses_debugger.export') }}" method="POST" target="_blank">
                        @csrf
                        <input type="hidden" name="cabang_id" x-model="form.cabang_id">
                        <input type="hidden" name="barang_id" x-model="form.barang_id">
                        <input type="hidden" name="range_days" x-model="rangeDays">
                        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-3.5 rounded-xl flex items-center gap-2 shadow-sm transition active:scale-95 text-xs">
                            <i class="fa-solid fa-file-pdf text-red-400"></i>
                            <span>Cetak PDF Laporan SES</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- 2. Summary Cards Akademik & Parameter Otomatis -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <!-- Card 1: Nilai Alpha Terbaik -->
                <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-2xl border border-gray-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
                    <div class="absolute top-0 right-0 p-4 opacity-15"><svg class="w-16 h-16 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Parameter Alpha Optimal</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-xs font-mono font-bold text-gray-400">α =</span>
                            <p class="text-4xl font-black text-indigo-700" x-text="result.best_alpha"></p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Dipilih dari 9 iterasi (0.1 - 0.9)
                        </span>
                        <p class="text-[10px] text-gray-400 mt-0.5">Memiliki tingkat deviasi (MAD) terendah</p>
                    </div>
                </div>
                
                <!-- Card 2: Tingkat Error & Evaluasi Ilmiah Lewis 1982 -->
                <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-2xl border border-gray-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
                    <div class="absolute top-0 right-0 p-4 opacity-15"><svg class="w-16 h-16 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Tingkat Galat (MAD & MAPE)</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <p class="text-4xl font-black text-gray-900"><span x-text="result.terkecil_mad"></span> <span class="text-lg font-bold text-gray-500">Unit</span></p>
                            <p class="text-sm font-bold text-gray-500">(<span class="font-extrabold text-indigo-600" x-text="result.terkecil_mape"></span>%)</p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span :class="getLewisEvaluation(result.terkecil_mape).badge" class="px-2.5 py-1 text-xs font-bold rounded-full border inline-flex items-center gap-1.5 shadow-xs">
                            <span>🎯</span>
                            <span x-text="getLewisEvaluation(result.terkecil_mape).label"></span>
                        </span>
                        <p class="text-[10px] text-gray-500 mt-1" x-text="getLewisEvaluation(result.terkecil_mape).desc"></p>
                    </div>
                </div>
                
                <!-- Card 3: Prediksi Penjualan & Batas EWS Otomatis -->
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl shadow-lg relative overflow-hidden text-white flex flex-col justify-between">
                    <div class="absolute top-0 right-0 p-4 opacity-20"><svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg></div>
                    <div>
                        <p class="text-xs font-bold text-blue-200 uppercase tracking-widest mb-1 text-shadow">Prediksi Penjualan (F<sub>t+1</sub>)</p>
                        <p class="text-4xl font-black drop-shadow-md"><span x-text="result.nilai_peramalan"></span> <span class="text-lg font-medium">Pcs</span></p>
                        <p class="text-xs text-blue-100 mt-1">Estimasi terjual untuk rentang <span class="font-bold underline" x-text="rangeDays"></span> hari ke depan</p>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-blue-500/40 text-xs text-blue-100 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">Laju Tren Harian:</span>
                            <span class="font-mono bg-blue-900/60 px-2 py-0.5 rounded text-white font-bold" x-text="(result.daily_forecast || (result.nilai_peramalan / rangeDays)).toFixed(2) + ' unit/hari'"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-amber-200">Batas Kritis EWS (Otomatis):</span>
                            <span class="font-mono bg-amber-400 text-slate-900 px-2 py-0.5 rounded font-black text-xs" x-text="result.batas_ews + ' Pcs'"></span>
                        </div>
                        <span class="block text-[10px] text-blue-200 mt-0.5 font-sans">Formula: Tren Harian × Lead Time 7 Hari</span>
                    </div>
                </div>

                <!-- Card 4: Rekomendasi Restok (Buffer AI Otomatis) -->
                <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-6 rounded-2xl shadow-lg relative overflow-hidden text-white flex flex-col justify-between">
                    <div class="absolute top-0 right-0 p-4 opacity-20"><svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                    <div>
                        <p class="text-xs font-bold text-emerald-200 uppercase tracking-widest mb-1 text-shadow">Rekomendasi Restok</p>
                        <p class="text-4xl font-black drop-shadow-md"><span x-text="result.rekomendasi_restok"></span> <span class="text-lg font-medium">Pcs</span></p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-emerald-500/40 text-[11px] text-emerald-100 leading-tight font-mono space-y-0.5">
                        <div>[Prediksi: <span x-text="result.nilai_peramalan"></span>] + [Buffer AI (<span x-text="result.safety_stock_percent"></span>%): <span x-text="result.safety_stock"></span>]</div>
                        <div>- [Stok Fisik: <span x-text="result.jumlah_stok"></span>] = <b class="text-white text-xs" x-text="result.rekomendasi_restok"></b> Pcs</div>
                        <div class="pt-1 text-[10px] text-emerald-200 font-sans">
                            Safety Stock dihitung otomatis via FSN Analysis
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Visualisasi Data & Evaluasi Model -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Grafik ApexCharts -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <span>📈</span> Kurva Penjualan Aktual vs Peramalan SES
                        </h3>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md font-mono">
                            Goodness of Fit Test
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mb-6">Melihat tingkat kedekatan garis prediksi (fitted) terhadap fluktuasi garis penjualan aktual per siklus agregasi.</p>
                    <div id="sesChart" class="w-full h-80"></div>
                </div>

                <!-- Tabel Transparansi Alpha & Standar Lewis 1982 -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-bold text-gray-800 flex items-center gap-1.5">
                            <span>🔍</span> Pengujian Grid Search Alpha
                        </h3>
                        <span class="text-[11px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-bold border border-indigo-100 font-mono">
                            α: 0.1 - 0.9
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">Uji komparatif tingkat galat MAD & MAPE beserta kategori kelayakan model.</p>
                    
                    <div class="flex-grow overflow-auto max-h-[380px]">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold text-gray-600 uppercase tracking-wider">Alpha (α)</th>
                                    <th class="px-3 py-2 text-right font-bold text-gray-600 uppercase tracking-wider">MAD</th>
                                    <th class="px-3 py-2 text-right font-bold text-gray-600 uppercase tracking-wider">MAPE</th>
                                    <th class="px-2 py-2 text-center font-bold text-gray-600 uppercase tracking-wider">Lewis 1982</th>
                                    <th class="px-3 py-2 text-right font-bold text-gray-600 uppercase tracking-wider">Prediksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 font-mono">
                                <template x-for="item in result.history_mape" :key="item.alpha">
                                    <tr :class="{'bg-emerald-50/90 font-bold border-l-4 border-emerald-500': item.alpha == result.best_alpha, 'hover:bg-gray-50': item.alpha != result.best_alpha}">
                                        <td class="px-3 py-2 whitespace-nowrap text-gray-900 font-sans">
                                            <span x-text="'α ' + item.alpha"></span>
                                            <span x-show="item.alpha == result.best_alpha" class="ml-1 text-emerald-600 font-bold" title="Alpha Optimal">★</span>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right" :class="{'text-emerald-700 font-bold': item.alpha == result.best_alpha, 'text-gray-600': item.alpha != result.best_alpha}" x-text="item.mad"></td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700" x-text="item.mape + '%'"></td>
                                        <td class="px-2 py-2 whitespace-nowrap text-center font-sans">
                                            <span :class="getLewisEvaluation(item.mape).badge" class="px-1.5 py-0.5 text-[10px] font-bold rounded-full border inline-block" x-text="getLewisEvaluation(item.mape).shortLabel"></span>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-gray-900 font-bold" x-text="item.forecast_next"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 text-[11px] text-gray-500 space-y-1">
                        <div class="flex items-center gap-1.5 text-emerald-700 font-semibold">
                            <span>★</span>
                            <span>Baris hijau (α = <span x-text="result.best_alpha"></span>) dipilih sistem otomatis karena deviasi absolut (MAD) terendah.</span>
                        </div>
                        <div class="text-[10px] text-gray-400 italic">
                            *Kriteria Evaluasi Akurasi: Lewis, C. D. (1982). Industrial and Business Forecasting Methods. London: Butterworth-Heinemann.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Alur Integrasi SES ke Sistem Buffer EWS (Bab 4 & Bab 5 Skripsi) -->
            <div class="mt-8 bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 border-2 border-indigo-200/80 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-indigo-600 text-white rounded-xl shadow-md text-2xl shrink-0">
                            🔗
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-black text-gray-900 uppercase tracking-wide">
                                    Alur Integrasi Hasil Riset SES ke Sistem Operasional Buffer EWS
                                </h4>
                                
                            </div>
                            <p class="text-xs text-gray-600 mt-1 max-w-3xl leading-relaxed">
                                Dari pengujian ini, produk diprediksi terjual <b class="text-gray-900" x-text="result.nilai_peramalan"></b> unit dalam rentang <b class="text-gray-900" x-text="rangeDays"></b> hari (<b class="text-indigo-600" x-text="(result.nilai_peramalan / rangeDays).toFixed(2)"></b> unit/hari). Laju tren harian ini secara otomatis dialirkan ke modul <b>Early Warning System (EWS)</b> untuk mengkalkulasi Batas Stok Kritis secara dinamis (<code class="bg-white px-1.5 py-0.5 rounded border border-gray-300 text-indigo-700 font-mono text-[11px]">Batas EWS = Tren Harian × Lead Time 7 Hari</code>) serta persentase Buffer cadangan adaptif berdasarkan FSN Analysis.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('owner.ews.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs flex items-center gap-2 shadow-md hover:shadow-lg transition">
                            <span>Buka Monitoring Produk di EWS</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Academic Callout: Penanganan Zero-Sales & Hari Libur Toko -->
                <div class="bg-gradient-to-r from-blue-50/70 via-indigo-50/40 to-slate-50 border border-blue-200/80 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-xs">
                            <i class="fa-solid fa-calendar-day"></i>
                        </div>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-wide">
                                    Ketahanan Matematis SES Terhadap Zero-Sales & Hari Libur Toko
                                </h4>
                                <span class="text-[10px] bg-blue-100 text-blue-800 font-black px-2 py-0.5 rounded-full">Sifat Matematis SES</span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Ketika cabang tutup operasional (misal: libur hari raya, <i>day off</i> mingguan, atau stock opname) di mana penjualan bernilai nol (<span class="font-mono font-bold text-slate-800">X<sub>t</sub> = 0</span>), peramalan SES tidak anjlok ke nol atau mengalami <i>crash</i> pembagian nol. Secara matematis: <span class="font-mono font-bold text-indigo-700">F<sub>t+1</sub> = (1 - α) × F<sub>t</sub></span>, nilai peramalan mengalami peredaman eksponensial halus (*smooth exponential decay*). Saat toko beroperasi kembali, nilai peramalan secara adaptif pulih mengikuti bobot aktual terbaru.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('sesForecasting', () => ({
            form: {
                cabang_id: '',
                barang_id: '',
                start_date: '{{ \Carbon\Carbon::now()->format("Y-m-d") }}',
                end_date: '{{ \Carbon\Carbon::now()->addDays(7)->format("Y-m-d") }}'
            },
            minEndDate: '{{ \Carbon\Carbon::now()->addDays(1)->format("Y-m-d") }}',
            maxEndDate: '{{ \Carbon\Carbon::now()->addDays(31)->format("Y-m-d") }}',
            rangeDays: 7,
            isLoading: false,
            hasData: false,
            errorMessage: '',
            result: {},
            chartInstance: null,

            init() {
                this.calculateRange();
                
                this.$nextTick(() => {
                    const tsCabang = new TomSelect(this.$refs.cabangSelect, {
                        create: false,
                        sortField: { field: "text", direction: "asc" },
                        onChange: (val) => { 
                            this.form.cabang_id = val; 
                        }
                    });
                    
                    const tsBarang = new TomSelect(this.$refs.barangSelect, {
                        create: false,
                        sortField: { field: "text", direction: "asc" },
                        onChange: (val) => { 
                            this.form.barang_id = val; 
                        }
                    });

                    // Cek jika dibuka langsung dari link EWS / Monitoring (URL query params)
                    const urlParams = new URLSearchParams(window.location.search);
                    const qCabang = urlParams.get('cabang_id');
                    const qBarang = urlParams.get('barang_id');

                    if (qCabang !== null && qCabang !== '') {
                        tsCabang.setValue(qCabang);
                        this.form.cabang_id = qCabang;
                    }
                    if (qBarang !== null && qBarang !== '') {
                        tsBarang.setValue(qBarang);
                        this.form.barang_id = qBarang;
                    }

                    if (this.form.cabang_id !== '' && this.form.barang_id !== '') {
                        this.fetchForecast();
                    }
                });
            },

            calculateRange() {
                if(!this.form.end_date) return;
                const start = new Date(this.form.start_date);
                const end = new Date(this.form.end_date);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                this.rangeDays = diffDays > 0 ? diffDays : 1;
            },

            getLewisEvaluation(mape) {
                let val = parseFloat(mape);
                if (isNaN(val)) return { label: 'Menghitung...', shortLabel: 'N/A', badge: 'bg-gray-100 text-gray-600 border-gray-300', desc: '-' };
                if (val < 10) {
                    return { 
                        label: 'Sangat Akurat (Lewis 1982)', 
                        shortLabel: 'Sangat Baik',
                        badge: 'bg-emerald-100 text-emerald-800 border-emerald-300', 
                        desc: 'Kemampuan prediksi sangat tinggi dengan galat MAPE < 10% (C.D. Lewis, 1982).' 
                    };
                } else if (val <= 20) {
                    return { 
                        label: 'Baik / Akurat (Lewis 1982)', 
                        shortLabel: 'Baik',
                        badge: 'bg-blue-100 text-blue-800 border-blue-300', 
                        desc: 'Kemampuan prediksi baik dan andal dengan 10% ≤ MAPE ≤ 20% (C.D. Lewis, 1982).' 
                    };
                } else if (val <= 50) {
                    return { 
                        label: 'Layak / Cukup (Lewis 1982)', 
                        shortLabel: 'Layak',
                        badge: 'bg-amber-100 text-amber-800 border-amber-300', 
                        desc: 'Akurasi peramalan wajar / layak dengan 20% < MAPE ≤ 50% (C.D. Lewis, 1982).' 
                    };
                } else {
                    return { 
                        label: 'Kurang Akurat (Lewis 1982)', 
                        shortLabel: 'Kurang Akurat',
                        badge: 'bg-rose-100 text-rose-800 border-rose-300', 
                        desc: 'Tingkat fluktuasi permintaan ekstrem dengan MAPE > 50% (C.D. Lewis, 1982).' 
                    };
                }
            },

            async fetchForecast() {
                this.isLoading = true;
                this.errorMessage = '';
                this.hasData = false;

                try {
                    const response = await fetch(`{{ route('owner.ses_debugger') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            cabang_id: this.form.cabang_id,
                            barang_id: this.form.barang_id,
                            range_days: this.rangeDays
                        })
                    });

                    const data = await response.json();

                    if(response.ok && data.success) {
                        this.result = data;
                        this.hasData = true;
                        
                        // Wait for DOM to show elements before rendering chart
                        this.$nextTick(() => {
                            this.renderChart();
                        });
                    } else {
                        this.errorMessage = data.message || 'Terjadi kesalahan saat mengambil data.';
                    }
                } catch (error) {
                    console.error('Error fetching data:', error);
                    this.errorMessage = 'Terjadi kesalahan koneksi ke server.';
                } finally {
                    this.isLoading = false;
                }
            },

            renderChart() {
                if (this.chartInstance) {
                    this.chartInstance.destroy();
                }

                const options = {
                    series: [
                        {
                            name: 'Data Aktual (Penjualan Nyata)',
                            data: this.result.chart_aktual
                        },
                        {
                            name: 'Prediksi SES (Fitted)',
                            data: this.result.chart_forecast_fitted
                        },
                        {
                            name: 'Prediksi Masa Depan',
                            data: this.result.chart_forecast_future
                        }
                    ],
                    chart: {
                        height: 380,
                        type: 'line',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        },
                        dropShadow: {
                            enabled: true,
                            color: '#000',
                            top: 18,
                            left: 7,
                            blur: 10,
                            opacity: 0.1
                        },
                        toolbar: {
                            show: false
                        }
                    },
                    colors: ['#3b82f6', '#10b981', '#f43f5e'],
                    dataLabels: {
                        enabled: false,
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3,
                        dashArray: [0, 0, 8] // Solid, Solid, Dashed
                    },
                    title: {
                        text: 'Tingkat Kecocokan Garis',
                        align: 'left',
                        style: {
                            fontSize: '12px',
                            color: '#9ca3af',
                            fontWeight: 'normal'
                        }
                    },
                    grid: {
                        borderColor: '#f3f4f6',
                        row: {
                            colors: ['transparent', 'transparent'],
                            opacity: 0.5
                        },
                    },
                    markers: {
                        size: 5,
                        colors: ['#fff'],
                        strokeColors: ['#3b82f6', '#10b981', '#f43f5e'],
                        strokeWidth: 2,
                        hover: {
                            size: 8,
                        }
                    },
                    xaxis: {
                        categories: this.result.chart_labels,
                        title: {
                            text: 'Periode Waktu (Blok ' + this.rangeDays + ' Hari)',
                            style: { fontWeight: 'bold' }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Total Kuantitas Terjual',
                            style: { fontWeight: 'bold' }
                        },
                        labels: {
                            formatter: function (val) {
                                return Math.round(val);
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        floating: true,
                        offsetY: -25,
                        offsetX: -5
                    }
                };

                this.chartInstance = new ApexCharts(document.querySelector("#sesChart"), options);
                this.chartInstance.render();
            }
        }));
    });
</script>
@endsection
