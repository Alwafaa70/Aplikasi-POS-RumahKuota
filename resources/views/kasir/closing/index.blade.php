@extends('layouts.app')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        
        <div class="bg-gradient-to-r from-red-600 to-red-800 px-6 py-8 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-20 h-20 bg-white opacity-10 rounded-full blur-xl"></div>
            
            <h1 class="text-3xl font-black text-white tracking-wider mb-2 relative z-10">TUTUP SHIFT (CLOSING)</h1>
            <p class="text-red-100 font-medium text-sm relative z-10">Hitung dan masukkan uang fisik secara aktual. Selisih akan otomatis tercatat di sistem.</p>
        </div>

        <form id="formClosing" action="{{ route('kasir.closing.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
            @csrf
            
            <!-- Alert Info -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-8">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-blue-800">Sistem Blind Closing</h3>
                        <p class="text-xs text-blue-600 mt-1">Anda diwajibkan menghitung uang fisik Laci dan Saldo Dompet Digital secara nyata tanpa melihat saldo sistem. Jika terjadi minus uang fisik, akan otomatis tercatat sebagai tanggungan kasir.</p>
                    </div>
                </div>
            </div>

            <!-- Uang Cash Fisik Laci -->
            <div class="mb-10">
                <h2 class="text-lg font-bold text-gray-800 border-b-2 border-gray-200 pb-2 mb-5 flex items-center gap-2 uppercase tracking-wider">
                    <span class="bg-green-100 text-green-700 p-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </span>
                    1. Uang Cash Laci Fisik
                </h2>
                
                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 shadow-inner">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Total Uang Fisik Saat Ini (Termasuk Modal) <span class="text-red-500">*</span></label>
                        <div class="relative mb-4">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <span class="text-gray-500 font-bold text-lg">Rp</span>
                            </div>
                            <input type="number" name="uang_fisik_aktual" min="0" required 
                                   class="w-full pl-12 pr-4 py-3 bg-white border-2 border-gray-200 rounded-xl font-black text-gray-900 text-lg focus:border-red-500 focus:ring-0 transition-colors"
                                   placeholder="0">
                        </div>
                        
                        <label class="block text-sm font-bold text-gray-700 mb-2 mt-4">Foto Bukti Uang Fisik (Opsional)</label>
                        <input type="file" name="bukti_uang_fisik" accept="image/*" 
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 mb-2 border border-gray-200 rounded-xl p-2 bg-white cursor-pointer">

                        <p class="text-xs text-gray-500 mt-2 font-medium"><span class="text-red-500 font-bold">PENTING:</span> Uang fisik ini akan dikurangi Modal Awal Laci, sisa kelebihannya akan otomatis ditarik sebagai Setoran ke Pusat.</p>
                    </div>
            </div>

            <!-- Saldo Dompet Digital -->
            <div class="mb-10">
                <h2 class="text-lg font-bold text-gray-800 border-b-2 border-gray-200 pb-2 mb-5 flex items-center gap-2 uppercase tracking-wider">
                    <span class="bg-blue-100 text-blue-700 p-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </span>
                    2. Cek Saldo Dompet Digital
                </h2>

                <div class="space-y-4">
                    @forelse($apks as $apk)
                        <div class="flex items-center justify-between bg-white border-2 border-gray-200 p-4 rounded-xl hover:border-blue-300 transition-colors shadow-sm">
                            <div class="flex items-center gap-3 w-1/3">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xl border border-blue-100">
                                    {{ substr($apk->masterAplikasiLayanan->nama_layanan, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ $apk->masterAplikasiLayanan->nama_layanan }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $apk->nomor_rekening }}</p>
                                </div>
                            </div>
                            <div class="w-2/3 pl-4">
                                <div class="relative mb-3">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-gray-500 font-bold text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="saldo_apk[{{ $apk->master_aplikasi_layanan_id }}]" min="0" required
                                           class="w-full pl-9 pr-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-lg font-bold text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors"
                                           placeholder="Saldo di HP saat ini...">
                                </div>
                                <div class="relative">
                                    <input type="file" name="bukti_saldo_apk[{{ $apk->master_aplikasi_layanan_id }}]" accept="image/*"
                                           class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-200 rounded-lg p-1 bg-white cursor-pointer"
                                           title="Upload screenshot saldo (opsional)">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 bg-gray-50 rounded-xl border border-gray-200 border-dashed">
                            <p class="text-gray-500 text-sm font-medium">Tidak ada dompet digital aktif di cabang ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Catatan Tambahan -->
            <div class="mb-10">
                <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Laporan (Opsional)</label>
                <textarea name="catatan" rows="3" 
                          class="w-full border-2 border-gray-300 rounded-xl p-4 text-sm focus:ring-0 focus:border-red-500 transition-colors bg-gray-50 focus:bg-white"
                          placeholder="Tuliskan catatan jika ada hal penting selama shift..."></textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('kasir.dashboard') }}" class="px-6 py-3 text-sm font-bold text-gray-600 hover:text-gray-900 transition-colors">
                    Kembali
                </a>
                <button type="submit" id="btnSubmit" class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    SUBMIT & CLOSING
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
    document.getElementById('formClosing').addEventListener('submit', function(e) {
        let btn = document.getElementById('btnSubmit');
        
        let confirmSubmit = confirm('Apakah data fisik yang Anda masukkan sudah benar dan sesuai?\n\nPastikan tidak ada kesalahan sebelum melakukan submit. Segala bentuk kelebihan maupun kekurangan akan dipertanggungjawabkan.');
        
        if (confirmSubmit) {
            btn.disabled = true;
            btn.innerHTML = 'Memproses...';
        } else {
            e.preventDefault();
        }
    });
</script>
@endsection
