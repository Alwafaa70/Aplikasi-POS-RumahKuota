@extends('layouts.app')
@section('title', 'POS Kasir - Dashboard')

@push('styles')
<style>
    /* Status Network */
    .network-status { display: flex; align-items: center; gap: 5px; background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; }
    .dot-online { background-color: #2ecc71; box-shadow: 0 0 5px #2ecc71; }
    .dot-offline { background-color: #e74c3c; box-shadow: 0 0 5px #e74c3c; }

    /* Layout Split Screen */
    .pos-container { display: grid; grid-template-columns: 2fr 1.1fr; height: calc(100vh - 120px); margin: -10px; }
    
    /* Left Panel (Products) */
    .products-panel { background: #f4f7f6; padding: 20px; overflow-y: auto; }
    .search-bar { width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; font-size: 16px; margin-bottom: 15px; box-sizing: border-box; }
    .filter-pills { display: flex; gap: 10px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 5px; }
    .pill { padding: 8px 16px; background: white; border: 1px solid var(--border); border-radius: 20px; cursor: pointer; font-size: 14px; white-space: nowrap; transition: 0.2s; }
    .pill.active { background: var(--primary); color: white; border-color: var(--primary); }
    
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
    .product-card { background: white; border: 1px solid var(--border); border-radius: 8px; padding: 15px; text-align: center; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer; transition: transform 0.1s, box-shadow 0.1s;}
    .product-card:active { transform: scale(0.97); }
    .product-card:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-color: var(--primary); }
    .product-name { font-size: 13px; font-weight: bold; margin-bottom: 8px; height: 38px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .product-price { color: var(--primary); font-size: 14px; font-weight: bold; margin-bottom: 5px; }
    .product-stock { font-size: 11px; color: #888; }
    
    /* Right Panel (Cart) */
    .cart-panel { background: white; border-left: 1px solid var(--border); display: flex; flex-direction: column; height: 100%; }
    .cart-header { padding: 20px; border-bottom: 1px solid var(--border); }
    .cart-header h2 { margin: 0; font-size: 18px; color: var(--text); }
    
    .cart-items { flex: 1; overflow-y: auto; padding: 15px; }
    .cart-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding: 12px 0; }
    .cart-item-info { flex: 1; }
    .cart-item-name { font-weight: bold; font-size: 14px; margin-bottom: 4px; }
    .cart-item-price { font-size: 13px; color: #666; }
    .cart-item-remove { background: #fee2e2; color: #e74c3c; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; }
    
    .cart-footer { padding: 20px; border-top: 1px solid var(--border); background: #fafafa; }
    .summary-row { display: flex; justify-content: space-between; font-size: 15px; margin-bottom: 10px; }
    .summary-total { font-size: 22px; font-weight: bold; color: var(--primary); border-top: 2px dashed #ddd; padding-top: 10px; margin-top: 5px; }
    
    .payment-group { margin-top: 15px; }
    .payment-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: bold; color: #555; }
    .payment-select, .payment-input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; margin-bottom: 10px; box-sizing: border-box; }
    
    .btn-checkout { background: var(--primary); color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: 0.2s;}
    .btn-checkout:disabled { background: #ccc; cursor: not-allowed; }
    
    /* Modal Struk */
    .modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; }
    .modal.show { display: flex; }
    .receipt-card { background: white; width: 320px; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    .receipt-header { background: #f8f9fa; padding: 20px; text-align: center; border-bottom: 1px dashed #ccc; }
    .receipt-header h3 { margin: 0 0 5px 0; color: var(--text); }
    .receipt-body { padding: 20px; }
    .receipt-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
    .receipt-total-row { font-size: 18px; font-weight: bold; margin: 15px 0; border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; padding: 10px 0; }
    .receipt-change { font-size: 20px; font-weight: bold; color: #2ecc71; text-align: center; margin-top: 15px; }
    .receipt-footer { padding: 15px; background: #f8f9fa; text-align: center; }
    .receipt-btn { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }

    .modal-digital { background: white; padding: 20px; border-radius: 8px; width: 80%; max-width: 320px; text-align: center; }
    .modal-digital input { width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 16px; text-align: center;}
    .modal-digital select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 15px; background: white; }
    .modal-digital .apk-saldo { font-size: 12px; color: #27ae60; font-weight: bold; text-align: left; margin-top: -5px; margin-bottom: 10px; }
</style>
@endpush

@section('content')
<div x-data="posData()">
    <!-- POS Main Container -->
    <div class="pos-container">
        <!-- Left: Product List -->
        <div class="products-panel">
            <input type="text" class="search-bar" placeholder="Cari nama barang..." x-model="searchQuery">
            
            <!-- Filter Kategori Dinamis -->
            <div style="margin-bottom: 15px;">
                <!-- Filter Tipe Barang -->
                <div class="filter-pills" style="margin-bottom: 10px; overflow-x: auto; white-space: nowrap;">
                    <div class="pill" :class="{ 'active': selectedTipeBarang === '' }" @click="selectTipe('')">Semua Tipe</div>
                    <template x-for="tipe in uniqueTipeBarang" :key="tipe">
                        <div class="pill" :class="{ 'active': selectedTipeBarang === tipe }" @click="selectTipe(tipe)" x-text="tipe"></div>
                    </template>
                </div>

                <!-- Parent Categories -->
                <div class="filter-pills" style="margin-bottom: 10px; overflow-x: auto; white-space: nowrap;" x-show="availableParentCategories.length > 0">
                    <div class="pill" :class="{ 'active': selectedParentId === '' }" @click="selectParent('')">Semua Kategori</div>
                    <template x-for="cat in availableParentCategories" :key="cat.id">
                        <div class="pill" :class="{ 'active': selectedParentId === cat.id }" @click="selectParent(cat.id)" x-text="cat.name"></div>
                    </template>
                </div>
                
                <!-- Sub Categories 1 -->
                <div class="filter-pills" style="margin-bottom: 10px; overflow-x: auto; white-space: nowrap;" x-show="availableSub1.length > 0">
                    <div class="pill" :class="{ 'active': selectedSub1Id === '' }" @click="selectSub1('')">Semua <span x-text="selectedParentName"></span></div>
                    <template x-for="sub in availableSub1" :key="sub.id">
                        <div class="pill" :class="{ 'active': selectedSub1Id === sub.id }" @click="selectSub1(sub.id)" x-text="sub.name"></div>
                    </template>
                </div>
                
                <!-- Sub Categories 2 -->
                <div class="filter-pills" style="margin-bottom: 10px; overflow-x: auto; white-space: nowrap;" x-show="availableSub2.length > 0">
                    <div class="pill" :class="{ 'active': selectedSub2Id === '' }" @click="selectedSub2Id = ''">Semua <span x-text="selectedSub1Name"></span></div>
                    <template x-for="sub2 in availableSub2" :key="sub2.id">
                        <div class="pill" :class="{ 'active': selectedSub2Id === sub2.id }" @click="selectedSub2Id = sub2.id" x-text="sub2.name"></div>
                    </template>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="product-grid">
                <template x-for="item in filteredProducts" :key="item.id + item.type">
                    <div class="product-card" @click="handleProductClick(item)" :style="!isOnline && item.type === 'digital' ? 'opacity: 0.5; pointer-events: none;' : ''">
                        <div>
                            <div class="product-name" x-text="item.nama"></div>
                            <div class="product-stock" x-show="item.type === 'fisik'" x-text="'Stok: ' + item.stok"></div>
                            <div class="product-stock" x-show="item.type === 'digital'" style="color: #2ecc71;">Non-Fisik</div>
                        </div>
                        <div class="product-price" x-text="'Rp ' + formatRupiah(item.harga)"></div>
                    </div>
                </template>
            </div>
            
            <!-- Bantuan Pusat Section (Floating Badge) -->
            <div x-show="bantuanPusatPending > 0" x-cloak style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
                <a href="{{ route('kasir.request_operasional.index') }}" style="display: flex; align-items: center; background: #e74c3c; color: white; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 10px rgba(231, 76, 60, 0.4); animation: pulse 2s infinite;">
                    <span style="font-size: 18px; margin-right: 10px;">🆘</span>
                    Bantuan Pusat Selesai
                    <span style="background: white; color: #e74c3c; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; justify-content: center; align-items: center; font-size: 14px; margin-left: 10px;" x-text="bantuanPusatPending"></span>
                </a>
                <style>
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.05); }
                        100% { transform: scale(1); }
                    }
                </style>
            </div>

            <!-- Mutasi Stok Section -->
            @if(!$mutasiDikirim->isEmpty() || !$mutasiCashDikirim->isEmpty())
            <div style="margin-top: 30px;">
                <div style="background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; margin-bottom: 5px;">📦</div>
                    <strong style="display: block; margin-bottom: 10px;">Ada Mutasi Masuk!</strong>
                    <div style="font-size: 13px; margin-bottom: 15px;">
                        Terdapat {{ $mutasiDikirim->count() }} mutasi barang dan {{ $mutasiCashDikirim->count() }} mutasi uang tunai yang perlu Anda konfirmasi penerimaannya.
                    </div>
                    <a href="{{ route('kasir.penerimaan.index') }}" style="background: #3498db; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">
                        Buka Halaman Penerimaan
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Cart -->
        <div class="cart-panel">
            <div class="cart-header">
                <h2>Keranjang <span x-show="cart.length > 0" x-text="'(' + cart.length + ')'"></span></h2>
            </div>
            
            <div class="cart-items">
                <div x-show="cart.length === 0" style="text-align: center; color: #888; margin-top: 50px;">
                    Keranjang kosong
                </div>
                
                <template x-for="(item, index) in cart" :key="index">
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name" x-text="item.nama"></div>
                            <div class="cart-item-price" style="display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                                <div x-show="item.type === 'fisik'" style="display: flex; align-items: center; gap: 5px; background: #eee; border-radius: 4px; padding: 2px;">
                                    <button style="border: none; background: white; width: 22px; height: 22px; border-radius: 2px; cursor: pointer; font-weight: bold;" @click="if(item.kuantitas > 1) { item.kuantitas--; item.diskonTotal = 0; }">-</button>
                                    <span style="font-size: 13px; font-weight: bold; min-width: 20px; text-align: center;" x-text="item.kuantitas"></span>
                                    <button style="border: none; background: white; width: 22px; height: 22px; border-radius: 2px; cursor: pointer; font-weight: bold;" @click="if(item.kuantitas < item.stok) { item.kuantitas++; item.diskonTotal = 0; }">+</button>
                                </div>
                                <span x-show="item.type === 'digital_b'" x-text="item.nomor_tujuan + ' | '"></span>
                                <span x-show="item.type === 'digital_a' && item.apk_nama" style="font-size: 11px; color: #8e44ad; font-weight: bold;" x-text="'via ' + item.apk_nama + ' | Modal: Rp ' + formatRupiah(item.harga_modal)"></span>
                                <div>
                                    <span x-show="item.type === 'fisik' && item.diskonTotal > 0" style="text-decoration: line-through; color: #999; font-size: 11px; margin-right: 5px;" x-text="'Rp ' + formatRupiah(item.hargaAsli * item.kuantitas)"></span>
                                    <span style="font-weight: bold; color: var(--primary);" x-text="'Rp ' + formatRupiah(item.type === 'fisik' ? ((item.hargaAsli * item.kuantitas) - (item.diskonTotal || 0)) : (item.harga * item.kuantitas))"></span>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <button x-show="item.tipe_barang === 'Fisik ACC'" 
                                    style="background: #f39c12; color: white; border: none; padding: 6px 8px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold;" 
                                    @click="openTawarModal(index)">🏷️ Diskon</button>
                            <button class="cart-item-remove" @click="removeFromCart(index)">X</button>
                        </div>
                    </div>
                </template>
            </div>
            
            <div class="cart-footer">
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span x-text="'Rp ' + formatRupiah(cartTotal)"></span>
                </div>
                
                <div class="payment-group">
                    <label>Metode Pembayaran</label>
                    <select class="payment-select" x-model="metodePembayaran" @change="uangDiterima = ''">
                        <option value="cash">Cash (Uang Tunai)</option>
                        <option value="qris" x-bind:disabled="!isOnline">QRIS (Langsung Lunas)</option>
                    </select>
                </div>
                
                <div class="payment-group" x-show="metodePembayaran === 'cash'">
                    <label>Uang Diterima (Rp)</label>
                    <input type="number" class="payment-input" x-model.number="uangDiterima" placeholder="Cth: 50000">
                </div>
                
                <!-- Error msg -->
                <div x-show="errorMessage" style="color: #e74c3c; font-size: 13px; font-weight: bold; margin-bottom: 10px; text-align: center;" x-text="errorMessage"></div>
                
                <button class="btn-checkout" @click="processCheckout" :disabled="cart.length === 0 || processing">
                    <span x-show="!processing">PROSES TRANSAKSI</span>
                    <span x-show="processing">MEMPROSES...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Transaksi Digital B -->
    <div class="modal" :class="showDigitalBModal ? 'show' : ''">
        <div class="modal-digital">
            <h3 style="margin-top:0; color: #2c3e50;">💸 <span x-text="selectedDigitalB?.nama"></span></h3>
            
            <div style="background: #f1f8ff; padding: 10px; border-radius: 6px; font-size: 12px; margin-bottom: 15px; color: #34495e; text-align: left;">
                <b x-show="selectedDigitalB?.jenis_transaksi === 'cash_in'">Transaksi Cash In (Topup/Transfer):</b>
                <b x-show="selectedDigitalB?.jenis_transaksi === 'cash_out'">Transaksi Cash Out (Tarik Tunai):</b><br>
                <span x-show="selectedDigitalB?.jenis_transaksi === 'cash_in'">Uang fisik masuk laci, saldo Apk Layanan Cabang berkurang.</span>
                <span x-show="selectedDigitalB?.jenis_transaksi === 'cash_out'">Uang fisik keluar laci (diberikan ke pelanggan), saldo Pusat bertambah (pelanggan TF ke pusat).</span>
            </div>

            <!-- Pilih Apk Layanan (Dinamis: Cabang untuk CashIn, Pusat untuk CashOut) -->
            <label style="font-size: 13px; font-weight: bold; text-align: left; display: block; margin-bottom: 5px; color: #555;">
                Pilih Apk Layanan <span x-text="selectedDigitalB?.jenis_transaksi === 'cash_in' ? 'Cabang' : 'Pusat'"></span>
            </label>
            <select x-model="digitalBApkId" @change="updateApkSaldoDigitalB()">
                <option value="">-- Pilih Aplikasi --</option>
                <template x-if="selectedDigitalB?.jenis_transaksi === 'cash_in'">
                    <template x-for="apk in apkLayananList" :key="apk.id">
                        <option :value="apk.id" x-text="apk.nama + ' (Saldo: Rp ' + formatRupiah(apk.saldo) + ')'"></option>
                    </template>
                </template>
                <template x-if="selectedDigitalB?.jenis_transaksi === 'cash_out'">
                    <template x-for="apk in apkLayananPusatList" :key="apk.id">
                        <option :value="apk.id" x-text="apk.nama"></option>
                    </template>
                </template>
            </select>
            <div class="apk-saldo" x-show="digitalBApkId && selectedDigitalB?.jenis_transaksi === 'cash_in'" x-text="'💰 Saldo tersedia: Rp ' + formatRupiah(selectedApkSaldo)"></div>
            <div class="apk-saldo" x-show="digitalBApkId && selectedDigitalB?.jenis_transaksi === 'cash_out'" style="color: #8e44ad;">ℹ️ Pelanggan harus transfer ke rekening pusat di aplikasi ini.</div>

            <label style="font-size: 13px; font-weight: bold; text-align: left; display: block; margin-bottom: 5px; color: #555; margin-top: 10px;">Nominal (Rp)</label>
            <input type="number" x-model.number="digitalBHargaModal" placeholder="Nominal Modal (Cth: 50000)">
            
            <label x-show="selectedDigitalB?.jenis_transaksi === 'cash_in'" style="font-size: 13px; font-weight: bold; text-align: left; display: block; margin-bottom: 5px; color: #555;">Nomor Tujuan</label>
            <input type="text" x-show="selectedDigitalB?.jenis_transaksi === 'cash_in'" x-model="digitalTujuan" placeholder="0812xxxxxx (Opsional)">

            <div style="background: #eafaf1; padding: 10px; border-radius: 6px; margin-top: 10px; margin-bottom: 10px; border: 1px dashed #2ecc71; text-align: center;" x-show="digitalBHargaModal > 0">
                <div style="font-size: 12px; color: #27ae60; font-weight: bold;">Total Bayar Pelanggan:</div>
                <div style="font-size: 18px; color: #2ecc71; font-weight: bold;" x-text="'Rp ' + formatRupiah(calculatedTotalBayarB)"></div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button style="flex:1; padding: 10px; border-radius: 4px; border:none; background:#e74c3c; color:white; font-weight:bold; cursor:pointer;" @click="showDigitalBModal = false">Batal</button>
                <button style="flex:1; padding: 10px; border-radius: 4px; border:none; background:#2ecc71; color:white; font-weight:bold; cursor:pointer;" @click="processDigitalB">Proses & Checkout</button>
            </div>
            
            <div x-show="selectedDigitalB?.jenis_transaksi === 'cash_in'" style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 15px;">
                <div style="font-size: 12px; color: #666; margin-bottom: 10px;">Saldo kasir tidak cukup / limit harian tercapai? Minta bantuan Admin Pusat.</div>
                <button style="width: 100%; padding: 10px; border-radius: 4px; border:none; background:#3498db; color:white; font-weight:bold; cursor:pointer;" @click="window.location.href='{{ route('kasir.request_operasional.index') }}?tab=bantuan_digital_b'">Request Bantuan Pusat</button>
            </div>
        </div>
    </div>

    <!-- Modal Input Digital A (Pilih Apk Layanan + Harga Modal) -->
    <div class="modal" :class="showDigitalAModal ? 'show' : ''">
        <div class="modal-digital">
            <h3 style="margin-top:0; color: #2c3e50;">📱 Pembelian Digital A</h3>
            <div style="font-size: 14px; color: #555; margin-bottom: 10px;" x-text="selectedDigitalA?.nama"></div>
            <div style="font-size: 16px; font-weight: bold; color: var(--primary); margin-bottom: 15px;" x-text="'Harga Jual: Rp ' + formatRupiah(selectedDigitalA?.harga)"></div>

            <label style="font-size: 13px; font-weight: bold; text-align: left; display: block; margin-bottom: 5px; color: #555;">Pilih Apk Layanan</label>
            <select x-model="digitalAApkId" @change="updateApkSaldo()">
                <option value="">-- Pilih Aplikasi --</option>
                <template x-for="apk in apkLayananList" :key="apk.id">
                    <option :value="apk.id" x-text="apk.nama + ' (Saldo: Rp ' + formatRupiah(apk.saldo) + ')'"></option>
                </template>
            </select>
            <div class="apk-saldo" x-show="digitalAApkId" x-text="'💰 Saldo tersedia: Rp ' + formatRupiah(selectedApkSaldo)"></div>

            <label style="font-size: 13px; font-weight: bold; text-align: left; display: block; margin-bottom: 5px; color: #555;">Harga Modal (Rp)</label>
            <input type="number" x-model.number="digitalAHargaModal" placeholder="Cth: 18000" style="margin-top: 5px;">

            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button style="flex:1; padding: 10px; border-radius: 4px; border:none; background:#e74c3c; color:white; font-weight:bold; cursor:pointer;" @click="showDigitalAModal = false">Batal</button>
                <button style="flex:1; padding: 10px; border-radius: 4px; border:none; background:#2ecc71; color:white; font-weight:bold; cursor:pointer;" @click="addToCartDigitalA">Masukkan</button>
            </div>
        </div>
    </div>

    <!-- Modal Tawar Fisik ACC -->
    <div class="modal" :class="showTawarModal ? 'show' : ''">
        <div class="modal-digital">
            <h3 style="margin-top:0;">Beri Diskon / Tawar</h3>
            <div style="font-size:14px; margin-bottom:10px; color:#555;" x-text="selectedTawar?.nama"></div>
            <div style="font-size:12px; margin-bottom:15px; color:#e74c3c; font-weight:bold;">
                Harga Asli Total (<span x-text="selectedTawar?.kuantitas"></span> pcs): Rp <span x-text="formatRupiah(selectedTawar?.hargaAsli * selectedTawar?.kuantitas)"></span>
            </div>
            <label style="font-size: 13px; font-weight: bold; text-align: left; display: block; margin-bottom: 5px;">Harga Final (Kesepakatan) Total</label>
            <input type="number" x-model.number="tawarNominal" placeholder="Contoh: 140000">
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button style="flex:1; padding: 10px; border-radius: 4px; border:none; background:#e74c3c; color:white; font-weight:bold; cursor:pointer;" @click="showTawarModal = false">Batal</button>
                <button style="flex:1; padding: 10px; border-radius: 4px; border:none; background:#2ecc71; color:white; font-weight:bold; cursor:pointer;" @click="applyTawar">Terapkan Diskon</button>
            </div>
        </div>
    </div>

    <!-- Modal Struk -->
    <div class="modal" :class="showReceipt ? 'show' : ''">
        <div class="receipt-card">
            <div class="receipt-header">
                <h3>POS SYSTEM</h3>
                <div style="font-size: 12px; color: #666;" x-text="receiptData.waktu"></div>
            </div>
            <div class="receipt-body">
                <div style="text-align: center; margin-bottom: 15px;">
                    <div style="background: #2ecc71; color: white; display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                        LUNAS
                    </div>
                </div>

                <!-- Items Purchased -->
                <div style="border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; padding: 10px 0; margin-bottom: 10px;">
                    <template x-for="(item, index) in receiptData.items" :key="index">
                        <div style="margin-bottom: 8px;">
                            <div style="font-weight: bold; font-size: 13px;" x-text="item.nama"></div>
                            <div x-show="item.kategori" style="font-size: 10px; color: #8e44ad; font-weight: bold; margin-bottom: 2px;" x-text="item.kategori"></div>
                            <div style="font-size: 11px; color: #666; display: flex; justify-content: space-between;">
                                <span>
                                    <span x-text="item.kuantitas + ' x ' + formatRupiah(item.hargaAsli || item.harga)"></span>
                                    <span x-show="item.tipe_barang" x-text="' (' + item.tipe_barang + ')'"></span>
                                </span>
                                <span style="font-weight: bold; color: #333;" x-text="formatRupiah((item.hargaAsli || item.harga) * item.kuantitas)"></span>
                            </div>
                            <div x-show="item.diskonTotal > 0" style="font-size: 11px; color: #e74c3c; display: flex; justify-content: space-between;">
                                <span>Potongan Diskon</span>
                                <span style="font-weight: bold;" x-text="'- Rp ' + formatRupiah(item.diskonTotal)"></span>
                            </div>
                            <div x-show="item.nomor_tujuan" style="font-size: 11px; color: #666;" x-text="'Tujuan: ' + item.nomor_tujuan"></div>
                        </div>
                    </template>
                </div>
                
                <div class="receipt-row">
                    <span>Metode</span>
                    <span style="text-transform: uppercase; font-weight: bold;" x-text="receiptData.metode"></span>
                </div>
                
                <div class="receipt-row receipt-total-row">
                    <span>Total Tagihan</span>
                    <span x-text="'Rp ' + formatRupiah(receiptData.total)"></span>
                </div>
                
                <div x-show="receiptData.metode === 'cash'">
                    <div class="receipt-row">
                        <span>Dibayar</span>
                        <span x-text="'Rp ' + formatRupiah(receiptData.uangDiterima)"></span>
                    </div>
                    <div class="receipt-change" x-text="'Kembali: Rp ' + formatRupiah(receiptData.kembalian)"></div>
                </div>
                
                <div x-show="receiptData.offline" style="margin-top: 15px; text-align: center; font-size: 11px; color: #e74c3c; font-weight: bold; border: 1px dashed #e74c3c; padding: 5px;">
                    * Disimpan Offline *
                </div>
            </div>
            <div class="receipt-footer">
                <button class="receipt-btn" @click="closeReceipt">Selesai (Tutup)</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Data Injeksi dari Controller
    const stokFisikData = @json($stokFisik);
    const produkDigitalAData = @json($produkDigitalA);
    const produkDigitalBData = @json($produkDigitalB);
    const categoriesTreeData = @json($categoriesTree);
    const apkLayananCabangData = @json($apkLayananCabang);
    const apkLayananPusatData = @json($apkLayananPusat);
    const bantuanPusatPending = {{ $bantuanPusatPending }};

    // Build flat category map with full path from tree
    function buildCategoryMap(nodes, parentPath = '') {
        let map = {};
        for (const node of nodes) {
            const path = parentPath ? parentPath + ' > ' + node.name : node.name;
            map[node.id] = path;
            if (node.children && node.children.length > 0) {
                Object.assign(map, buildCategoryMap(node.children, path));
            }
        }
        return map;
    }
    const categoryMap = buildCategoryMap(categoriesTreeData);
    
    // Format master data untuk Alpine
    const masterProducts = [
        ...stokFisikData.map(item => ({
            id: item.barang_id,
            type: 'fisik',
            tipe_barang: item.barang.tipe_barang,
            category_id: item.barang.category_id,
            kategori: categoryMap[item.barang.category_id] || '',
            nama: item.barang.nama_barang,
            harga: item.barang.harga_jual,
            hpp: item.hpp_tertinggi,
            stok: item.jumlah_stok
        })),
        ...produkDigitalAData.map(item => ({
            id: item.id,
            type: 'digital_a',
            tipe_barang: item.tipe_barang,
            category_id: item.category_id,
            kategori: categoryMap[item.category_id] || '',
            nama: item.nama_barang,
            harga: item.harga_jual,
            stok: 'Non-Fisik'
        })),
        ...produkDigitalBData.map(item => ({
            id: item.id,
            type: 'digital_b',
            tipe_barang: item.tipe_barang,
            category_id: item.category_id,
            kategori: categoryMap[item.category_id] || '',
            nama: item.nama_barang,
            harga: 0,
            stok: 'Non-Fisik',
            jenis_transaksi: item.jenis_transaksi,
            ranges: item.ranges_digital_b || []
        }))
    ];

    document.addEventListener('alpine:init', () => {
        Alpine.data('posData', () => ({
            isOnline: navigator.onLine,
            searchQuery: '',
            categoriesTree: categoriesTreeData,
            selectedTipeBarang: '',
            selectedParentId: '',
            selectedSub1Id: '',
            selectedSub2Id: '',
            
            products: masterProducts,
            cart: [],
            metodePembayaran: 'cash',
            uangDiterima: '',
            errorMessage: '',
            processing: false,
            
            // Modal Digital
            showDigitalModal: false,
            selectedDigital: null,
            digitalNominal: '',
            digitalTujuan: '',
            
            // Modal Tawar Fisik
            showTawarModal: false,
            selectedTawar: null,
            tawarNominal: '',
            selectedCartIndex: null,
            
            // Modal Receipt
            showReceipt: false,
            receiptData: {},

            // Modal Digital A
            showDigitalAModal: false,
            selectedDigitalA: null,
            digitalAApkId: '',
            digitalAHargaModal: '',
            selectedApkSaldo: 0,
            apkLayananList: apkLayananCabangData.map(a => ({
                id: a.id,
                nama: a.master_aplikasi_layanan?.nama_layanan ?? 'Unknown',
                saldo: parseFloat(a.saldo_sekarang) || 0
            })),

            // Modal Digital B & Bantuan Pusat
            showDigitalBModal: false,
            selectedDigitalB: null,
            digitalBHargaModal: '',
            digitalBApkId: '',
            showBantuanPusatModal: false,
            bantuanKeterangan: '',
            apkLayananPusatList: apkLayananPusatData.map(a => ({
                id: a.id,
                nama: a.master_aplikasi_layanan?.nama_layanan ?? 'Unknown',
                saldo: parseFloat(a.saldo_sekarang) || 0
            })),
            bantuanPusatPending: bantuanPusatPending,

            init() {
                // Listen to network changes
                window.addEventListener('online', () => { this.isOnline = true; });
                window.addEventListener('offline', () => { 
                    this.isOnline = false;
                    if(this.metodePembayaran !== 'cash') this.metodePembayaran = 'cash';
                });
            },
            
            get availableSub1() {
                if (!this.selectedParentId) return [];
                const parent = this.categoriesTree.find(c => c.id == this.selectedParentId);
                // Filter sub categories that have products in the selected tipe
                if (!parent || !parent.children) return [];
                
                let validCatIds = [];
                if (this.selectedTipeBarang) {
                    validCatIds = this.products.filter(p => p.tipe_barang === this.selectedTipeBarang && p.category_id).map(p => p.category_id);
                } else {
                    validCatIds = this.products.filter(p => p.category_id).map(p => p.category_id);
                }

                return parent.children.filter(sub => {
                    const descendantIds = this.getAllDescendantCategoryIds(sub.id);
                    return descendantIds.some(id => validCatIds.includes(id));
                });
            },
            
            get availableSub2() {
                if (!this.selectedSub1Id) return [];
                const sub1 = this.availableSub1.find(c => c.id == this.selectedSub1Id);
                return sub1 && sub1.children ? sub1.children : [];
            },
            
            get selectedParentName() {
                const parent = this.categoriesTree.find(c => c.id == this.selectedParentId);
                return parent ? '(' + parent.name + ')' : '';
            },
            
            get selectedSub1Name() {
                const sub1 = this.availableSub1.find(c => c.id == this.selectedSub1Id);
                return sub1 ? '(' + sub1.name + ')' : '';
            },
            
            selectParent(id) {
                this.selectedParentId = id;
                this.selectedSub1Id = '';
                this.selectedSub2Id = '';
            },
            
            selectTipe(tipe) {
                this.selectedTipeBarang = tipe;
                this.selectedParentId = '';
                this.selectedSub1Id = '';
                this.selectedSub2Id = '';
            },
            
            get uniqueTipeBarang() {
                const tipes = this.products.map(p => p.tipe_barang).filter(t => t);
                return [...new Set(tipes)];
            },
            
            get availableParentCategories() {
                // Jangan tampilkan kategori jika tipe barang belum dipilih secara spesifik
                if (!this.selectedTipeBarang) return [];
                
                // Return categories from categoriesTree that have products matching the selectedTipeBarang
                let validCatIds = [];
                if (this.selectedTipeBarang) {
                    validCatIds = this.products.filter(p => p.tipe_barang === this.selectedTipeBarang && p.category_id).map(p => p.category_id);
                }
                
                return this.categoriesTree.filter(cat => {
                    const descendantIds = this.getAllDescendantCategoryIds(cat.id);
                    return descendantIds.some(id => validCatIds.includes(id));
                });
            },
            
            selectSub1(id) {
                this.selectedSub1Id = id;
                this.selectedSub2Id = '';
            },
            
            getAllDescendantCategoryIds(parentId) {
                let ids = [parentId];
                const findChildren = (nodeId, nodes) => {
                    for (let node of nodes) {
                        if (node.id == nodeId) {
                            if (node.children) {
                                node.children.forEach(child => {
                                    ids.push(child.id);
                                    findChildren(child.id, [child]);
                                });
                            }
                        } else if (node.children) {
                            findChildren(nodeId, node.children);
                        }
                    }
                };
                findChildren(parentId, this.categoriesTree);
                return ids;
            },

            get filteredProducts() {
                return this.products.filter(item => {
                    const matchSearch = item.nama.toLowerCase().includes(this.searchQuery.toLowerCase());
                    
                    const matchTipe = this.selectedTipeBarang === '' || item.tipe_barang === this.selectedTipeBarang;

                    let matchKategori = true;
                    // Hirarki Filter Logic
                    if (this.selectedSub2Id) {
                        matchKategori = item.category_id == this.selectedSub2Id;
                    } else if (this.selectedSub1Id) {
                        const validIds = this.getAllDescendantCategoryIds(this.selectedSub1Id);
                        matchKategori = validIds.includes(item.category_id);
                    } else if (this.selectedParentId) {
                        const validIds = this.getAllDescendantCategoryIds(this.selectedParentId);
                        matchKategori = validIds.includes(item.category_id);
                    }
                    
                    return matchSearch && matchTipe && matchKategori;
                });
            },

            get cartTotal() {
                return this.cart.reduce((total, item) => {
                    let sub = (item.hargaAsli || item.harga) * parseInt(item.kuantitas);
                    let diskon = item.diskonTotal || 0;
                    return total + (sub - diskon);
                }, 0);
            },

            get calculatedTotalBayarB() {
                if (!this.selectedDigitalB || !this.digitalBHargaModal) return 0;
                let nominal = parseInt(this.digitalBHargaModal) || 0;
                let fee = 0;
                let ranges = this.selectedDigitalB.ranges || [];
                for (let r of ranges) {
                    if (nominal >= parseFloat(r.min_nominal) && nominal <= parseFloat(r.max_nominal)) {
                        fee = parseInt(r.fee);
                        break;
                    }
                }
                return nominal + fee;
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number || 0);
            },

            handleProductClick(item) {
                if(!this.isOnline && (item.type === 'digital_a' || item.type === 'digital_b')) return; // Cegah klik offline
                
                if(item.type === 'fisik') {
                    // Cek stok logic sederhana
                    const existingItem = this.cart.find(c => c.id === item.id && c.type === 'fisik');
                    if (existingItem) {
                        if (existingItem.kuantitas >= item.stok) {
                            alert('Stok tidak mencukupi!');
                            return;
                        }
                        existingItem.kuantitas++;
                        existingItem.diskonTotal = 0; // Reset diskon jika nambah barang
                    } else {
                        this.cart.push({ ...item, kuantitas: 1, hargaAsli: item.harga, diskonTotal: 0 });
                    }
                } else if (item.type === 'digital_a') {
                    // Digital A: tampilkan modal pilih apk layanan + harga modal
                    this.selectedDigitalA = item;
                    this.digitalAApkId = '';
                    this.digitalAHargaModal = '';
                    this.selectedApkSaldo = 0;
                    this.showDigitalAModal = true;
                } else if (item.type === 'digital_b') {
                    this.selectedDigitalB = item;
                    this.digitalBHargaModal = '';
                    this.digitalBApkId = '';
                    this.digitalTujuan = ''; // reset if any
                    
                    if (item.jenis_transaksi === 'cash_in') {
                        this.selectedApkSaldo = 0;
                    } else {
                        // cash_out uses Pusat apk
                        this.selectedApkSaldo = 0; // we'll use same variable for convenience or separate it
                    }
                    this.showDigitalBModal = true;
                }
            },

            updateApkSaldo() {
                if (!this.digitalAApkId) { this.selectedApkSaldo = 0; return; }
                const apk = this.apkLayananList.find(a => a.id == this.digitalAApkId);
                this.selectedApkSaldo = apk ? apk.saldo : 0;
            },

            updateApkSaldoDigitalB() {
                if (!this.digitalBApkId) { this.selectedApkSaldo = 0; return; }
                
                let apkList = this.selectedDigitalB.jenis_transaksi === 'cash_in' 
                    ? this.apkLayananList 
                    : this.apkLayananPusatList;
                    
                const apk = apkList.find(a => a.id == this.digitalBApkId);
                this.selectedApkSaldo = apk ? apk.saldo : 0;
            },

            addToCartDigitalA() {
                if (!this.digitalAApkId) {
                    alert('Pilih Apk Layanan terlebih dahulu!');
                    return;
                }
                if (!this.digitalAHargaModal || this.digitalAHargaModal <= 0) {
                    alert('Harga Modal harus diisi dan lebih dari 0!');
                    return;
                }
                if (this.digitalAHargaModal > this.selectedApkSaldo) {
                    alert('Saldo Apk Layanan tidak mencukupi! Saldo: Rp ' + this.formatRupiah(this.selectedApkSaldo));
                    return;
                }

                const apk = this.apkLayananList.find(a => a.id == this.digitalAApkId);
                this.cart.push({
                    ...this.selectedDigitalA,
                    kuantitas: 1,
                    apk_layanan_id: parseInt(this.digitalAApkId),
                    apk_nama: apk ? apk.nama : '',
                    harga_modal: this.digitalAHargaModal
                });

                // Kurangi saldo di tampilan
                if (apk) {
                    apk.saldo -= this.digitalAHargaModal;
                }

                this.showDigitalAModal = false;
            },

            openTawarModal(index) {
                this.selectedCartIndex = index;
                this.selectedTawar = this.cart[index];
                this.tawarNominal = (this.cart[index].hargaAsli * this.cart[index].kuantitas) - (this.cart[index].diskonTotal || 0); // Default ke total setelah diskon sebelumnya
                this.showTawarModal = true;
            },

            applyTawar() {
                if(!this.tawarNominal || this.tawarNominal <= 0) {
                    alert('Harga final total tidak boleh kosong atau nol!');
                    return;
                }
                
                const batasModalTotal = this.selectedTawar.hpp * this.selectedTawar.kuantitas;
                if(this.tawarNominal < batasModalTotal) {
                    alert('Harga ditolak oleh sistem! Harga total yang Anda masukkan berada di bawah modal/batas wajar.');
                    return;
                }

                this.cart[this.selectedCartIndex].diskonTotal = (this.selectedTawar.hargaAsli * this.selectedTawar.kuantitas) - this.tawarNominal;
                this.showTawarModal = false;
            },

            async processDigitalB() {
                if (!this.digitalBApkId) {
                    Swal.fire('Oops', 'Pilih Apk Layanan terlebih dahulu!', 'warning');
                    return;
                }
                if (!this.digitalBHargaModal || this.digitalBHargaModal <= 0) {
                    Swal.fire('Oops', 'Harga Modal harus diisi dan lebih dari 0!', 'warning');
                    return;
                }
                
                // Cek saldo
                if (this.selectedDigitalB.jenis_transaksi === 'cash_in') {
                    if (this.digitalBHargaModal > this.selectedApkSaldo) {
                        Swal.fire('Oops', 'Saldo Apk Layanan (Cabang) tidak mencukupi! Saldo: Rp ' + this.formatRupiah(this.selectedApkSaldo), 'error');
                        return;
                    }
                }

                const resultConfirm = await Swal.fire({
                    title: 'Konfirmasi Checkout',
                    text: `Apakah Anda yakin memproses ${this.selectedDigitalB.nama}? Uang yang harus dibayar pelanggan: Rp ${this.formatRupiah(this.calculatedTotalBayarB)}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Proses',
                    cancelButtonText: 'Batal'
                });
                
                if (!resultConfirm.isConfirmed) return;

                this.processing = true;
                try {
                    const response = await fetch('{{ route("kasir.transaksi.digital_b") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            barang_id: this.selectedDigitalB.id,
                            apk_layanan_id: this.digitalBApkId,
                            harga_modal: this.digitalBHargaModal,
                            nomor_tujuan: this.digitalTujuan
                        })
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        Swal.fire('Berhasil', result.message + "\nTotal Bayar: Rp " + this.formatRupiah(result.data.total_bayar || result.data.uang_keluar), 'success')
                        .then(() => {
                            this.showDigitalBModal = false;
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                    console.error(e);
                } finally {
                    this.processing = false;
                }
            },

            async processBantuanPusat() {
                if (!this.digitalBHargaModal || this.digitalBHargaModal <= 0) {
                    Swal.fire('Oops', 'Nominal topup/tf (harga modal) harus diisi dan lebih dari 0!', 'warning');
                    return;
                }

                const resultConfirm = await Swal.fire({
                    title: 'Kirim Request Bantuan',
                    text: `Kirim Request Bantuan Pusat untuk ${this.selectedDigitalB.nama}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Kirim Request',
                    cancelButtonText: 'Batal'
                });

                if (!resultConfirm.isConfirmed) return;

                this.processing = true;
                try {
                    const response = await fetch('{{ route("kasir.bantuan_pusat") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            barang_id: this.selectedDigitalB.id,
                            nominal: this.digitalBHargaModal,
                            nomor_tujuan: this.digitalTujuan,
                            keterangan: this.bantuanKeterangan
                        })
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        Swal.fire('Berhasil', result.message, 'success').then(() => {
                            this.showBantuanPusatModal = false;
                            this.showDigitalBModal = false;
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', result.message || result.error || 'Terjadi kesalahan', 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                    console.error(e);
                } finally {
                    this.processing = false;
                }
            },

            removeFromCart(index) {
                const item = this.cart[index];
                // Restore saldo apk jika Digital A
                if (item.type === 'digital_a' && item.apk_layanan_id) {
                    const apk = this.apkLayananList.find(a => a.id == item.apk_layanan_id);
                    if (apk) {
                        apk.saldo += item.harga_modal;
                    }
                }
                this.cart.splice(index, 1);
            },

            async processCheckout() {
                this.errorMessage = '';
                if(this.cart.length === 0) return;
                
                if(!confirm("Apakah Anda yakin ingin memproses transaksi ini?\n\nPastikan barang dan nominal uang sudah sesuai.")) {
                    return;
                }

                if(this.metodePembayaran === 'cash') {
                    if(!this.uangDiterima || this.uangDiterima < this.cartTotal) {
                        this.errorMessage = 'Uang diterima kurang dari total!';
                        return;
                    }
                }

                this.processing = true;

                // Siapkan data seragam
                // Kita anggap sekarang semua masuk store()
                const payload = {
                    cart: JSON.stringify(this.cart.map(c => {
                        if(c.type === 'fisik') return { tipe: 'fisik', barang_id: c.id, kuantitas: c.kuantitas, diskon_total: c.diskonTotal || 0 };
                        if(c.type === 'digital_a') return { tipe: 'digital_a', produk_digital_a_id: c.id, kuantitas: 1, apk_layanan_id: c.apk_layanan_id, harga_modal: c.harga_modal };
                        if(c.type === 'digital_b') return { tipe: 'digital_b', produk_digital_b_id: c.id, nominal: c.harga, nomor_tujuan: c.nomor_tujuan };
                    })),
                    metode_pembayaran: this.metodePembayaran,
                    uang_diterima: this.metodePembayaran === 'cash' ? this.uangDiterima : null,
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };

                const currentTotal = this.cartTotal;
                const currentUang = this.uangDiterima;
                const currentMetode = this.metodePembayaran;

                if (!this.isOnline) {
                    // TRANSAKSI OFFLINE
                    try {
                        // Memanggil fungsi dari pwa.js
                        if(typeof saveOfflineTransaction === 'function') {
                            await saveOfflineTransaction(payload);
                            
                            // Tampilkan Struk Dummy
                            this.receiptData = {
                                items: JSON.parse(JSON.stringify(this.cart)),
                                total: currentTotal,
                                metode: currentMetode,
                                uangDiterima: currentUang,
                                kembalian: currentUang - currentTotal,
                                waktu: new Date().toLocaleString('id-ID'),
                                offline: true
                            };

                            // Kurangi stok di tampilan
                            this.cart.forEach(c => {
                                if (c.type === 'fisik') {
                                    let p = this.products.find(prod => prod.id === c.id && prod.type === 'fisik');
                                    if (p) p.stok -= c.kuantitas;
                                }
                            });

                            this.cart = [];
                            this.uangDiterima = '';
                            this.showReceipt = true;
                        } else {
                            this.errorMessage = "PWA Script belum siap.";
                        }
                    } catch (e) {
                        this.errorMessage = 'Gagal menyimpan offline: ' + e;
                    }
                } else {
                    // TRANSAKSI ONLINE (Via Fetch JSON)
                    try {
                        const response = await fetch('/kasir/transaksi', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': payload._token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        if (response.ok) {
                            // Tampilkan Struk
                            this.receiptData = {
                                items: JSON.parse(JSON.stringify(this.cart)),
                                total: currentTotal,
                                metode: currentMetode,
                                uangDiterima: currentUang,
                                kembalian: currentMetode === 'cash' ? (currentUang - currentTotal) : 0,
                                waktu: new Date().toLocaleString('id-ID'),
                                offline: false
                            };

                            // Kurangi stok di tampilan
                            this.cart.forEach(c => {
                                if (c.type === 'fisik') {
                                    let p = this.products.find(prod => prod.id === c.id && prod.type === 'fisik');
                                    if (p) p.stok -= c.kuantitas;
                                }
                            });

                            this.cart = [];
                            this.uangDiterima = '';
                            this.showReceipt = true;
                        } else {
                            const errData = await response.json();
                            this.errorMessage = errData.message || 'Terjadi kesalahan di server.';
                        }
                    } catch (e) {
                        this.errorMessage = 'Koneksi ke server gagal.';
                    }
                }
                
                this.processing = false;
            },

            closeReceipt() {
                this.showReceipt = false;
            }
        }));
    });
</script>
@endpush
