@extends('layouts.app')
@section('title', 'Buat Purchase Order')

@push('styles')
<style>
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn-back { color: var(--primary); text-decoration: none; font-weight: bold; font-size: 14px; }
    .btn-back:hover { text-decoration: underline; }

    .form-section { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 20px; }
    .form-header { background: var(--primary); color: white; padding: 15px 20px; }
    .form-header h2 { margin: 0; font-size: 18px; }
    .form-header p { margin: 5px 0 0 0; font-size: 13px; color: #e0f7fa; }
    
    .form-body { padding: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50; font-size: 14px; }
    .form-control { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 4px; font-size: 14px; box-sizing: border-box; }
    
    .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .table th, .table td { border: 1px solid var(--border); padding: 10px; text-align: left; font-size: 14px; }
    .table th { background: #f9f9f9; font-weight: bold; color: #555; }
    .table-input { width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 4px; box-sizing: border-box; font-size: 13px;}
    
    .btn-add { background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px;}
    .btn-remove { background: #e74c3c; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px;}
    .btn-save { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 16px; width: 100%; margin-top: 20px;}
    .btn-save:hover { opacity: 0.9; }

    /* Alpine Search Modal/Dropdown */
    .search-box { position: relative; margin-bottom: 20px; }
    .search-results { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid var(--border); border-radius: 0 0 4px 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-height: 300px; overflow-y: auto; z-index: 50; display: none; }
    .search-results.active { display: block; }
    .search-item { padding: 10px; border-bottom: 1px solid #f0f0f0; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
    .search-item:hover { background: #f9f9f9; }
</style>
@endpush

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('admin.pembelian.index') }}" class="btn-back">⬅️ Kembali ke Riwayat Pembelian</a>
</div>

<div class="form-section" x-data="pembelianData()">
    <div class="form-header">
        <h2>Edit Draft PO #{{ $pembelian->id }}</h2>
        <p>Edit draft pesanan ke supplier dan tambahkan ke stok gudang.</p>
    </div>

    <form action="{{ route('admin.pembelian.update', $pembelian->id) }}" method="POST" class="form-body">
        @csrf
        @method('PUT')
        @if(session('success'))
            <div style="background: #d5f5e3; color: #27ae60; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; border-left: 4px solid #2ecc71;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: #fadbd8; color: #c0392b; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; border-left: 4px solid #e74c3c;">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: #fadbd8; color: #c0392b; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; border-left: 4px solid #e74c3c;">
                ⚠️ Terjadi kesalahan:
                <ul style="margin-top: 5px; margin-bottom: 0;">
                    @foreach($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Nama Supplier (Opsional)</label>
                <input type="text" name="nama_supplier" value="{{ old('nama_supplier', $pembelian->supplier->nama_supplier ?? '') }}" class="form-control" placeholder="Biarkan kosong jika tidak ada (Umum)">
            </div>
            <div class="form-group">
                <label>Kontak Supplier (Opsional)</label>
                <input type="text" name="kontak_supplier" value="{{ old('kontak_supplier', $pembelian->supplier->kontak ?? '') }}" class="form-control" placeholder="No HP / Email">
            </div>
        </div>

        <div class="form-group" style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px;">
            <h3 style="margin-top: 0; color: #2c3e50;">Pilih Barang</h3>
            
            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                <select x-model="filterKategori" class="form-control" style="width: 250px;">
                    <option value="">Semua Kategori</option>
                    <option value="Fisik ACC">Fisik ACC</option>
                    <option value="Fisik Voucher Kartu">Fisik Voucher Kartu</option>
                </select>
                
                <input type="text" x-model="searchQuery" @focus="showDropdown = true" @click.away="showDropdown = false" class="form-control" placeholder="Cari nama barang...">
            </div>

            <div class="search-box">
                <div class="search-results" :class="showDropdown && filteredItems.length > 0 ? 'active' : ''">
                    <template x-for="item in filteredItems" :key="item.id">
                        <div class="search-item" @click="addItem(item)">
                            <div>
                                <div style="font-weight: bold; font-size: 14px;" x-text="item.nama_barang"></div>
                                <div style="font-size: 11px; color: #7f8c8d;" x-text="item.tipe_barang"></div>
                            </div>
                            <button type="button" class="btn-add">Tambah</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <label style="margin: 0;">Daftar Barang yang Dipesan</label>
            </div>

            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Barang Fisik</th>
                            <th style="width: 100px;">Qty Beli</th>
                            <th style="width: 200px;">Total Harga Beli (Rp)</th>
                            <th style="width: 150px;">HPP / Satuan (Rp)</th>
                            <th style="width: 50px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in selectedItems" :key="index">
                            <tr>
                                <td>
                                    <input type="hidden" :name="'items['+index+'][barang_fisik_id]'" :value="row.id">
                                    <div style="font-weight: bold; font-size: 13px;" x-text="row.nama_barang"></div>
                                    <div style="font-size: 11px; color: #7f8c8d;" x-text="row.tipe_barang"></div>
                                </td>
                                <td>
                                    <input type="number" :name="'items['+index+'][qty]'" x-model="row.qty" class="table-input" min="1" required>
                                </td>
                                <td>
                                    <input type="number" :name="'items['+index+'][subtotal]'" x-model="row.subtotal" class="table-input" min="0" required>
                                </td>
                                <td>
                                    <input type="text" class="table-input" style="background: #f4f7f6; font-weight: bold;" readonly :value="formatRupiah(hitungHPP(row))">
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" @click="removeItem(index)" class="btn-remove">✖</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="selectedItems.length === 0">
                            <td colspan="5" style="text-align: center; color: #7f8c8d; padding: 20px;">Belum ada barang yang dipilih.</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="text-align: right; font-weight: bold; color: #7f8c8d;">Grand Total:</td>
                            <td colspan="3" style="font-weight: bold; color: var(--primary); font-size: 18px;" x-text="'Rp ' + formatRupiah(grandTotal)"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="form-group" style="margin-top: 30px; background: #fdfdfd; padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
            <h3 style="margin-top: 0; color: #2c3e50; font-size: 16px;">Opsi "Langsung Beli" (Bypass Draft)</h3>
            <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 15px;">Jika Anda ingin langsung membeli dan stok masuk ke gudang, pilih metode pembayaran di bawah ini. Jika hanya menyimpan draft, biarkan kosong.</p>
            
            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-weight: normal;">
                    <input type="radio" name="metode_pembayaran" value="cash_pusat" x-model="metodeBayar">
                    <span>Cash Pusat (Saldo: Rp {{ number_format($kasPusat->saldo ?? 0, 0, ',', '.') }})</span>
                </label>
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-weight: normal;">
                    <input type="radio" name="metode_pembayaran" value="apk_layanan" x-model="metodeBayar">
                    <span>Dompet Digital Pusat</span>
                </label>
            </div>

            <div x-show="metodeBayar === 'apk_layanan'" style="margin-bottom: 15px;">
                <label>Pilih Dompet Digital</label>
                <select name="apk_layanan_id" class="form-control" style="max-width: 400px;">
                    <option value="">-- Pilih --</option>
                    @foreach($dompetPusat as $dompet)
                        <option value="{{ $dompet->id }}">{{ $dompet->masterAplikasiLayanan->nama_layanan ?? $dompet->masterAplikasiLayanan->nama_aplikasi }} (Saldo: Rp {{ number_format($dompet->saldo_sekarang, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <button type="submit" name="action" value="draft" class="btn-save" style="background: #95a5a6; flex: 1;" :disabled="selectedItems.length === 0">💾 Simpan ke Draft</button>
            <button type="submit" name="action" value="langsung_beli" class="btn-save" style="flex: 2; background: #27ae60;" :disabled="selectedItems.length === 0 || !metodeBayar" @click="if(!confirm('Apakah Anda yakin data dan total bayar sudah benar? Saldo Anda akan otomatis terpotong.')) { $event.preventDefault(); }">✅ Langsung Beli & Selesai</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pembelianData', () => ({
        barangs: @json($barangs),
        searchQuery: '',
        filterKategori: '',
        showDropdown: false,
        selectedItems: [],
        metodeBayar: '{{ old('metode_pembayaran', $pembelian->metode_pembayaran ?? '') }}',

        init() {
            let existingDetails = @json($pembelian->detailPembelian);
            if (existingDetails && existingDetails.length > 0) {
                existingDetails.forEach(detail => {
                    this.selectedItems.push({
                        id: detail.barang_fisik_id,
                        nama: detail.barang_fisik.nama_barang,
                        qty: detail.qty,
                        subtotal: detail.subtotal > 0 ? detail.subtotal : ''
                    });
                });
            }
        },

        get filteredItems() {
            if (this.searchQuery === '' && this.filterKategori === '') return [];
            
            return this.barangs.filter(b => {
                let matchCat = this.filterKategori === '' || b.tipe_barang === this.filterKategori;
                let matchSearch = b.nama_barang.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchCat && matchSearch;
            }).slice(0, 10); // Batasi 10 hasil agar rapi
        },

        addItem(item) {
            // Cek apakah sudah ada
            let exists = this.selectedItems.find(x => x.id === item.id);
            if(exists) {
                exists.qty++;
            } else {
                this.selectedItems.push({
                    id: item.id,
                    nama_barang: item.nama_barang,
                    tipe_barang: item.tipe_barang,
                    qty: 1,
                    subtotal: 0
                });
            }
            this.showDropdown = false;
            this.searchQuery = '';
        },

        removeItem(index) {
            this.selectedItems.splice(index, 1);
        },

        hitungHPP(row) {
            let qty = parseFloat(row.qty) || 0;
            let subtotal = parseFloat(row.subtotal) || 0;
            if(qty === 0) return 0;
            return subtotal / qty;
        },

        get grandTotal() {
            return this.selectedItems.reduce((acc, row) => {
                return acc + (parseFloat(row.subtotal) || 0);
            }, 0);
        },

        formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(Math.round(angka));
        }
    }));
});
</script>
@endpush
@endsection
