@extends('layouts.app')
@section('title', 'Master Data Cabang & Setup')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .topbar { background-color: white !important; }
</style>
<!-- Tambahkan Alpine JS CDN jika belum diload -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="p-6 bg-gray-50 min-h-screen" x-data="cabangData()">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Master Cabang</h1>
                <p class="text-gray-500 text-sm mt-1">Kelola data cabang dan konfigurasi dompet kasir.</p>
            </div>
            <button @click="openTambahModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-md transition transform hover:-translate-y-0.5">
                + Setup Cabang Baru
            </button>
        </div>

        <!-- Flash Messages will be handled by SweetAlert -->

        <!-- Filters & Search -->
        @php
            $uniqueAreas = $cabangs->pluck('area')->unique();
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex flex-wrap gap-2">
                <button @click="filterArea = 'Semua'" :class="filterArea === 'Semua' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-4 py-2 rounded-lg font-bold text-sm transition">Semua Area</button>
                @foreach($uniqueAreas as $area)
                    <button @click="filterArea = '{{ $area }}'" :class="filterArea === '{{ $area }}' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-4 py-2 rounded-lg font-bold text-sm transition">{{ $area }}</button>
                @endforeach
            </div>
            <div class="w-full md:w-1/3 relative">
                <input type="text" x-model="searchQuery" placeholder="Cari Nama Cabang..." class="w-full border-gray-300 rounded-lg p-2.5 pl-10 focus:ring-blue-500 focus:border-blue-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Cabang</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Area</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Dompet Layanan</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($cabangs as $cabang)
                    <tr class="hover:bg-gray-50 transition" x-show="(filterArea === 'Semua' || filterArea === '{{ $cabang->area }}') && '{{ strtolower(addslashes($cabang->nama_cabang)) }}'.includes(searchQuery.toLowerCase())">
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="font-bold text-gray-900 text-lg">{{ $cabang->nama_cabang }}</div>
                                @if(!$cabang->is_active)
                                    <span class="bg-red-100 text-red-800 text-[10px] font-bold px-2 py-0.5 rounded border border-red-200">NONAKTIF</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $cabang->alamat ?? 'Tidak ada alamat' }}</div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-blue-600">
                            {{ $cabang->area }}
                        </td>
                        <td class="px-6 py-5 text-center whitespace-nowrap">
                            <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-full text-xs font-bold border border-blue-300">
                                {{ $cabang->aplikasiLayanan()->count() }} Dompet Aktif
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right whitespace-nowrap text-sm font-medium flex justify-end gap-2">
                            <form action="{{ route('master.cabang.toggle_active', $cabang->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="{{ $cabang->is_active ? 'text-red-600 hover:text-red-900 bg-red-50 border-red-200' : 'text-green-600 hover:text-green-900 bg-green-50 border-green-200' }} px-3 py-1 rounded-md border text-xs font-bold" onclick="return confirm('Apakah Anda yakin ingin mengubah status cabang ini?')">
                                    {{ $cabang->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            @php
                                $cabangWithLayanan = $cabang->load('aplikasiLayanan');
                            @endphp
                            <button @click="editCabang({{ $cabangWithLayanan->toJson() }})" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md border border-blue-200 text-xs font-bold">Edit</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Belum ada cabang</h3>
                            <p class="text-gray-500 text-sm mt-1">Mulai setup bisnis Anda dengan menekan tombol Tambah Cabang.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal Tambah Cabang & Setup Dinamis -->
        <div x-show="modalOpen" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" @click="modalOpen = false" class="fixed inset-0 transition-opacity backdrop-blur-sm bg-gray-900 bg-opacity-60" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full">
                    <form @submit.prevent="confirmSubmit($event)" :action="editCabangId ? `/master/cabang/${editCabangId}` : '{{ route('master.cabang.store') }}'" method="POST">
                        @csrf
                        <template x-if="editCabangId">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <div class="bg-blue-600 px-6 py-4 flex justify-between items-center shadow-inner">
                            <h3 class="text-xl font-black text-white tracking-wide" x-text="editCabangId ? '✏️ Edit Data Cabang' : '🏢 Setup Cabang & Dompet'">
                            </h3>
                            <button type="button" @click="modalOpen = false" class="text-blue-200 hover:text-white transition">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <div class="bg-gray-50 px-6 pt-5 pb-6">
                            <!-- Data Induk Cabang -->
                            <h4 class="font-bold text-gray-800 border-b-2 border-gray-200 pb-2 mb-4 uppercase text-xs tracking-wider">1. Informasi Dasar Cabang</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Cabang <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="namaCabang" name="nama_cabang" required class="w-full border-2 border-gray-300 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none" placeholder="Cth: Cabang Sudirman">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Area <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="area" name="area" required class="w-full border-2 border-gray-300 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none" placeholder="Cth: Area Selatan">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Alamat Lengkap</label>
                                    <textarea x-model="alamat" name="alamat" rows="2" class="w-full border-2 border-gray-300 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none" placeholder="Jl. Raya..."></textarea>
                                </div>
                                
                                <template x-if="editCabangId">
                                    <div class="md:col-span-2 bg-yellow-50 border border-yellow-200 p-3 rounded-lg flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Status Operasional Cabang</label>
                                            <p class="text-xs text-gray-500">Cabang Nonaktif tidak bisa bertransaksi atau menerima barang.</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" x-model="isActive" value="1" class="sr-only peer">
                                            <!-- Hidden input to ensure false is sent if unchecked -->
                                            <input type="hidden" name="is_active" :value="isActive ? 1 : 0">
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            <span class="ml-3 text-sm font-bold" :class="isActive ? 'text-blue-600' : 'text-gray-500'" x-text="isActive ? 'Aktif' : 'Nonaktif'"></span>
                                        </label>
                                    </div>
                                </template>
                                <template x-if="!editCabangId">
                                    <input type="hidden" name="is_active" value="1">
                                </template>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Modal / Saldo Awal Cash Laci (Uang Fisik) <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 font-bold text-gray-500">Rp</span>
                                        <input type="number" x-model="saldoAwalCash" name="saldo_awal_cash" min="0" :required="!editCabangId" :readonly="editCabangId !== null" :class="editCabangId ? 'bg-gray-100 text-gray-500 border-gray-300' : 'text-green-600 border-gray-300 focus:border-blue-500'" class="w-full border-2 rounded-lg p-2.5 pl-10 focus:outline-none font-bold">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1" x-text="editCabangId ? 'Saldo awal tidak bisa diubah.' : 'Uang modal kembalian kasir di pagi hari.'"></p>
                                </div>
                            </div>

                            <!-- Setup Dompet Dinamis -->
                            <div class="flex justify-between items-end border-b-2 border-gray-200 pb-2 mb-4 mt-8">
                                <h4 class="font-bold text-gray-800 uppercase text-xs tracking-wider">2. Dompet Layanan Kasir (Digital)</h4>
                                <button type="button" @click="tambahDompet()" class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1.5 rounded hover:bg-blue-200 transition shadow-sm border border-blue-200">
                                    + Tambah Dompet
                                </button>
                            </div>
                            
                            <template x-for="(dompet, index) in dompets" :key="dompet.id || dompet.temp_id || index">
                                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm mb-3 relative group transition hover:border-blue-300">
                                    <button type="button" @click="hapusDompet(index)" class="absolute -top-3 -right-3 bg-red-100 text-red-600 p-1.5 rounded-full border border-red-200 shadow-sm hover:bg-red-600 hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    
                                    <input type="hidden" x-bind:name="'layanan['+index+'][id]'" x-model="dompet.id">
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 mb-1">Aplikasi Layanan</label>
                                            <select x-bind:name="'layanan['+index+'][master_aplikasi_layanan_id]'" x-model="dompet.master_id" required :class="dompet.id ? 'bg-gray-200 pointer-events-none text-gray-500' : 'bg-gray-50'" class="w-full border-2 border-gray-300 rounded-md p-2 text-sm focus:border-blue-500 focus:outline-none">
                                                <option value="">-- Pilih --</option>
                                                @foreach($masterLayanan as $master)
                                                    <option value="{{ $master->id }}">{{ $master->nama_layanan }}</option>
                                                @endforeach
                                            </select>
                                            <p x-show="dompet.id" class="text-[10px] text-gray-400 mt-1">Jenis aplikasi terkunci.</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 mb-1">No. Rekening / ID</label>
                                            <input type="text" x-bind:name="'layanan['+index+'][nomor_rekening]'" x-model="dompet.rek" required class="w-full border-2 border-gray-300 rounded-md p-2 text-sm font-mono focus:border-blue-500 focus:outline-none" placeholder="Cth: 08123...">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 mb-1">Saldo Awal</label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-xs font-bold text-gray-500">Rp</span>
                                                <input type="number" x-bind:name="'layanan['+index+'][saldo_awal]'" x-model="dompet.saldo" min="0" :required="!dompet.id" :readonly="dompet.id ? true : false" :class="dompet.id ? 'bg-gray-100 text-gray-500 border-gray-300' : 'text-blue-600 border-gray-300 focus:border-blue-500'" class="w-full border-2 rounded-md p-2 pl-7 text-sm font-bold focus:outline-none">
                                            </div>
                                            <p x-show="dompet.id" class="text-[10px] text-gray-400 mt-1">Terkunci.</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                            <template x-if="dompets.length === 0">
                                <div class="text-center p-6 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300">
                                    <p class="text-sm text-gray-500 font-medium">Belum ada dompet digital ditambahkan.</p>
                                    <button type="button" @click="tambahDompet()" class="mt-2 text-blue-600 font-bold text-sm underline hover:text-blue-800">Tambah sekarang</button>
                                </div>
                            </template>

                        </div>
                        <div class="bg-gray-100 px-6 py-4 flex flex-row-reverse rounded-b-xl border-t border-gray-200">
                            <button type="submit" class="w-full sm:w-auto ml-3 bg-blue-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-md hover:bg-blue-700 transition transform hover:scale-105">
                                Simpan Cabang
                            </button>
                            <button type="button" @click="modalOpen = false" class="w-full sm:w-auto bg-white text-gray-700 font-bold py-2.5 px-6 rounded-lg border-2 border-gray-300 shadow-sm hover:bg-gray-50 transition">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cabangData', () => ({
            filterArea: 'Semua',
            searchQuery: '',
            modalOpen: false,
            editCabangId: null,
            
            namaCabang: '',
            alamat: '',
            area: '',
            saldoAwalCash: '',
            isActive: true,

            dompets: [],

            openTambahModal() {
                this.editCabangId = null;
                this.namaCabang = '';
                this.alamat = '';
                this.area = '';
                this.saldoAwalCash = '';
                this.isActive = true;
                this.dompets = [];
                this.modalOpen = true;
            },

            editCabang(cabang) {
                this.editCabangId = cabang.id;
                this.namaCabang = cabang.nama_cabang;
                this.alamat = cabang.alamat || '';
                this.area = cabang.area;
                this.saldoAwalCash = cabang.saldo_awal_cash;
                this.isActive = cabang.is_active == 1; // SQLite boolean comes as 0/1 usually, or just truthy
                
                this.dompets = [];
                if (cabang.aplikasi_layanan && cabang.aplikasi_layanan.length > 0) {
                    this.dompets = cabang.aplikasi_layanan.map(l => ({
                        id: l.id,
                        master_id: l.master_aplikasi_layanan_id,
                        rek: l.nomor_rekening,
                        saldo: l.saldo_awal,
                        temp_id: Date.now() + Math.random()
                    }));
                }
                this.modalOpen = true;
            },
            
            tambahDompet() {
                this.dompets.push({
                    id: '', // Empty means new
                    master_id: '',
                    rek: '',
                    saldo: 0,
                    temp_id: Date.now() + Math.random()
                });
            },
            
            hapusDompet(index) {
                if (this.dompets[index].id) {
                    Swal.fire({
                        title: 'Yakin Hapus Dompet?',
                        html: 'Menghapus dompet ini akan menghilangkan data saldo aplikasi layanan ini di cabang! Pastikan dompet ini memang sudah tidak digunakan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus Saja',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.dompets.splice(index, 1);
                        }
                    });
                } else {
                    this.dompets.splice(index, 1);
                }
            },

            confirmSubmit(e) {
                // Cek duplikat jenis layanan
                let masterIds = this.dompets.map(d => d.master_id).filter(id => id);
                let uniqueMasterIds = new Set(masterIds);
                if (masterIds.length !== uniqueMasterIds.size) {
                    Swal.fire('Gagal', 'Ada jenis dompet yang duplikat! Tiap cabang hanya boleh memiliki 1 (satu) dompet dari jenis layanan yang sama.', 'error');
                    return;
                }

                Swal.fire({
                    title: this.editCabangId ? 'Konfirmasi Simpan Perubahan' : 'Konfirmasi Setup Cabang',
                    html: this.editCabangId 
                        ? 'Pastikan data perubahan dompet & cabang sudah benar.' 
                        : `Pastikan <b>Saldo Cash Laci</b> dan <b>Saldo Awal Dompet</b> sudah benar.<br><br><span class="text-red-500 font-bold">Saldo awal TIDAK BISA DIUBAH lagi setelah cabang disimpan!</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Data Sudah Benar',
                    cancelButtonText: 'Cek Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        e.target.submit();
                    }
                });
            }
        }))
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'OK'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Tutup'
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                html: `
                    <ul class="text-left list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Tutup'
            });
        @endif
    });
</script>
@endsection
