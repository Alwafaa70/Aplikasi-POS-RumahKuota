@extends('layouts.app')
@section('title', 'Manajemen Distribusi, Mutasi & Aset Gudang')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Styling select2 agar cocok dengan desain form-control */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        padding: 5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        color: #555 !important;
    }

    .widget-container { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
    .widget { flex: 1; min-width: 250px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid var(--primary); }
    .widget-title { font-size: 13px; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
    .widget-value { font-size: 24px; font-weight: bold; color: #2c3e50; }
    
    .table-container { background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow-x: auto; padding: 20px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f8f9fa; font-weight: bold; color: #34495e; text-transform: uppercase; font-size: 12px; }
    tr:hover { background: #fdfdfd; }
    
    .positive { color: #27ae60; font-weight: bold; }

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; color: #34495e; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
    .btn-submit { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; }
    .btn-submit:hover { background: #2980b9; }

    /* Accordion Cabang */
    .cabang-card { background: white; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #ddd; margin-bottom: 10px; }
    .cabang-header { padding: 15px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; background: #fafafa; border-radius: 6px; }
    .cabang-title { font-weight: bold; font-size: 16px; color: #2c3e50; }
    .cabang-content { padding: 15px; border-top: 1px solid #eee; display: none; }

    /* Custom Tabs */
    .tab-nav-container { display: flex; gap: 5px; margin-bottom: 20px; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow-x: auto; }
    .tab-btn { padding: 10px 15px; font-weight: bold; cursor: pointer; border-radius: 6px; white-space: nowrap; transition: background 0.2s, color 0.2s; color: #7f8c8d; }
    .tab-btn:hover { background: #f8f9fa; }
    .tab-btn.active { background: #3498db; color: white; }
    .tab-btn.active-green { background: #27ae60; color: white; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background: #f8d7da; color: #721c24; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px;">
        {{ session('error') }}
    </div>
@endif

<div x-data="{ 
    tab: '{{ request('tab', (request('mutasi_uang') ? 'mutasi_uang' : (request('request_id') || request('barang_id') || request('cabang_tujuan') ? 'mutasi' : 'aset'))) }}', 
    search: '' 
}">
    <div class="tab-nav-container">
        <div class="tab-btn" :class="{ 'active': tab === 'aset' }" @click="tab = 'aset'">Monitoring Aset & Stok Pusat</div>
        <div class="tab-btn" :class="{ 'active': tab === 'mutasi' }" @click="tab = 'mutasi'">Kirim Mutasi Stok</div>
        <div class="tab-btn" :class="{ 'active-green': tab === 'mutasi_uang' }" @click="tab = 'mutasi_uang'">Kirim Uang Fisik</div>
        <div class="tab-btn" :class="{ 'active': tab === 'draft' }" @click="tab = 'draft'" style="display: flex; align-items: center; gap: 5px;">
            Draft Mutasi 
            @if($draftMutasiStok->count() > 0)
                <span style="background: #e74c3c; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px;">{{ $draftMutasiStok->count() }}</span>
            @endif
        </div>

        <div class="tab-btn" :class="{ 'active': tab === 'cabang' }" @click="tab = 'cabang'">Monitoring Aset Cabang</div>
    </div>

    <!-- TAB: ASET GUDANG PUSAT -->
    <!-- TAB: ASET GUDANG PUSAT -->
    <div x-show="tab === 'aset'"
         x-data="{ 
            items: {{ json_encode($stokPusatGrouped ?? []) }},
            searchPusat: '',
            filterTipePusat: '',
            sortOrderPusat: 'terbaru',
            get filteredAndSortedPusat() {
                let result = this.items.filter(i => 
                    (this.searchPusat === '' || i.nama_barang.toLowerCase().includes(this.searchPusat.toLowerCase())) && 
                    (this.filterTipePusat === '' || i.tipe_barang === this.filterTipePusat)
                );
                
                if (this.sortOrderPusat === 'terlama') {
                    result.sort((a, b) => new Date(a.oldest_batch_date) - new Date(b.oldest_batch_date));
                } else if (this.sortOrderPusat === 'terbaru') {
                    result.sort((a, b) => new Date(b.newest_batch_date) - new Date(a.newest_batch_date));
                }
                
                return result;
            }
         }">
        
        <div class="widget-container">
            <div class="widget" style="border-left-color: #3498db; background: #ebf5fb;">
                <div class="widget-title" style="color: #2980b9;">Total Aset (Harga Modal)</div>
                <div class="widget-value" style="color: #2c3e50;">Rp {{ number_format($totalAsetModalPusat ?? 0, 0, ',', '.') }}</div>
            </div>
            
            <div class="widget" style="border-left-color: #2ecc71; background: #eafaf1;">
                <div class="widget-title" style="color: #27ae60;">Total Aset (Potensi Harga Jual)</div>
                <div class="widget-value" style="color: #27ae60;">Rp {{ number_format($totalAsetJualPusat ?? 0, 0, ',', '.') }}</div>
            </div>
            
            <div class="widget" style="border-left-color: #e67e22; background: #fdf2e9;">
                <div class="widget-title" style="color: #d35400;">Potensi Laba Kotor</div>
                <div class="widget-value" style="color: #d35400;">Rp {{ number_format(($totalAsetJualPusat ?? 0) - ($totalAsetModalPusat ?? 0), 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="table-container" style="padding: 20px;">
            <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; align-items: center; background: #fdfdfd; border: 1px solid #eee; padding: 15px; border-radius: 8px;">
                <input type="text" x-model="searchPusat" placeholder="Cari nama barang..." style="padding: 10px; flex: 1; min-width: 200px; border: 1px solid #ddd; border-radius: 6px;">
                <select x-model="filterTipePusat" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-width: 150px;">
                    <option value="">Semua Tipe Barang</option>
                    <option value="Fisik ACC">Fisik ACC</option>
                    <option value="Fisik Voucher Kartu">Fisik Voucher Kartu</option>
                </select>
                <select x-model="sortOrderPusat" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-width: 150px;">
                    <option value="terbaru">Terbaru (Berdasarkan Batch)</option>
                    <option value="terlama">Terlama (Berdasarkan Batch)</option>
                </select>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 15px; border-bottom: 2px solid #eee; text-align: left; color: #34495e;">Nama Barang</th>
                        <th style="padding: 15px; border-bottom: 2px solid #eee; text-align: center; color: #34495e;">Total Stok Fisik</th>
                        <th style="padding: 15px; border-bottom: 2px solid #eee; text-align: center; color: #34495e;">Total Aset Modal</th>
                        <th style="padding: 15px; border-bottom: 2px solid #eee; text-align: center; color: #34495e;">Potensi Laba</th>
                        <th style="padding: 15px; border-bottom: 2px solid #eee; text-align: center; color: #34495e;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in filteredAndSortedPusat" :key="item.barang_id">
                        <tr :style="item.is_kritis ? 'background-color: #fff5f5; border-bottom: 1px solid #ffe3e3;' : 'border-bottom: 1px solid #eee;'">
                            <td style="padding: 15px;">
                                <div style="font-weight: bold; font-size: 14px;" :style="item.is_kritis ? 'color: #c0392b;' : 'color: #2c3e50;'">
                                    <span x-text="item.nama_barang"></span>
                                    <template x-if="item.is_kritis">
                                        <button type="button" @click="$dispatch('load-ews-pusat-modal', { item: item })" style="margin-left: 8px; font-size: 10px; background: #e74c3c; color: white; padding: 2px 8px; border-radius: 4px; font-weight: bold; border: none; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#c0392b'" onmouseout="this.style.background='#e74c3c'">⚠ KRITIS</button>
                                    </template>
                                </div>
                                <div style="font-size: 11px; margin-top: 4px;">
                                    <span :style="item.is_kritis ? 'color: #e74c3c;' : 'color: #7f8c8d;'" x-text="item.kategori"></span>
                                </div>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: bold; font-size: 14px;" :style="item.is_kritis ? 'color: #c0392b;' : 'color: #2980b9;'" x-text="new Intl.NumberFormat('id-ID').format(item.total_stok)"></td>
                            <td style="padding: 15px; text-align: center; font-size: 14px; color: #7f8c8d;" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.total_aset_modal)"></td>
                            <td style="padding: 15px; text-align: center; font-weight: bold; font-size: 14px; color: #27ae60;" x-text="'+ Rp ' + new Intl.NumberFormat('id-ID').format(item.potensi_laba)"></td>
                            <td style="padding: 15px; text-align: center;">
                                <button type="button" @click="$dispatch('load-pusat-modal', { batches: item.batches, barangName: item.nama_barang })" style="background: #e8f4f8; color: #2980b9; border: 1px solid #bce0fd; padding: 6px 12px; border-radius: 6px; font-weight: bold; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Detail Batch
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredAndSortedPusat.length === 0">
                        <td colspan="5" style="padding: 30px; text-align: center; color: #95a5a6;">Belum ada barang di Gudang Pusat yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    <!-- TAB: KIRIM MUTASI -->
    <div x-show="tab === 'mutasi'" style="display: none;" 
         x-data="{ 
            cabangs: {{ json_encode($cabangs->map(fn($c) => ['id' => $c->id, 'nama' => $c->nama_cabang])) }},
            stokPusat: {{ json_encode($stokMutasiPusat) }},
            stokCache: { '': {{ json_encode($stokMutasiPusat) }} },
            mutasiList: {{ (isset($requestItems) && count($requestItems) > 0 && !request('mutasi_uang')) ? json_encode($requestItems) : '[{ id: Date.now(), cabang_asal_id: "", cabang_tujuan_id: "", barang_id: "", qty: 1 }]' }},
            
            async fetchStok(asalId) {
                if (this.stokCache[asalId] !== undefined) return this.stokCache[asalId];
                try {
                    let res = await fetch('{{ route('api.stok_asal') }}/' + (asalId || ''));
                    let json = await res.json();
                    this.stokCache[asalId] = json.data;
                    return json.data;
                } catch(e) {
                    return [];
                }
            },
            async onAsalChange(item) {
                item.barang_id = '';
                await this.fetchStok(item.cabang_asal_id);
            },
            getStokOptions(asalId) {
                return this.stokCache[asalId] || [];
            },
            tambahBaris() {
                this.mutasiList.push({ id: Date.now(), cabang_asal_id: '', cabang_tujuan_id: '', barang_id: '', qty: 1 });
            },
            hapusBaris(index) {
                this.mutasiList.splice(index, 1);
                if (this.mutasiList.length === 0) this.tambahBaris();
            },
            submitForm(action) {
                document.getElementById('mutasi_action').value = action;
                document.getElementById('mutasi_items_payload').value = JSON.stringify(this.mutasiList);
                document.getElementById('form_mutasi').submit();
            },
            init() {
                // preload all stocks needed if prefilled
                let originalList = JSON.parse(JSON.stringify(this.mutasiList));
                this.mutasiList.forEach((item, index) => {
                    this.fetchStok(item.cabang_asal_id).then(() => {
                        this.$nextTick(() => {
                            this.mutasiList[index].barang_id = originalList[index].barang_id;
                        });
                    });
                });
            }
         }">
        <style>
            .custom-dropdown-menu { position: absolute; z-index: 50; width: 100%; margin-top: 4px; background-color: white; border: 1px solid #ccc; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); max-height: 250px; overflow-y: auto; text-align: left; }
            .custom-dropdown-header { position: sticky; top: 0; background: white; padding: 8px; border-bottom: 1px solid #eee; z-index: 60; }
            .custom-dropdown-item { padding: 8px 12px; font-size: 13px; cursor: pointer; border-bottom: 1px solid #f9f9f9; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s; }
            .custom-dropdown-item:hover:not(.disabled) { background-color: #f0f7ff; }
            .custom-dropdown-item.active { background-color: #e6f2ff; font-weight: bold; color: #2980b9; }
            .custom-dropdown-item.disabled { opacity: 0.5; cursor: not-allowed; background-color: #fafafa; }
            .search-input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; outline: none; font-size: 13px; box-sizing: border-box; }
            .search-input:focus { border-color: #3498db; }
            .filter-btn-group { display: flex; gap: 5px; margin-bottom: 8px; }
            .filter-btn { padding: 4px 10px; font-size: 11px; border-radius: 12px; border: 1px solid #ddd; background: #f9f9f9; cursor: pointer; color: #555; }
            .filter-btn.active { background: #3498db; color: white; border-color: #3498db; font-weight: bold; }
            .badge-stok { font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: bold; }
            .badge-stok.ada { background: #d4edda; color: #155724; }
            .badge-stok.habis { background: #f8d7da; color: #721c24; }
        </style>
        <div class="card" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="padding: 10px; background: #e6f2ff; color: #2980b9; border-radius: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" style="height: 24px; width: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    </div>
                    <div>
                        <h3 style="font-size: 20px; font-weight: bold; color: #2c3e50; margin: 0;">Kirim Mutasi Stok (Multi-Item)</h3>
                        <p style="font-size: 14px; color: #7f8c8d; margin: 5px 0 0 0;">Kirim berbagai barang dari/ke cabang yang berbeda dalam satu kali proses.</p>
                    </div>
                </div>
                <button type="button" @click="tambahBaris()" class="btn-submit" style="background: #27ae60; color: white; padding: 10px 15px; border-radius: 5px; border: none; display: flex; align-items: center; gap: 5px; cursor: pointer; font-weight: bold;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Baris
                </button>
            </div>
            
            <form id="form_mutasi" method="POST" action="{{ route('admin.mutasi.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="action" id="mutasi_action" value="kirim">
                <input type="hidden" name="mutasi_items" id="mutasi_items_payload" value="[]">
                
                @if(request('request_id'))
                    <input type="hidden" name="request_id" value="{{ request('request_id') }}">
                    <div style="background: #e8f8f5; color: #27ae60; padding: 10px; border-radius: 6px; border-left: 4px solid #2ecc71; margin-bottom: 15px;">
                        ⚠️ <b>Mode Persetujuan Request Operasional:</b> Mutasi stok ini akan terhubung secara otomatis untuk menyelesaikan Request #{{ request('request_id') }}. Semua barang yang diminta kasir sudah otomatis terisi di bawah. Anda bisa menyesuaikan kuantitas atau lokasi asalnya.
                    </div>
                @elseif(request('barang_id') || request('cabang_tujuan'))
                    <div style="background: #eff6ff; color: #1e40af; padding: 12px 16px; border-radius: 8px; border-left: 4px solid #3b82f6; margin-bottom: 15px; font-size: 13px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            ⚡ <b>Akses Cepat Restok EWS:</b> Formulir mutasi stok telah otomatis diisi untuk cabang tujuan dan produk yang dipilih sesuai rekomendasi buffer EWS. Anda dapat langsung menyimpan draft atau memproses pengiriman.
                        </div>
                        <a href="{{ route('distribusi.mutasi.index', ['tab' => 'mutasi']) }}" style="font-size: 11px; color: #2563eb; text-decoration: underline; white-space: nowrap; margin-left: 12px;">Reset Form</a>
                    </div>
                @endif
                
                <div class="table-container" style="padding: 0; box-shadow: none; border: 1px solid #eee; overflow: visible; border-radius: 8px;">
                    <table style="margin: 0; width: 100%; border-collapse: collapse;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="width: 25%;">Lokasi Asal</th>
                                <th style="width: 25%;">Lokasi Tujuan</th>
                                <th style="width: 30%;">Barang Fisik & Stok</th>
                                <th style="width: 15%;">Qty</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in mutasiList" :key="item.id">
                                <tr>
                                    <td>
                                        <div x-data="{ 
                                            open: false, 
                                            search: '',
                                            get filtered() {
                                                if(this.search === '') return cabangs;
                                                return cabangs.filter(c => c.nama.toLowerCase().includes(this.search.toLowerCase()));
                                            },
                                            get selectedName() {
                                                if (!item.cabang_asal_id) return 'Gudang Pusat (Utama)';
                                                let c = cabangs.find(c => c.id == item.cabang_asal_id);
                                                return c ? c.nama : 'Gudang Pusat (Utama)';
                                            }
                                        }" class="relative" style="position: relative;" @click.away="open = false">
                                            <div @click="open = !open" class="form-control" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: white;">
                                                <span x-text="selectedName" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 13px;"></span>
                                                <svg style="width: 16px; height: 16px; color: #888;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                            <div x-show="open" class="custom-dropdown-menu" style="display: none;">
                                                <div class="custom-dropdown-header">
                                                    <input type="text" x-model="search" placeholder="Cari cabang..." class="search-input" @click.stop>
                                                </div>
                                                <div @click="item.cabang_asal_id = ''; onAsalChange(item); open = false" class="custom-dropdown-item" :class="item.cabang_asal_id == '' ? 'active' : ''">
                                                    Gudang Pusat (Utama)
                                                </div>
                                                <template x-for="cab in filtered" :key="cab.id">
                                                    <div @click="item.cabang_asal_id = cab.id; onAsalChange(item); open = false" class="custom-dropdown-item" :class="item.cabang_asal_id == cab.id ? 'active' : ''" x-text="cab.nama"></div>
                                                </template>
                                                <div x-show="filtered.length === 0" style="padding: 10px; font-size: 12px; color: #888; text-align: center;">Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div x-data="{ 
                                            open: false, 
                                            search: '',
                                            get filtered() {
                                                let list = cabangs.filter(c => c.id != item.cabang_asal_id);
                                                if(this.search === '') return list;
                                                return list.filter(c => c.nama.toLowerCase().includes(this.search.toLowerCase()));
                                            },
                                            get selectedName() {
                                                if (!item.cabang_tujuan_id) return '-- Pilih Tujuan --';
                                                let c = cabangs.find(c => c.id == item.cabang_tujuan_id);
                                                return c ? c.nama : '-- Pilih Tujuan --';
                                            }
                                        }" class="relative" style="position: relative;" @click.away="open = false">
                                            <div @click="open = !open" class="form-control" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: white;">
                                                <span x-text="selectedName" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 13px;"></span>
                                                <svg style="width: 16px; height: 16px; color: #888;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                            <div x-show="open" class="custom-dropdown-menu" style="display: none;">
                                                <div class="custom-dropdown-header">
                                                    <input type="text" x-model="search" placeholder="Cari tujuan..." class="search-input" @click.stop>
                                                </div>
                                                <template x-for="cab in filtered" :key="cab.id">
                                                    <div @click="item.cabang_tujuan_id = cab.id.toString(); open = false" class="custom-dropdown-item" :class="item.cabang_tujuan_id == cab.id ? 'active' : ''" x-text="cab.nama"></div>
                                                </template>
                                                <div x-show="filtered.length === 0" style="padding: 10px; font-size: 12px; color: #888; text-align: center;">Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div x-data="{ 
                                            open: false, 
                                            search: '',
                                            kategori: '',
                                            get options() { return getStokOptions(item.cabang_asal_id); },
                                            get filtered() {
                                                let list = this.options;
                                                if(this.kategori !== '') list = list.filter(o => o.tipe_barang === this.kategori);
                                                if(this.search !== '') list = list.filter(o => o.nama_tampil.toLowerCase().includes(this.search.toLowerCase()));
                                                return list;
                                            },
                                            get selectedName() {
                                                if (!item.barang_id) return '-- Pilih Barang --';
                                                let b = this.options.find(o => o.barang_id == item.barang_id);
                                                if (b) return b.nama_tampil + ' (Stok: ' + b.stok + ')';
                                                if (item.nama_barang) return item.nama_barang;
                                                return '-- Pilih Barang --';
                                            }
                                        }" class="relative" style="position: relative;" @click.away="open = false">
                                            <div @click="open = !open" class="form-control" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: white;">
                                                <span x-text="selectedName" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 13px;"></span>
                                                <svg style="width: 16px; height: 16px; color: #888;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                            <div x-show="open" class="custom-dropdown-menu" style="display: none; min-width: 320px; right: 0;">
                                                <div class="custom-dropdown-header">
                                                    <div class="filter-btn-group">
                                                        <button type="button" @click.stop="kategori = ''" class="filter-btn" :class="kategori === '' ? 'active' : ''">Semua</button>
                                                        <button type="button" @click.stop="kategori = 'Fisik ACC'" class="filter-btn" :class="kategori === 'Fisik ACC' ? 'active' : ''">ACC</button>
                                                        <button type="button" @click.stop="kategori = 'Fisik Voucher Kartu'" class="filter-btn" :class="kategori === 'Fisik Voucher Kartu' ? 'active' : ''">Voucher/Kartu</button>
                                                    </div>
                                                    <input type="text" x-model="search" placeholder="Cari nama barang..." class="search-input" @click.stop>
                                                </div>
                                                <div>
                                                    <template x-for="opt in filtered" :key="opt.barang_id">
                                                        <div @click="if(opt.stok > 0) { item.barang_id = opt.barang_id.toString(); open = false; }" 
                                                             class="custom-dropdown-item" 
                                                             :class="[(opt.stok <= 0 ? 'disabled' : ''), (item.barang_id == opt.barang_id ? 'active' : '')].join(' ')">
                                                            <div style="flex: 1; min-width: 0; padding-right: 10px; text-align: left;">
                                                                <div x-text="opt.nama_tampil" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                                                                <div x-text="opt.tipe_barang" style="font-size: 10px; color: #888;"></div>
                                                            </div>
                                                            <div style="white-space: nowrap;">
                                                                <span x-show="opt.stok > 0" class="badge-stok ada" x-text="'Stok: ' + opt.stok"></span>
                                                                <span x-show="opt.stok <= 0" class="badge-stok habis">Habis</span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div x-show="filtered.length === 0" style="padding: 15px; font-size: 12px; color: #888; text-align: center;">Tidak ditemukan barang yang sesuai filter.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" x-model="item.qty" min="1" class="form-control" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" @click="hapusBaris(index)" style="background: transparent; border: none; color: #e74c3c; cursor: pointer; padding: 5px;">
                                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="pt-2 flex" style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="button" @click="submitForm('draft')" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2" style="flex: 1; background: #f39c12; color: white; font-weight: bold; padding: 12px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        SIMPAN KE DRAFT
                    </button>
                    <button type="button" @click="if(confirm('Proses dan kirim mutasi ini sekarang? Stok asal akan dipotong.')) submitForm('kirim')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2" style="flex: 1; background: #2980b9; color: white; font-weight: bold; padding: 12px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="height: 20px; width: 20px;" viewBox="0 0 20 20" fill="currentColor"><path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" /><path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" /></svg>
                        PROSES & KIRIM MUTASI
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB: DRAFT MUTASI -->
    <div x-show="tab === 'draft'" style="display: none;">
        <div class="table-container">
            <h3 style="margin-top: 0; color: #34495e; border-bottom: 2px solid #eee; padding-bottom: 10px;">Daftar Draft Mutasi Stok</h3>
            <table>
                <thead>
                    <tr>
                        <th>Waktu Dibuat</th>
                        <th>Lokasi Asal</th>
                        <th>Lokasi Tujuan</th>
                        <th>Daftar Barang (Qty)</th>
                        <th>Dibuat Oleh</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($draftMutasiStok as $mutasi)
                    <tr>
                        <td style="font-size: 13px; color: #7f8c8d;">
                            {{ $mutasi->created_at->format('d M Y, H:i') }}<br>
                            <span style="color: #e67e22;">{{ $mutasi->created_at->diffForHumans() }}</span>
                        </td>
                        <td style="font-weight: bold; color: #2c3e50;">
                            {{ $mutasi->cabangAsal ? $mutasi->cabangAsal->nama_cabang : 'Gudang Pusat' }}
                        </td>
                        <td style="font-weight: bold; color: #2980b9;">
                            {{ $mutasi->cabangTujuan->nama_cabang ?? 'Unknown' }}
                        </td>
                        <td style="font-size: 13px;">
                            <ul style="margin: 0; padding-left: 15px; list-style-type: disc; color: #555;">
                                @foreach($mutasi->detailMutasi as $dtl)
                                <li>{{ $dtl->barang->nama_barang ?? 'Unknown' }} <b>({{ $dtl->kuantitas }})</b></li>
                                @endforeach
                            </ul>
                        </td>
                        <td style="font-size: 13px; color: #7f8c8d;">
                            {{ $mutasi->admin->name ?? 'System' }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 5px; justify-content: flex-end;">
                                <form action="{{ route('admin.mutasi.proses_draft', $mutasi->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn-submit" style="background: #27ae60; padding: 6px 12px; font-size: 12px; display: flex; align-items: center; gap: 4px;" onclick="return confirm('Kirim mutasi ini sekarang? Stok asal akan dipotong.')">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Kirim
                                    </button>
                                </form>
                                <form action="{{ route('admin.mutasi.hapus_draft', $mutasi->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-submit" style="background: #e74c3c; padding: 6px 12px; font-size: 12px; display: flex; align-items: center; gap: 4px;" onclick="return confirm('Hapus draft ini secara permanen?')">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #888; padding: 30px;">
                            Tidak ada draft mutasi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <h3 style="margin-top: 40px; color: #34495e; border-bottom: 2px solid #eee; padding-bottom: 10px;">Daftar Draft Mutasi Uang Fisik</h3>
            <table>
                <thead>
                    <tr>
                        <th>Waktu Dibuat</th>
                        <th>Lokasi Asal</th>
                        <th>Lokasi Tujuan</th>
                        <th>Nominal Uang Fisik</th>
                        <th>Dibuat Oleh</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($draftMutasiCash as $mutasi)
                    <tr>
                        <td style="font-size: 13px; color: #7f8c8d;">
                            {{ $mutasi->created_at->format('d M Y, H:i') }}<br>
                            <span style="color: #e67e22;">{{ $mutasi->created_at->diffForHumans() }}</span>
                        </td>
                        <td style="font-weight: bold; color: #2c3e50;">
                            {{ $mutasi->cabangAsal ? $mutasi->cabangAsal->nama_cabang : 'Gudang Pusat' }}
                        </td>
                        <td style="font-weight: bold; color: #2980b9;">
                            {{ $mutasi->cabangTujuan->nama_cabang ?? 'Unknown' }}
                        </td>
                        <td style="font-size: 16px; font-weight: bold; color: #27ae60;">
                            Rp {{ number_format($mutasi->nominal, 0, ',', '.') }}
                        </td>
                        <td style="font-size: 13px; color: #7f8c8d;">
                            {{ $mutasi->admin->name ?? 'System' }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 5px; justify-content: flex-end;">
                                <form action="{{ route('admin.mutasi_cash.proses_draft', $mutasi->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn-submit" style="background: #27ae60; padding: 6px 12px; font-size: 12px; display: flex; align-items: center; gap: 4px;" onclick="return confirm('Kirim mutasi uang fisik ini sekarang? Saldo asal akan dipotong.')">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Kirim
                                    </button>
                                </form>
                                <form action="{{ route('admin.mutasi_cash.hapus_draft', $mutasi->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-submit" style="background: #e74c3c; padding: 6px 12px; font-size: 12px; display: flex; align-items: center; gap: 4px;" onclick="return confirm('Hapus draft uang fisik ini secara permanen?')">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #888; padding: 30px;">
                            Tidak ada draft mutasi uang fisik.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: KIRIM MUTASI UANG FISIK -->
    <div x-show="tab === 'mutasi_uang'">
        <div class="card" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="padding: 10px; background: #e8f8f5; color: #27ae60; border-radius: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" style="height: 24px; width: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 style="font-size: 20px; font-weight: bold; color: #2c3e50; margin: 0;">Kirim Mutasi Uang Fisik</h3>
                        <p style="font-size: 14px; color: #7f8c8d; margin: 5px 0 0 0;">Kirim uang tunai fisik antar cabang atau dari/ke gudang pusat.</p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('admin.mutasi_uang.store') }}" method="POST">
                @csrf
                @if(request('request_id') && request('mutasi_uang'))
                    <input type="hidden" name="request_id" value="{{ request('request_id') }}">
                    <div style="background: #e8f8f5; color: #27ae60; padding: 10px; border-radius: 6px; border-left: 4px solid #2ecc71; margin-bottom: 15px;">
                        ⚠️ <b>Mode Persetujuan Request Operasional:</b> Pengiriman uang fisik ini akan terhubung secara otomatis untuk menyelesaikan Request #{{ request('request_id') }} dari cabang. Nominal sudah otomatis terisi.
                    </div>
                @endif
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="font-weight: bold; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Lokasi Asal (Kirim Dari)</label>
                        <select name="asal_type" id="asal_type" class="form-control" style="width: 100%;" onchange="toggleCabangAsal()">
                            <option value="pusat">Gudang Pusat (Utama)</option>
                            <option value="cabang">Dari Cabang Lain</option>
                        </select>
                        <div id="cabang_asal_container" style="display: none; margin-top: 10px;">
                            <select name="asal_cabang_id" id="asal_cabang_id" class="form-control select2" style="width: 100%;" onchange="fetchSaldoKas()">
                                <option value="">-- Cari/Pilih Cabang Asal --</option>
                                @foreach($cabangs as $cab)
                                    <option value="{{ $cab->id }}">{{ $cab->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="saldo_kas_info" style="margin-top: 10px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;">
                            <strong style="color: #555;">Stok Uang Fisik (Laci):</strong>
                            <div style="color: #27ae60; font-size: 16px; font-weight: bold;" id="saldo_kas_value">Memuat...</div>
                        </div>
                    </div>
                    <div>
                        <label style="font-weight: bold; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Lokasi Tujuan (Penerima)</label>
                        <select name="tujuan_cabang_id" id="tujuan_cabang_id" required class="form-control select2" style="width: 100%;">
                            <option value="">-- Cari/Pilih Cabang Tujuan --</option>
                            @foreach($cabangs as $cab)
                                <option value="{{ $cab->id }}" {{ request('cabang_id') == $cab->id ? 'selected' : '' }}>{{ $cab->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="font-weight: bold; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Nominal Uang Fisik (Rp)</label>
                    <input type="number" name="nominal" required min="1" value="{{ request('nominal', '') }}" class="form-control" placeholder="Contoh: 1000000" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 16px; font-weight: bold; color: #27ae60;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-weight: bold; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Keterangan / Catatan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Opsional: Tuliskan nama pengantar atau catatan pengiriman..." style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                    <button type="submit" name="action" value="draft" style="flex: 1; background: #f39c12; color: white; border: none; padding: 12px 20px; font-weight: bold; border-radius: 6px; cursor: pointer; display: inline-flex; justify-content: center; align-items: center; gap: 8px;">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        SIMPAN KE DRAFT
                    </button>
                    <button type="submit" name="action" value="kirim" style="flex: 1; background: #27ae60; color: white; border: none; padding: 12px 20px; font-weight: bold; border-radius: 6px; cursor: pointer; display: inline-flex; justify-content: center; align-items: center; gap: 8px;" onclick="return confirm('Proses dan kirim mutasi uang fisik ini sekarang? Saldo laci asal akan terpotong.')">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        PROSES & KIRIM UANG FISIK
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- BAGIAN: RIWAYAT MUTASI (Digabung ke tab mutasi) -->
    <div style="margin-top: 40px;" x-data="{ filterRiwayat: '' }" x-show="['mutasi', 'mutasi_uang', 'draft'].includes(tab)">
        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0 0 10px 0; color: #34495e;">Riwayat Pengiriman Mutasi (Stok & Cash)</h3>
                    <form action="{{ route('admin.mutasi.print') }}" method="GET" target="_blank" style="display: flex; gap: 10px; align-items: center; background: #f8f9fa; padding: 10px; border-radius: 6px; border: 1px solid #eee;">
                        <div style="font-size: 13px; font-weight: bold; color: #555;">Cetak Laporan:</div>
                        <input type="date" name="start_date" required class="form-control" style="padding: 6px; font-size: 13px; border: 1px solid #ccc; border-radius: 4px;">
                        <span style="font-size: 13px;">s/d</span>
                        <input type="date" name="end_date" required class="form-control" style="padding: 6px; font-size: 13px; border: 1px solid #ccc; border-radius: 4px;">
                        <button type="submit" style="background: #e67e22; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Cetak
                        </button>
                    </form>
                </div>
                <input type="text" x-model="filterRiwayat" placeholder="Cari cabang atau admin..." style="padding: 10px; width: 100%; max-width: 300px; border: 1px solid #ddd; border-radius: 6px;">
            </div>

            <!-- Tabel Riwayat Mutasi Stok -->
            <h4 style="margin-bottom: 10px; color: #2980b9; padding-bottom: 5px; border-bottom: 1px solid #eee;">📦 Riwayat Mutasi Barang Fisik</h4>
            <div style="overflow-x: auto; margin-bottom: 30px;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; border: 1px solid #eee; text-align: left;">Waktu Mutasi</th>
                            <th style="padding: 12px; border: 1px solid #eee; text-align: left;">Lokasi Asal ➔ Tujuan</th>
                            <th style="padding: 12px; border: 1px solid #eee; text-align: left;">Detail Barang</th>
                            <th style="padding: 12px; border: 1px solid #eee; text-align: center;">Status</th>
                            <th style="padding: 12px; border: 1px solid #eee; text-align: left;">Dikirim Oleh / Bukti Gambar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayatMutasiStok as $rm)
                        <tr x-show="filterRiwayat === '' || '{{ strtolower(addslashes(($rm->cabangAsal->nama_cabang ?? 'Pusat') . ' ' . ($rm->cabangTujuan->nama_cabang ?? '') . ' ' . ($rm->admin->name ?? '')) ) }}'.includes(filterRiwayat.toLowerCase())">
                            <td style="padding: 12px; border: 1px solid #eee;">
                                {{ \Carbon\Carbon::parse($rm->created_at)->format('d M Y, H:i') }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #eee; font-weight: bold; color: #2c3e50;">
                                {{ $rm->cabangAsal ? $rm->cabangAsal->nama_cabang : 'Gudang Pusat' }}
                                <br><span style="color: #3498db;">➔ {{ $rm->cabangTujuan ? $rm->cabangTujuan->nama_cabang : 'Unknown' }}</span>
                            </td>
                            <td style="padding: 12px; border: 1px solid #eee; font-size: 13px;">
                                <ul style="margin: 0; padding-left: 20px;">
                                @foreach($rm->detailMutasi as $dtl)
                                    <li>
                                        {{ $dtl->barang->nama_barang ?? 'Unknown' }} ({{ $dtl->kuantitas }})
                                        @if($rm->status == 'diterima' && ($dtl->qty_rusak > 0 || $dtl->qty_hilang > 0))
                                            <br>
                                            <span style="color: #e74c3c; font-size: 11px;">
                                                ⚠️ Selisih: {{ $dtl->qty_rusak }} rusak, {{ $dtl->qty_hilang }} hilang
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                                </ul>
                            </td>
                            <td style="padding: 12px; border: 1px solid #eee; text-align: center;">
                                @if($rm->status == 'dikirim')
                                    <span style="background: #f39c12; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">Dikirim (In Transit)</span>
                                @else
                                    @if($rm->status_penerimaan == 'bermasalah')
                                        <span style="background: #e74c3c; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">⚠️ Ada Selisih</span>
                                    @else
                                        <span style="background: #27ae60; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">Diterima Sesuai</span>
                                    @endif
                                @endif
                            </td>
                            <td style="padding: 12px; border: 1px solid #eee; font-size: 13px;">
                                <strong>Admin:</strong> {{ $rm->admin->name ?? '-' }}
                                @if($rm->bukti_gambar)
                                    <div style="margin-top: 8px;">
                                        <a href="{{ asset('storage/' . $rm->bukti_gambar) }}" target="_blank" style="display: inline-block; padding: 4px 8px; background: #3498db; color: white; border-radius: 4px; text-decoration: none; font-size: 11px;">
                                            📸 Lihat Bukti Gambar
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #888;">Belum ada riwayat mutasi barang fisik.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tabel Riwayat Mutasi Cash -->
            <h4 style="margin-bottom: 10px; color: #27ae60; padding-bottom: 5px; border-bottom: 1px solid #eee;">💵 Riwayat Mutasi Uang Tunai</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; border: 1px solid #eee; text-align: left;">Waktu Mutasi</th>
                            <th style="padding: 12px; border: 1px solid #eee; text-align: left;">Lokasi Asal ➔ Tujuan</th>
                            <th style="padding: 12px; border: 1px solid #eee; text-align: left;">Nominal Uang</th>
                            <th style="padding: 12px; border: 1px solid #eee; text-align: center;">Status</th>
                            <th style="padding: 12px; border: 1px solid #eee; text-align: left;">Dikirim Oleh / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayatMutasiCash as $rc)
                        <tr x-show="filterRiwayat === '' || '{{ strtolower(addslashes(($rc->cabangAsal->nama_cabang ?? 'Pusat') . ' ' . ($rc->cabangTujuan->nama_cabang ?? '') . ' ' . ($rc->admin->name ?? '')) ) }}'.includes(filterRiwayat.toLowerCase())">
                            <td style="padding: 12px; border: 1px solid #eee;">
                                {{ \Carbon\Carbon::parse($rc->created_at)->format('d M Y, H:i') }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #eee; font-weight: bold; color: #2c3e50;">
                                {{ $rc->cabangAsal ? $rc->cabangAsal->nama_cabang : 'Gudang Pusat' }}
                                <br><span style="color: #3498db;">➔ {{ $rc->cabangTujuan ? $rc->cabangTujuan->nama_cabang : 'Unknown' }}</span>
                            </td>
                            <td style="padding: 12px; border: 1px solid #eee; font-weight: bold; color: #27ae60;">
                                Rp {{ number_format($rc->nominal, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #eee; text-align: center;">
                                @if($rc->status == 'dikirim')
                                    <span style="background: #f39c12; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">Dikirim (In Transit)</span>
                                @else
                                    <span style="background: #27ae60; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">Diterima Kasir</span>
                                @endif
                            </td>
                            <td style="padding: 12px; border: 1px solid #eee; font-size: 13px;">
                                <strong>Admin:</strong> {{ $rc->admin->name ?? '-' }}<br>
                                <span style="color: #7f8c8d;">Catatan: {{ $rc->keterangan ?? '-' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #888;">Belum ada riwayat mutasi uang tunai.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: MONITORING SALDO CABANG -->
    <!-- TAB: MONITORING ASET CABANG -->
    <div x-show="tab === 'cabang'" style="display: none;" x-data="{ modeCabang: 'fisik', searchCabang: '' }">

        <!-- Mode Toggle -->
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;">
            <div style="display: flex; background: #f4f6f7; border-radius: 8px; padding: 4px; gap: 2px;">
                <button @click="modeCabang = 'fisik'" :style="modeCabang === 'fisik' ? 'background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.1); color: #2980b9;' : 'background: transparent; color: #7f8c8d;'" style="padding: 8px 20px; border: none; border-radius: 6px; font-weight: bold; font-size: 13px; cursor: pointer; transition: all 0.2s;">📦 Stok Fisik</button>
                <button @click="modeCabang = 'saldo'" :style="modeCabang === 'saldo' ? 'background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.1); color: #27ae60;' : 'background: transparent; color: #7f8c8d;'" style="padding: 8px 20px; border: none; border-radius: 6px; font-weight: bold; font-size: 13px; cursor: pointer; transition: all 0.2s;">💰 Saldo Kas</button>
            </div>
            <input type="text" x-model="searchCabang" placeholder="Cari nama cabang..." style="padding: 9px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; flex: 1; min-width: 180px; max-width: 300px; outline: none;">
        </div>

        <!-- MODE: STOK FISIK PER CABANG -->
        <div x-show="modeCabang === 'fisik'">
            @foreach($rekapCabang as $rekap)
            <div style="margin-bottom: 16px; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,0.07); border: 1px solid {{ $rekap['has_ews'] ? '#ffc9c9' : '#e8eaed' }};" x-show="searchCabang === '' || '{{ strtolower($rekap['cabang']->nama_cabang) }}'.includes(searchCabang.toLowerCase())">
                <!-- Cabang Header -->
                <div style="background: {{ $rekap['has_ews'] ? '#fff0f0' : '#f8f9fa' }}; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid {{ $rekap['has_ews'] ? '#ffc9c9' : '#eee' }};" x-data="{ open: false }" @click="open = !open" style2="cursor: pointer;">
                    <div style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <span style="font-weight: bold; font-size: 15px; color: {{ $rekap['has_ews'] ? '#c0392b' : '#2c3e50' }};">
                            {{ $rekap['cabang']->nama_cabang }}
                        </span>
                        @if($rekap['has_ews'])
                            <span style="font-size: 11px; background: #e74c3c; color: white; padding: 2px 8px; border-radius: 12px; font-weight: bold;">⚠ {{ $rekap['ews_count'] }} Kritis</span>
                        @endif
                        <span style="font-size: 12px; color: #7f8c8d;">{{ $rekap['total_barang'] }} jenis barang</span>
                    </div>
                    <span style="color: #7f8c8d; font-size: 18px; cursor: pointer;" x-text="open ? '▲' : '▼'"></span>
                </div>

                <!-- Cabang Items Table -->
                <div x-show="open" x-transition style="overflow-x: auto; background: white;">
                    @if(count($rekap['items']) > 0)
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #fdfdfd; border-bottom: 2px solid #eee;">
                                <th style="padding: 10px 16px; font-size: 11px; font-weight: bold; color: #7f8c8d; text-transform: uppercase; text-align: left;">Nama Barang</th>
                                <th style="padding: 10px 16px; font-size: 11px; font-weight: bold; color: #7f8c8d; text-transform: uppercase; text-align: center;">Stok</th>
                                <th style="padding: 10px 16px; font-size: 11px; font-weight: bold; color: #7f8c8d; text-transform: uppercase; text-align: center;">Batas EWS</th>
                                <th style="padding: 10px 16px; font-size: 11px; font-weight: bold; color: #7f8c8d; text-transform: uppercase; text-align: center;">Total Modal</th>
                                <th style="padding: 10px 16px; font-size: 11px; font-weight: bold; color: #7f8c8d; text-transform: uppercase; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekap['items'] as $item)
                            <tr style="border-bottom: 1px solid #f5f5f5; background: {{ $item['is_kritis'] ? '#fffafa' : 'white' }};">
                                <td style="padding: 12px 16px;">
                                    <div style="font-weight: 600; color: {{ $item['is_kritis'] ? '#c0392b' : '#2c3e50' }}; font-size: 13px;">{{ $item['nama_barang'] }}</div>
                                    <div style="font-size: 11px; color: #95a5a6;">{{ $item['kategori'] }}</div>
                                </td>
                                <td style="padding: 12px 16px; text-align: center; font-weight: bold; color: {{ $item['is_kritis'] ? '#e74c3c' : '#2980b9' }};">
                                    {{ number_format($item['total_stok'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 12px 16px; text-align: center; color: #7f8c8d; font-size: 13px;">
                                    ≤ {{ number_format($item['batas_ews'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 12px 16px; text-align: center; font-size: 13px; color: #555;">
                                    Rp {{ number_format($item['total_aset_modal'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    @if($item['is_kritis'])
                                        <button type="button"
                                            onclick="openEwsCabangModal({{ json_encode($item) }}, '{{ addslashes($rekap['cabang']->nama_cabang) }}', {{ $rekap['cabang']->id }})"
                                            style="font-size: 11px; background: #e74c3c; color: white; padding: 3px 10px; border-radius: 12px; font-weight: bold; border: none; cursor: pointer;">
                                            ⚠ KRITIS
                                        </button>
                                    @else
                                        <span style="font-size: 11px; background: #d5f5e3; color: #27ae60; padding: 3px 10px; border-radius: 12px; font-weight: bold;">Aman</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div style="padding: 20px; text-align: center; color: #95a5a6; font-size: 13px;">Belum ada stok barang fisik di cabang ini.</div>
                    @endif
                </div>
            </div>
            @endforeach
            @if(count($rekapCabang) === 0)
            <div style="padding: 40px; text-align: center; color: #95a5a6;">Belum ada data cabang.</div>
            @endif
        </div>

        <!-- MODE: SALDO KAS PER CABANG -->
        <div x-show="modeCabang === 'saldo'">
            @foreach($groupedCabangs as $area => $cabangsInArea)
            <h4 style="margin-top: 20px; color: #34495e; border-bottom: 2px solid #eee; padding-bottom: 5px;">Area: {{ $area }}</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
                    @foreach($cabangsInArea as $cab)
                    <div x-show="searchCabang === '' || '{{ strtolower($cab->nama_cabang) }}'.includes(searchCabang.toLowerCase())" class="cabang-card">
                        <div class="cabang-header" onclick="loadCabangStats({{ $cab->id }}, this)">
                            <div class="cabang-title">{{ $cab->nama_cabang }}</div>
                            <div style="font-size: 12px; color: var(--primary);">Lihat Detail ▼</div>
                        </div>
                        <div class="cabang-content" id="cabang-content-{{ $cab->id }}">
                            <div style="text-align: center; color: #888; font-size: 12px;">Memuat data...</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endforeach
        </div>

    </div>

</div>

<script>
    function toggleCabangAsal() {
        var asalType = document.getElementById('asal_type').value;
        var cabangSelectContainer = document.getElementById('cabang_asal_container');
        var cabangSelect = document.getElementById('asal_cabang_id');
        
        if (asalType === 'cabang') {
            cabangSelectContainer.style.display = 'block';
            cabangSelect.required = true;
        } else {
            cabangSelectContainer.style.display = 'none';
            cabangSelect.required = false;
        }
        fetchSaldoKas();
    }

    function fetchSaldoKas() {
        var asalType = $('#asal_type').val() || document.getElementById('asal_type').value;
        var cabangId = $('#asal_cabang_id').val() || document.getElementById('asal_cabang_id').value;
        var url = '/api/saldo-kas';
        
        if (asalType === 'cabang' && cabangId) {
            url += '/' + cabangId;
        } else if (asalType === 'cabang' && !cabangId) {
            document.getElementById('saldo_kas_value').innerText = 'Pilih cabang...';
            return;
        }
        
        document.getElementById('saldo_kas_value').innerText = 'Memuat...';
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('saldo_kas_value').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.saldo);
                } else {
                    document.getElementById('saldo_kas_value').innerText = 'Gagal memuat.';
                }
            })
            .catch(err => {
                document.getElementById('saldo_kas_value').innerText = 'Error jaringan.';
            });
    }

    // Call fetchSaldoKas on page load to initialize Gudang Pusat balance
    document.addEventListener('DOMContentLoaded', function() {
        fetchSaldoKas();
    });
</script>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function loadCabangStats(cabangId, headerEl) {
        const contentDiv = document.getElementById('cabang-content-' + cabangId);
        if (contentDiv.style.display === 'block') {
            contentDiv.style.display = 'none';
            headerEl.querySelector('div:last-child').innerText = 'Lihat Detail ▼';
            return;
        }

        contentDiv.style.display = 'block';
        headerEl.querySelector('div:last-child').innerText = 'Tutup ▲';
        contentDiv.innerHTML = '<div style="text-align:center; color:#888;">Memuat data secara live...</div>';

        fetch(`/distribusi-mutasi/stats/${cabangId}`)
            .then(res => res.json())
            .then(data => {
                let html = `
                    <div style="margin-bottom: 10px;">
                        <strong style="color: #2c3e50;">Cash (Laci):</strong> 
                        <span style="color: #27ae60; font-size: 18px; font-weight: bold; float: right;">Rp ${new Intl.NumberFormat('id-ID').format(data.total_pendapatan_hari_ini)}</span>
                    </div>
                    <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dashed #ccc;">
                        Masuk: Rp ${new Intl.NumberFormat('id-ID').format(data.cash_masuk_hari_ini)} | Keluar: Rp ${new Intl.NumberFormat('id-ID').format(data.cash_keluar_hari_ini)}
                    </div>
                    <strong style="color: #34495e; font-size: 13px;">Saldo Digital (Layanan):</strong>
                    <ul style="margin: 5px 0 0 0; padding-left: 20px; font-size: 13px;">
                `;

                if(data.layanan.length === 0) {
                    html += `<li style="color:#e74c3c;">Belum ada saldo terdaftar</li>`;
                } else {
                    data.layanan.forEach(l => {
                        html += `<li style="margin-bottom: 5px;">${l.nama_layanan}: <b style="color: #2980b9;">Rp ${new Intl.NumberFormat('id-ID').format(l.saldo_sekarang)}</b></li>`;
                    });
                }
                
                html += `</ul>`;
                contentDiv.innerHTML = html;
            })
            .catch(err => {
                contentDiv.innerHTML = '<div style="color:red; font-size: 12px;">Gagal memuat data live.</div>';
            });
    }

    $(document).ready(function() {
        $('.select2').select2();

        $('#asal_type').on('change', function() { toggleCabangAsal(); });
        $('#asal_cabang_id').on('change', function() { fetchSaldoKas(); });
        
        toggleCabangAsal();
    });
</script>

<!-- Modal Detail Gudang Pusat -->
<div id="pusatModal" style="display: none; position: fixed; inset: 0; z-index: 100; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; padding: 20px;" 
     x-data="{ batches: [], searchBatch: '', filterSupplier: '' }" 
     @load-pusat-modal.window="batches = $event.detail.batches; searchBatch = ''; filterSupplier = ''; document.getElementById('modalPusatTitle').textContent = 'Detail Batch: ' + $event.detail.barangName; document.getElementById('pusatModal').style.display = 'flex';" 
     @close-pusat-modal.window="document.getElementById('pusatModal').style.display = 'none';">
     
    <div style="background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 900px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f9f9f9;">
            <h3 style="margin: 0; font-size: 18px; color: #34495e;" id="modalPusatTitle">Detail Batch Barang</h3>
            <button type="button" @click="$dispatch('close-pusat-modal')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #7f8c8d;">&times;</button>
        </div>
        <div style="padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; gap: 15px; background: white;">
            <input type="text" x-model="searchBatch" placeholder="Cari kode batch..." style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; flex: 1;">
            <input type="text" x-model="filterSupplier" placeholder="Cari supplier..." style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; flex: 1;">
        </div>
        <div style="padding: 20px; overflow-y: auto; background: #fafafa;">
            <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #eee; border-radius: 8px;">
                <thead style="background: #f4f6f7;">
                    <tr>
                        <th style="padding: 12px 15px; border-bottom: 1px solid #eee; text-align: left; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Batch Beli</th>
                        <th style="padding: 12px 15px; border-bottom: 1px solid #eee; text-align: left; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Tanggal Masuk (Usia)</th>
                        <th style="padding: 12px 15px; border-bottom: 1px solid #eee; text-align: left; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Supplier</th>
                        <th style="padding: 12px 15px; border-bottom: 1px solid #eee; text-align: right; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Stok Sisa</th>
                        <th style="padding: 12px 15px; border-bottom: 1px solid #eee; text-align: right; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Harga Modal Satuan</th>
                        <th style="padding: 12px 15px; border-bottom: 1px solid #eee; text-align: right; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Subtotal Modal</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="b in batches.filter(i => (searchBatch === '' || i.kode_batch.toLowerCase().includes(searchBatch.toLowerCase())) && (filterSupplier === '' || i.supplier.toLowerCase().includes(filterSupplier.toLowerCase())))" :key="b.kode_batch">
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 12px 15px; font-weight: bold; color: #2c3e50;" x-text="b.kode_batch"></td>
                            <td style="padding: 12px 15px;">
                                <div style="font-weight: 500; color: #34495e;" x-text="b.tanggal_masuk"></div>
                                <div style="font-size: 11px; color: #95a5a6;"><span x-text="b.usia_hari"></span> Hari di Gudang</div>
                            </td>
                            <td style="padding: 12px 15px; color: #7f8c8d;" x-text="b.supplier"></td>
                            <td style="padding: 12px 15px; text-align: right; font-weight: bold; color: #2980b9;" x-text="new Intl.NumberFormat('id-ID').format(b.stok)"></td>
                            <td style="padding: 12px 15px; text-align: right; color: #7f8c8d;" x-text="b.harga_modal"></td>
                            <td style="padding: 12px 15px; text-align: right; font-weight: bold; color: #34495e;" x-text="b.subtotal_modal"></td>
                        </tr>
                    </template>
                    <tr x-show="batches.length === 0">
                        <td colspan="6" style="padding: 20px; text-align: center; color: #95a5a6;">Tidak ada data batch.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endpush

<!-- ========== MODAL: EWS KRITIS - GUDANG PUSAT ========== -->
<div id="ewsPusatModal" style="display:none; position:fixed; inset:0; z-index:200; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; padding:20px;">
    <div style="background:white; border-radius:14px; box-shadow:0 20px 50px rgba(0,0,0,0.3); width:100%; max-width:500px; overflow:hidden;">
        <div style="background: linear-gradient(135deg, #e74c3c, #c0392b); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="color:rgba(255,255,255,0.8); font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">⚠ Peringatan EWS Kritis</div>
                <h3 id="ewsPusatTitle" style="margin:4px 0 0; color:white; font-size:18px; font-weight:bold;"></h3>
            </div>
            <button onclick="document.getElementById('ewsPusatModal').style.display='none'" style="background:rgba(255,255,255,0.2); border:none; color:white; font-size:22px; border-radius:50%; width:36px; height:36px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <div style="padding:24px;">
            <div style="background:#fff8f8; border:1px solid #ffc9c9; border-radius:10px; padding:16px; margin-bottom:16px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <div style="font-size:11px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">Stok Saat Ini</div>
                        <div id="ewsPusatStok" style="font-size:22px; font-weight:bold; color:#e74c3c;"></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">Batas EWS (Safety Stock)</div>
                        <div id="ewsPusatBatas" style="font-size:22px; font-weight:bold; color:#e67e22;"></div>
                    </div>
                </div>
            </div>
            <div style="background:#f0f7ff; border:1px solid #bce0fd; border-radius:10px; padding:16px; margin-bottom:20px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <div style="font-size:11px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">Peramalan (Hari)</div>
                        <div id="ewsPusatHari" style="font-size:22px; font-weight:bold; color:#2980b9;"></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">Rekomendasi Restok</div>
                        <div id="ewsPusatRekomendasi" style="font-size:22px; font-weight:bold; color:#27ae60;"></div>
                    </div>
                </div>
            </div>
            <p style="font-size:13px; color:#7f8c8d; margin:0 0 16px; text-align:center;">Segera lakukan pembelian ke supplier untuk menjaga ketersediaan stok.</p>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button onclick="document.getElementById('ewsPusatModal').style.display='none'" style="padding:10px 20px; border:1px solid #ddd; background:white; border-radius:8px; cursor:pointer; font-weight:bold; color:#7f8c8d;">Tutup</button>
                <a id="ewsPusatLinkBeli" href="{{ route('admin.pembelian.create') }}" style="padding:10px 20px; background:#27ae60; color:white; border-radius:8px; font-weight:bold; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                    🛒 Beli ke Supplier
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL: EWS KRITIS - CABANG ========== -->
<div id="ewsCabangModal" style="display:none; position:fixed; inset:0; z-index:200; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; padding:20px;">
    <div style="background:white; border-radius:14px; box-shadow:0 20px 50px rgba(0,0,0,0.3); width:100%; max-width:500px; overflow:hidden;">
        <div style="background: linear-gradient(135deg, #e74c3c, #c0392b); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div id="ewsCabangSubtitle" style="color:rgba(255,255,255,0.8); font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">⚠ Kritis di Cabang</div>
                <h3 id="ewsCabangTitle" style="margin:4px 0 0; color:white; font-size:18px; font-weight:bold;"></h3>
            </div>
            <button onclick="document.getElementById('ewsCabangModal').style.display='none'" style="background:rgba(255,255,255,0.2); border:none; color:white; font-size:22px; border-radius:50%; width:36px; height:36px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <div style="padding:24px;">
            <div style="background:#fff8f8; border:1px solid #ffc9c9; border-radius:10px; padding:16px; margin-bottom:16px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <div style="font-size:11px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">Stok Cabang Saat Ini</div>
                        <div id="ewsCabangStok" style="font-size:22px; font-weight:bold; color:#e74c3c;"></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">Batas EWS (Safety Stock)</div>
                        <div id="ewsCabangBatas" style="font-size:22px; font-weight:bold; color:#e67e22;"></div>
                    </div>
                </div>
            </div>
            <div style="background:#f0f7ff; border:1px solid #bce0fd; border-radius:10px; padding:16px; margin-bottom:20px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <div style="font-size:11px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">Peramalan (Hari)</div>
                        <div id="ewsCabangHari" style="font-size:22px; font-weight:bold; color:#2980b9;"></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">Rekomendasi Mutasi</div>
                        <div id="ewsCabangRekomendasi" style="font-size:22px; font-weight:bold; color:#27ae60;"></div>
                    </div>
                </div>
            </div>
            <p style="font-size:13px; color:#7f8c8d; margin:0 0 16px; text-align:center;">Cabang ini kekurangan stok. Kirim mutasi barang dari Gudang Pusat atau cabang lain.</p>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button onclick="document.getElementById('ewsCabangModal').style.display='none'" style="padding:10px 20px; border:1px solid #ddd; background:white; border-radius:8px; cursor:pointer; font-weight:bold; color:#7f8c8d;">Tutup</button>
                <a id="ewsCabangLinkMutasi" href="#" style="padding:10px 20px; background:#2980b9; color:white; border-radius:8px; font-weight:bold; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                    🚚 Kirim Mutasi Barang
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Open EWS modal for Gudang Pusat (triggered via Alpine dispatch)
document.addEventListener('load-ews-pusat-modal', function(e) {
    const item = e.detail.item;
    document.getElementById('ewsPusatTitle').textContent = item.nama_barang;
    document.getElementById('ewsPusatStok').textContent = new Intl.NumberFormat('id-ID').format(item.total_stok) + ' pcs';
    document.getElementById('ewsPusatBatas').textContent = '\u2264 ' + new Intl.NumberFormat('id-ID').format(item.batas_ews) + ' pcs';
    document.getElementById('ewsPusatHari').textContent = item.hari_peramalan + ' hari';
    document.getElementById('ewsPusatRekomendasi').textContent = new Intl.NumberFormat('id-ID').format(item.rekomendasi_restok) + ' pcs';
    document.getElementById('ewsPusatModal').style.display = 'flex';
});

// Open EWS modal for Cabang (called from Blade onclick)
function openEwsCabangModal(item, cabangName, cabangId) {
    document.getElementById('ewsCabangSubtitle').textContent = '\u26a0 Kritis di ' + cabangName;
    document.getElementById('ewsCabangTitle').textContent = item.nama_barang;
    document.getElementById('ewsCabangStok').textContent = new Intl.NumberFormat('id-ID').format(item.total_stok) + ' pcs';
    document.getElementById('ewsCabangBatas').textContent = '\u2264 ' + new Intl.NumberFormat('id-ID').format(item.batas_ews) + ' pcs';
    document.getElementById('ewsCabangHari').textContent = item.hari_peramalan + ' hari';
    document.getElementById('ewsCabangRekomendasi').textContent = new Intl.NumberFormat('id-ID').format(item.rekomendasi_restok) + ' pcs';
    // Link ke tab mutasi dengan pre-select cabang tujuan, barang, dan qty rekomendasi
    const mutasiUrl = '{{ route('distribusi.mutasi.index') }}?tab=mutasi&cabang_tujuan=' + cabangId + '&barang_id=' + item.barang_id + '&qty=' + (item.rekomendasi_restok || 1);
    document.getElementById('ewsCabangLinkMutasi').href = mutasiUrl;
    document.getElementById('ewsCabangModal').style.display = 'flex';
}
</script>
@endsection
