@extends('layouts.app')
@section('title', 'Riwayat Transaksi Kasir')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar { z-index: 50; }
        .main-content { min-width: 0; }
        :root {
            --primary: #3498db;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            display: flex;
        }
    </style>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="p-6 max-w-7xl mx-auto" x-data="ownerTransaksiData()">
    <!-- HEADER -->
    <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <a href="{{ route('owner.laporan_pusat.index') }}" class="bg-gray-100 p-2 rounded-lg text-gray-600 hover:bg-gray-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800">Riwayat Transaksi Kasir</h1>
                <p class="text-sm text-gray-500">Log seluruh transaksi penjualan dari kasir tiap cabang (Read-Only).</p>
            </div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-4">
        <form action="{{ route('owner.laporan_pusat.transaksi') }}" method="GET" class="flex flex-wrap items-end gap-3 w-full flex-1">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Cabang</label>
                <select name="cabang_id" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none min-w-[150px]">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-indigo-700 transition">Filter Data</button>
            @if(request('start_date') || request('end_date') || request('cabang_id'))
                <a href="{{ route('owner.laporan_pusat.transaksi') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>
        <div class="flex gap-2">
            <a href="{{ route('owner.laporan_pusat.transaksi.export_pdf') }}?cabang_id={{ request('cabang_id') }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" target="_blank" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-red-600 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> PDF
            </a>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 p-6">
        <div style="overflow-x: auto;">
            @if(count($transaksis) > 0)
            <table class="w-full text-sm text-left border-collapse" style="min-width: 600px; width: 100%;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px 10px; border-bottom: 2px solid #ddd;">Waktu Transaksi</th>
                        <th style="padding: 12px 10px; border-bottom: 2px solid #ddd;">Cabang</th>
                        <th style="padding: 12px 10px; border-bottom: 2px solid #ddd;">Metode Pembayaran</th>
                        <th style="padding: 12px 10px; border-bottom: 2px solid #ddd;">Total Diskon</th>
                        <th style="padding: 12px 10px; border-bottom: 2px solid #ddd;">Total Tagihan</th>
                        <th style="padding: 12px 10px; border-bottom: 2px solid #ddd; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksis as $trx)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px 10px; color: #555;">
                            <div>{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->translatedFormat('d M Y H:i') }}</div>
                            <div style="margin-top: 5px; font-size: 11px;">
                                <span style="font-weight: bold; color: #333;">Oleh: {{ $trx->kasir->name ?? 'Sistem' }}</span>
                                @if($trx->is_bantuan_pusat)
                                    <span style="background: #f1c40f; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-left: 5px;">Via Admin Pusat</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 12px 10px; font-weight: bold;">
                            {{ $trx->cabang->nama_cabang ?? 'Pusat' }}
                        </td>
                        <td style="padding: 12px 10px;">
                            <span style="background: {{ $trx->metode_pembayaran === 'qris' ? '#3498db' : '#2ecc71' }}; color: white; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 11px; text-transform: uppercase;">
                                {{ str_replace('_', ' ', $trx->metode_pembayaran) }}
                            </span>
                        </td>
                        <td style="padding: 12px 10px; color: #e74c3c; font-weight: bold;">
                            @if($trx->total_diskon > 0)
                                - Rp {{ number_format($trx->total_diskon, 0, ',', '.') }}
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td style="padding: 12px 10px; font-weight: bold; color: var(--primary);">
                            Rp {{ number_format($trx->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td style="padding: 12px 10px; text-align: center;">
                            <button @click='openDetail(@json($trx))' style="background: #34495e; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; cursor: pointer; transition: 0.2s;">
                                📄 Lihat Struk
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="text-align: center; padding: 40px 20px; background: #f9f9f9; border-radius: 8px; border: 1px dashed #ccc; margin-top: 10px;">
                <div style="font-size: 40px; margin-bottom: 10px; color: #ccc;">📂</div>
                <p style="font-weight: bold; color: #666; margin: 0;">Belum ada riwayat transaksi</p>
                <p style="font-size: 12px; color: #999; margin-top: 5px;">Atau tidak ada transaksi pada rentang tanggal/cabang yang dipilih.</p>
            </div>
            @endif
        </div>
        
        <div class="mt-4">
            {{ $transaksis->withQueryString()->links() }}
        </div>
    </div>

    <!-- Modal Detail Struk (Alpine) -->
    <div class="modal-overlay" x-show="showModal" style="display: none;" x-transition>
        <div style="background: white; width: 340px; border-radius: 12px; overflow: hidden; box-shadow: 0 15px 30px rgba(0,0,0,0.3);" @click.outside="closeDetail">
            <!-- Receipt Header -->
            <div style="background: #f8f9fa; padding: 25px 20px; text-align: center; border-bottom: 2px dashed #ccc; position: relative;">
                <div style="width: 50px; height: 50px; background: #e8f0fe; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto;">
                    <span style="font-size: 24px;">📄</span>
                </div>
                <h3 style="margin: 0 0 5px 0; color: #333; font-size: 18px; font-weight: bold;">Struk Transaksi</h3>
                <p style="margin: 0; font-size: 12px; color: #666; font-weight: bold;" x-text="selectedTransaksi ? formatDate(selectedTransaksi.created_at) : ''"></p>
                <p style="margin: 0; font-size: 12px; color: var(--primary); font-weight: bold;" x-text="selectedTransaksi ? 'Cabang: ' + (selectedTransaksi.cabang ? selectedTransaksi.cabang.nama_cabang : 'Pusat') : ''"></p>
            </div>
            
            <!-- Receipt Body -->
            <div style="padding: 20px; max-height: 400px; overflow-y: auto; background: white;">
                <template x-if="selectedTransaksi">
                    <div>
                        <!-- Items -->
                        <template x-for="detail in selectedTransaksi.detail_transaksi" :key="detail.id">
                            <div style="padding-bottom: 15px; border-bottom: 1px solid #f1f1f1; margin-bottom: 15px;">
                                <div style="font-weight: bold; color: #333; font-size: 14px;" x-text="detail.barang?.nama_barang ?? 'Item'"></div>
                                <div style="font-size: 11px; color: #777; margin-top: 2px;">
                                    <span x-text="detail.barang?.tipe_barang || ''"></span>
                                    <template x-if="detail.barang?.category">
                                        <span> - <span x-text="detail.barang.category.name"></span></span>
                                    </template>
                                </div>
                                <template x-if="detail.barang?.jenis_transaksi === 'cash_out'">
                                    <div style="background: #f8f9fa; border: 1px dashed #ccc; padding: 10px; margin-top: 8px; border-radius: 6px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 5px;">
                                            <span style="color: #666;">Nominal (Modal):</span>
                                            <span style="font-weight: bold; color: #444;" x-text="'Rp ' + formatRupiah(detail.hpp_satuan)"></span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 5px;">
                                            <span style="color: #666;">Biaya Admin / Fee:</span>
                                            <span style="font-weight: bold; color: #444;" x-text="'Rp ' + formatRupiah(detail.harga_jual_final - detail.hpp_satuan)"></span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; border-top: 1px solid #ddd; padding-top: 5px; margin-top: 5px;">
                                            <span style="color: #333; font-weight: bold;">Subtotal:</span>
                                            <span style="font-weight: bold; color: #333;" x-text="'Rp ' + formatRupiah(detail.harga_jual_final)"></span>
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="detail.barang?.jenis_transaksi !== 'cash_out'">
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-top: 5px;">
                                        <span style="color: #666;" x-text="detail.kuantitas + ' x Rp ' + formatRupiah(detail.harga_jual_final)"></span>
                                        <span style="font-weight: bold; color: #444;" x-text="'Rp ' + formatRupiah(detail.kuantitas * detail.harga_jual_final)"></span>
                                    </div>
                                </template>
                                <template x-if="(detail?.diskon || 0) > 0">
                                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-top: 5px; background: #fdedec; padding: 4px 8px; border-radius: 4px;">
                                        <span style="color: #c0392b; font-weight: bold;">Diskon</span>
                                        <span style="color: #c0392b; font-weight: bold;" x-text="'- Rp ' + formatRupiah(detail.diskon)"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <!-- Summary -->
                        <div style="padding-top: 5px; font-size: 14px;">
                            <div style="display: flex; justify-content: space-between; color: #555; margin-bottom: 5px;">
                                <span>Subtotal:</span>
                                <span style="font-weight: bold; color: #333;" x-text="'Rp ' + formatRupiah((selectedTransaksi?.total_tagihan || 0) + (selectedTransaksi?.total_diskon || 0))"></span>
                            </div>
                            
                            <template x-if="(selectedTransaksi?.total_diskon || 0) > 0">
                                <div style="display: flex; justify-content: space-between; color: #e74c3c; margin-bottom: 5px;">
                                    <span>Total Diskon:</span>
                                    <span style="font-weight: bold;" x-text="'- Rp ' + formatRupiah(selectedTransaksi?.total_diskon)"></span>
                                </div>
                            </template>
                        </div>
                        <!-- Total Tagihan -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-top: 15px; margin-top: 10px; border-top: 2px dashed #ccc;">
                            <span style="color: #666; font-weight: bold; text-transform: uppercase; font-size: 13px;">Total Tagihan</span>
                            <span style="font-size: 24px; font-weight: bold; color: var(--primary);" x-text="'Rp ' + formatRupiah(selectedTransaksi?.total_tagihan)"></span>
                        </div>
                        
                        <!-- Payment Details -->
                        <template x-if="selectedTransaksi?.metode_pembayaran === 'cash'">
                            <div style="background: #f8f9fa; border-radius: 8px; padding: 12px; margin-top: 15px; border: 1px solid #eee; font-size: 13px;">
                                <div style="display: flex; justify-content: space-between; color: #555; margin-bottom: 5px;">
                                    <span>Tunai:</span>
                                    <span style="font-weight: bold; color: #333;" x-text="'Rp ' + formatRupiah(selectedTransaksi?.uang_diterima)"></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: #555;">
                                    <span>Kembali:</span>
                                    <span style="font-weight: bold; color: #333;" x-text="'Rp ' + formatRupiah(selectedTransaksi?.kembalian)"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="selectedTransaksi?.metode_pembayaran && selectedTransaksi?.metode_pembayaran !== 'cash'">
                            <div style="margin-top: 15px; text-align: center;">
                                <span style="display: inline-block; padding: 5px 15px; background: #e8f0fe; color: var(--primary); font-weight: bold; font-size: 12px; text-transform: uppercase; border-radius: 20px; border: 1px solid #d2e3fc;" x-text="'Pembayaran: ' + selectedTransaksi?.metode_pembayaran.replace('_', ' ')"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            
            <!-- Receipt Footer -->
            <div style="padding: 15px; background: #f8f9fa; border-top: 1px solid #eee;">
                <button @click="closeDetail" style="width: 100%; background: var(--primary); color: white; font-weight: bold; padding: 12px; border-radius: 8px; border: none; cursor: pointer; font-size: 15px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function ownerTransaksiData() {
        return {
            showModal: false,
            selectedTransaksi: null,
            
            formatDate(dateStr) {
                if(!dateStr) return '';
                const date = new Date(dateStr);
                const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
                return date.toLocaleDateString('id-ID', options).replace(/\./g, ':');
            },
            
            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number || 0);
            },
            
            openDetail(transaksi) {
                this.selectedTransaksi = transaksi;
                this.showModal = true;
            },
            
            closeDetail() {
                this.showModal = false;
                this.selectedTransaksi = null;
            }
        }
    }
</script>
@endpush
