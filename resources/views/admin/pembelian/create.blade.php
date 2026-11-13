@extends('layouts.app')
@section('title', 'Buat Purchase Order (PO)')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    /* Styling scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush

@section('content')
<div class="p-4 md:p-6 bg-slate-50 min-h-screen" x-data="pembelianData()">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Top Header Navigation & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pembelian.index') }}" 
                   class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition active:scale-95" 
                   title="Kembali ke Riwayat Pembelian">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Form Pembelian Barang (PO Supplier)</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pesan stok fisik baru dari supplier untuk menambah persediaan Gudang Pusat</p>
                </div>
            </div>
            
            <a href="{{ route('admin.pembelian.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Lihat Riwayat PO Supplier</span>
            </a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
                <div>
                    <p class="font-bold">Berhasil!</p>
                    <p class="text-sm text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-xl shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg mt-0.5"></i>
                <div>
                    <p class="font-bold">Gagal Memproses:</p>
                    <p class="text-sm text-rose-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                    <span>Harap periksa form kembali:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Banner Jika Terhubung dari Request Operasional Cabang -->
        @if(isset($requestOp) && $requestOp)
            <div class="bg-amber-50 border border-amber-300 text-amber-900 p-4 rounded-2xl shadow-sm flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Menindaklanjuti Request Operasional #{{ $requestOp->id }} (Cabang: {{ $requestOp->cabang->nama_cabang ?? '-' }})</p>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Barang fisik yang diminta oleh cabang telah otomatis dimuat ke tabel PO di bawah ini. Anda dapat melengkapi harga beli supplier lalu langsung checkout atau simpan sebagai draft.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.pembelian.create') }}" class="text-xs font-bold text-amber-800 hover:text-amber-950 underline whitespace-nowrap">
                    Batal Link Request
                </a>
            </div>
        @elseif(request('barang_id') || request('items'))
            <div class="bg-blue-50 border border-blue-200 text-blue-900 p-4 rounded-2xl shadow-sm flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-bolt-lightning"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Akses Cepat Restok dari Rekomendasi Dynamic Buffer EWS</p>
                        <p class="text-xs text-blue-700 mt-0.5">
                            Produk dan kuantitas rekomendasi restok telah otomatis dimasukkan ke tabel pemesanan di bawah. Silakan masukkan harga satuan atau total tagihan pembelian.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.pembelian.create') }}" class="text-xs font-bold text-blue-700 hover:text-blue-900 underline whitespace-nowrap">
                    Reset Form
                </a>
            </div>
        @endif

        <!-- Main Form PO -->
        <form id="formPembelian" action="{{ route('admin.pembelian.draft') }}" method="POST" @submit="handleSubmit($event)">
            @csrf
            
            @if(request('request_id'))
                <input type="hidden" name="request_id" value="{{ request('request_id') }}">
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                
                <!-- 1. Informasi Supplier (Opsional) -->
                <div>
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-truck-field text-indigo-600"></i>
                        <span>1. Informasi Supplier / Vendor (Opsional)</span>
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Nama Supplier
                            </label>
                            <input type="text" 
                                   name="nama_supplier" 
                                   x-model="namaSupplier"
                                   placeholder="Contoh: PT Surya Distribusi / Vendor Utama (Boleh Kosong)" 
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none text-sm font-semibold text-slate-800">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Kontak / No. HP Supplier
                            </label>
                            <input type="text" 
                                   name="kontak_supplier" 
                                   x-model="kontakSupplier"
                                   placeholder="Contoh: 0812-3456-7890 (Boleh Kosong)" 
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none text-sm font-semibold text-slate-800">
                        </div>
                    </div>
                </div>

                <!-- 2. Tambah Barang Fisik ke PO -->
                <div class="pt-6 border-t border-slate-100">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-cart-plus text-indigo-600"></i>
                        <span>2. Tambah Barang Fisik ke Daftar Pesanan</span>
                    </h2>
                    <p class="text-xs text-slate-500 mb-3">
                        Pilih produk fisik yang akan dipesan ke supplier. Harga satuan atau total belanja dapat diisi manual pada tabel di bawah.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 items-stretch">
                        <!-- Dropdown Pilih Barang -->
                        <div class="flex-1">
                            <select x-model="selectedBarangId" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none text-sm font-semibold text-slate-800 bg-white">
                                <option value="">-- Pilih Barang Fisik yang Ingin Dipesan --</option>
                                <template x-for="b in barangs" :key="b.id">
                                    <option :value="b.id" x-text="b.nama_barang + ' (' + b.tipe_barang + ') — Stok Gudang: ' + (b.stok_gudang || 0) + ' pcs'"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Tombol Tambah Barang -->
                        <button type="button" 
                                @click="addSelectedBarang()" 
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 shadow-sm active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Tambah ke PO</span>
                        </button>
                    </div>
                </div>

                <!-- 3. Tabel Rincian Pesanan (Input Harga Satuan atau Total Beli 2-Arah) -->
                <div class="pt-6 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-boxes-stacked text-indigo-600"></i>
                                <span>3. Rincian Barang Dipesan & Pengisian Harga</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Bebas isi: jika Anda isi <b>Harga Satuan</b>, <b>Total</b> akan otomatis dihitung. Atau jika Anda isi <b>Total Beli</b>, <b>Harga Satuan</b> otomatis menyesuaikan.
                            </p>
                        </div>
                        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl">
                            Total: <span class="text-indigo-600 font-black" x-text="selectedItems.length"></span> Item
                        </span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4">Nama Barang Fisik</th>
                                    <th class="py-3.5 px-3 text-center w-40">Qty Pesan</th>
                                    <th class="py-3.5 px-3 text-right w-44">Harga Satuan (Rp)</th>
                                    <th class="py-3.5 px-4 text-right w-48">Total Beli / Subtotal (Rp)</th>
                                    <th class="py-3.5 px-3 text-center w-14">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <template x-for="(row, index) in selectedItems" :key="row.id + '_' + index">
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <!-- Barang Fisik Info -->
                                        <td class="py-3.5 px-4">
                                            <input type="hidden" :name="'items['+index+'][barang_fisik_id]'" :value="row.id">
                                            <div class="font-extrabold text-slate-800" x-text="row.nama_barang"></div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600" x-text="row.tipe_barang"></span>
                                                <span class="text-[11px] text-slate-400">
                                                    Stok Gudang: <b class="text-slate-600 font-mono" x-text="(row.stok_gudang || 0) + ' pcs'"></b>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Qty Pesan dengan Stepper +/- yang User-Friendly -->
                                        <td class="py-3.5 px-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button type="button" 
                                                        @click="stepQty(row, -1)" 
                                                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-sm flex items-center justify-center transition active:scale-95 cursor-pointer">
                                                    -
                                                </button>
                                                <input type="number" 
                                                       :name="'items['+index+'][qty]'" 
                                                       x-model.number="row.qty" 
                                                       @input="onQtyChange(row)"
                                                       min="1" 
                                                       required
                                                       class="w-16 text-center py-1 px-1 rounded-lg border border-slate-200 focus:border-indigo-500 outline-none font-black text-slate-800 text-sm">
                                                <button type="button" 
                                                        @click="stepQty(row, 1)" 
                                                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-sm flex items-center justify-center transition active:scale-95 cursor-pointer">
                                                    +
                                                </button>
                                            </div>
                                            
                                            <!-- Tombol Akselerasi Cepat Qty -->
                                            <div class="flex items-center justify-center gap-1 mt-1.5">
                                                <button type="button" @click="stepQty(row, 5)" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-500 transition cursor-pointer">+5</button>
                                                <button type="button" @click="stepQty(row, 10)" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-500 transition cursor-pointer">+10</button>
                                                <button type="button" @click="stepQty(row, 50)" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-500 transition cursor-pointer">+50</button>
                                            </div>
                                        </td>

                                        <!-- Harga Satuan Input (Dua Arah: Mengisi Satuan -> Subtotal Terhitung) -->
                                        <td class="py-3.5 px-3 text-right">
                                            <input type="number" 
                                                   x-model.number="row.harga_satuan" 
                                                   @input="onHargaSatuanChange(row)"
                                                   min="0" 
                                                   placeholder="Rp Satuan"
                                                   class="w-full text-right py-1.5 px-2.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 outline-none font-bold text-slate-800 text-sm">
                                            <span class="text-[10px] text-slate-400 block mt-1" x-text="row.harga_satuan ? 'Rp ' + formatRupiah(row.harga_satuan) + ' /unit' : 'Bisa dikosongkan'"></span>
                                        </td>

                                        <!-- Subtotal Input (Dua Arah: Mengisi Subtotal -> Harga Satuan Terhitung) -->
                                        <td class="py-3.5 px-4 text-right">
                                            <input type="number" 
                                                   :name="'items['+index+'][subtotal]'" 
                                                   x-model.number="row.subtotal" 
                                                   @input="onSubtotalChange(row)"
                                                   min="0" 
                                                   placeholder="Total Belanja"
                                                   class="w-full text-right py-1.5 px-2.5 rounded-lg border-2 border-indigo-200 bg-indigo-50/30 focus:border-indigo-600 focus:bg-white outline-none font-black text-indigo-700 text-sm">
                                            <span class="text-[11px] font-black text-indigo-600 block mt-1" x-text="row.subtotal ? 'Rp ' + formatRupiah(row.subtotal) : 'Rp 0'"></span>
                                        </td>

                                        <!-- Hapus Item Row -->
                                        <td class="py-3.5 px-3 text-center">
                                            <button type="button" 
                                                    @click="removeItem(index)" 
                                                    class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold transition flex items-center justify-center cursor-pointer active:scale-95" 
                                                    title="Hapus barang dari daftar pesanan">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Kosong State -->
                                <tr x-show="selectedItems.length === 0">
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-cart-shopping text-3xl mb-2 text-slate-300 block"></i>
                                        <p class="font-bold text-slate-500">Belum ada barang dalam daftar PO.</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Pilih produk pada dropdown di atas atau klik tombol akses cepat dari halaman EWS / Request Cabang.</p>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                                <tr>
                                    <td colspan="3" class="py-4 px-4 text-right font-extrabold text-slate-700 text-sm">
                                        Grand Total Tagihan PO:
                                    </td>
                                    <td class="py-4 px-4 text-right font-black text-xl text-indigo-700 font-mono">
                                        <span x-text="'Rp ' + formatRupiah(grandTotal)"></span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- 4. Sumber Dana / Metode Pembayaran -->
                <div class="pt-6 border-t border-slate-100">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-wallet text-indigo-600"></i>
                        <span>4. Sumber Dana / Kas Pembayaran</span>
                    </h2>
                    <p class="text-xs text-slate-500 mb-4">
                        Pilih kas yang digunakan untuk membayar pesanan. Saldo kas pusat akan terpotong saat checkout dilakukan.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Option A: Cash Kas Pusat -->
                        <label class="relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition"
                               :class="metodeBayar === 'cash_pusat' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/10' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" 
                                   name="metode_pembayaran" 
                                   value="cash_pusat" 
                                   x-model="metodeBayar" 
                                   class="mt-1 text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3 space-y-1">
                                <span class="font-black text-sm text-slate-800 block">💵 Cash Kas Pusat (Fisik)</span>
                                <span class="text-xs text-slate-500 block">
                                    Saldo Kas Saat Ini: 
                                    <b class="text-slate-900 font-mono">Rp {{ number_format($kasPusat->saldo ?? 0, 0, ',', '.') }}</b>
                                </span>
                                <template x-if="grandTotal > {{ (int) ($kasPusat->saldo ?? 0) }}">
                                    <span class="inline-block text-[11px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 mt-1">
                                        ⚠️ Saldo Kas Pusat tidak mencukupi untuk checkout langsung
                                    </span>
                                </template>
                            </div>
                        </label>

                        <!-- Option B: Dompet Digital Pusat -->
                        <label class="relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition"
                               :class="metodeBayar === 'apk_layanan' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/10' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" 
                                   name="metode_pembayaran" 
                                   value="apk_layanan" 
                                   x-model="metodeBayar" 
                                   class="mt-1 text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3 space-y-1 w-full">
                                <span class="font-black text-sm text-slate-800 block">📱 Dompet Digital Pusat (Transfer/Saldo)</span>
                                <span class="text-xs text-slate-500 block">Pembayaran via transfer antar-rekening saldo server</span>
                                
                                <!-- Dompet Digital Selector -->
                                <div x-show="metodeBayar === 'apk_layanan'" class="pt-2">
                                    <select name="apk_layanan_id" 
                                            x-model="apkLayananId"
                                            class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none text-xs font-semibold text-slate-800 bg-white">
                                        <option value="">-- Pilih Akun Dompet Digital --</option>
                                        @foreach($dompetPusat as $dompet)
                                            <option value="{{ $dompet->id }}">
                                                {{ $dompet->masterAplikasiLayanan->nama_layanan ?? $dompet->masterAplikasiLayanan->nama_aplikasi }} (Saldo: Rp {{ number_format($dompet->saldo_sekarang, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 5. Tombol Aksi Simpan & Checkout Bayar -->
                <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-3">
                    <!-- Tombol Simpan Draft (Bayar Nanti) -->
                    <button type="submit" 
                            name="action" 
                            value="draft" 
                            class="w-full sm:w-1/3 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold rounded-xl transition text-sm flex items-center justify-center gap-2 cursor-pointer active:scale-95 shadow-2xs">
                        <i class="fa-solid fa-floppy-disk text-slate-500"></i>
                        <span>Simpan sebagai Draft PO</span>
                    </button>

                    <!-- Tombol Checkout & Bayar Sekarang -->
                    <button type="submit" 
                            name="action" 
                            value="langsung_beli" 
                            class="w-full sm:w-2/3 py-3.5 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl transition text-sm flex items-center justify-center gap-2 shadow-md hover:shadow-lg cursor-pointer active:scale-95">
                        <i class="fa-solid fa-circle-check text-base"></i>
                        <span>Checkout & Bayar Sekarang (Stok Masuk Gudang)</span>
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pembelianData', () => ({
        barangs: @json($barangs),
        selectedBarangId: '',
        namaSupplier: '{{ old('nama_supplier', '') }}',
        kontakSupplier: '{{ old('kontak_supplier', '') }}',
        selectedItems: [],
        metodeBayar: '{{ old('metode_pembayaran', 'cash_pusat') }}',
        apkLayananId: '{{ old('apk_layanan_id', '') }}',

        init() {
            // 1. Cek apakah ada old items saat form reload dari error validasi
            let oldItems = @json(old('items', []));
            if (oldItems && oldItems.length > 0) {
                this.selectedItems = oldItems.map(item => {
                    let master = this.barangs.find(b => b.id == item.barang_fisik_id);
                    let qty = parseInt(item.qty) || 1;
                    let subtotal = parseFloat(item.subtotal) || 0;
                    let satuan = qty > 0 ? Math.round(subtotal / qty) : 0;
                    return {
                        id: item.barang_fisik_id,
                        nama_barang: master ? master.nama_barang : 'Barang #' + item.barang_fisik_id,
                        tipe_barang: master ? master.tipe_barang : 'Fisik',
                        stok_gudang: master ? master.stok_gudang : 0,
                        qty: qty,
                        harga_satuan: satuan > 0 ? satuan : '',
                        subtotal: subtotal > 0 ? subtotal : ''
                    };
                });
                return;
            }

            // 2. Prefill dari Request Operasional Cabang jika ada
            @if(isset($requestOp) && $requestOp && $requestOp->detail)
                let reqDetails = @json($requestOp->detail);
                reqDetails.forEach(dtl => {
                    if (dtl.barang_fisik_id) {
                        let master = this.barangs.find(b => b.id == dtl.barang_fisik_id);
                        if (master) {
                            this.selectedItems.push({
                                id: master.id,
                                nama_barang: master.nama_barang,
                                tipe_barang: master.tipe_barang,
                                stok_gudang: master.stok_gudang || 0,
                                qty: parseInt(dtl.nominal_atau_qty) || 1,
                                harga_satuan: '',
                                subtotal: ''
                            });
                        }
                    }
                });
                if (this.selectedItems.length > 0) return;
            @endif

            // 3. Prefill multi items dari URL (?items=[{"barang_id": 3, "qty": 10}, ...])
            let urlParams = new URLSearchParams(window.location.search);
            let itemsParam = urlParams.get('items');
            if (itemsParam) {
                try {
                    let parsed = JSON.parse(itemsParam);
                    if (Array.isArray(parsed)) {
                        parsed.forEach(it => {
                            let item = this.barangs.find(b => b.id == it.barang_id);
                            if (item) {
                                this.selectedItems.push({
                                    id: item.id,
                                    nama_barang: item.nama_barang,
                                    tipe_barang: item.tipe_barang,
                                    stok_gudang: item.stok_gudang || 0,
                                    qty: parseInt(it.qty) || 1,
                                    harga_satuan: '',
                                    subtotal: ''
                                });
                            }
                        });
                        if (this.selectedItems.length > 0) return;
                    }
                } catch(e) {}
            }

            // 4. Prefill single item dari URL (?barang_id=X&qty=Y)
            let prefillId = urlParams.get('barang_id');
            let prefillQty = parseInt(urlParams.get('qty')) || 1;
            if (prefillId) {
                let item = this.barangs.find(b => b.id == prefillId);
                if (item) {
                    this.selectedItems.push({
                        id: item.id,
                        nama_barang: item.nama_barang,
                        tipe_barang: item.tipe_barang,
                        stok_gudang: item.stok_gudang || 0,
                        qty: Math.max(1, prefillQty),
                        harga_satuan: '',
                        subtotal: ''
                    });
                }
            }
        },

        addSelectedBarang() {
            if (!this.selectedBarangId) {
                alert('Pilih barang fisik terlebih dahulu dari daftar.');
                return;
            }
            let item = this.barangs.find(b => b.id == this.selectedBarangId);
            if (!item) return;

            let exists = this.selectedItems.find(x => x.id === item.id);
            if (exists) {
                exists.qty++;
                if (exists.harga_satuan) {
                    exists.subtotal = Math.round(exists.qty * exists.harga_satuan);
                }
            } else {
                this.selectedItems.push({
                    id: item.id,
                    nama_barang: item.nama_barang,
                    tipe_barang: item.tipe_barang,
                    stok_gudang: item.stok_gudang || 0,
                    qty: 1,
                    harga_satuan: '',
                    subtotal: ''
                });
            }
            this.selectedBarangId = '';
        },

        removeItem(index) {
            this.selectedItems.splice(index, 1);
        },

        stepQty(row, delta) {
            let next = (parseInt(row.qty) || 1) + delta;
            row.qty = Math.max(1, next);
            this.onQtyChange(row);
        },

        onQtyChange(row) {
            if (row.qty < 1) row.qty = 1;
            // Jika harga satuan sudah diisi, sesuaikan subtotal
            if (row.harga_satuan !== '' && row.harga_satuan !== null && row.harga_satuan !== undefined) {
                row.subtotal = Math.round(row.qty * parseFloat(row.harga_satuan));
            } else if (row.subtotal !== '' && row.subtotal !== null && row.subtotal !== undefined) {
                // Sebaliknya jika hanya subtotal yang diisi, sesuaikan harga satuan
                row.harga_satuan = Math.round(parseFloat(row.subtotal) / row.qty);
            }
        },

        // Dua Arah: Input Harga Satuan -> Subtotal otomatis terhitung
        onHargaSatuanChange(row) {
            if (row.harga_satuan === '' || row.harga_satuan === null || isNaN(row.harga_satuan)) {
                row.subtotal = '';
                return;
            }
            let satuan = parseFloat(row.harga_satuan) || 0;
            let qty = parseInt(row.qty) || 1;
            row.subtotal = Math.round(satuan * qty);
        },

        // Dua Arah: Input Total Beli / Subtotal -> Harga Satuan otomatis terhitung
        onSubtotalChange(row) {
            if (row.subtotal === '' || row.subtotal === null || isNaN(row.subtotal)) {
                row.harga_satuan = '';
                return;
            }
            let subtotal = parseFloat(row.subtotal) || 0;
            let qty = parseInt(row.qty) || 1;
            row.harga_satuan = Math.round(subtotal / qty);
        },

        get grandTotal() {
            return this.selectedItems.reduce((acc, row) => acc + (parseFloat(row.subtotal) || 0), 0);
        },

        formatRupiah(val) {
            return new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
        },

        handleSubmit(e) {
            if (this.selectedItems.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 barang fisik untuk dipesan.');
                return false;
            }

            let submitter = e.submitter;
            let action = submitter ? submitter.value : 'draft';

            // Jika langsung bayar, validasi kelengkapan harga dan saldo kas
            if (action === 'langsung_beli') {
                for (let i = 0; i < this.selectedItems.length; i++) {
                    let it = this.selectedItems[i];
                    if (!it.subtotal || it.subtotal <= 0) {
                        e.preventDefault();
                        alert('Harap isi harga satuan atau total belanja untuk barang "' + it.nama_barang + '" sebelum checkout.');
                        return false;
                    }
                }

                if (this.metodeBayar === 'apk_layanan' && !this.apkLayananId) {
                    e.preventDefault();
                    alert('Silakan pilih salah satu Akun Dompet Digital Pusat terlebih dahulu.');
                    return false;
                }
            }
        }
    }));
});
</script>
@endsection
