@extends('layouts.app')
@section('title', 'Request Bantuan Operasional Pusat')

@push('styles')
<style>
    .tab-btn { padding: 10px 20px; font-weight: bold; border-bottom: 2px solid transparent; cursor: pointer; color: #7f8c8d; }
    .tab-btn.active { border-bottom: 2px solid var(--primary); color: var(--primary); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; color: #34495e; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
    .btn-submit { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; }
    .btn-submit:hover { background: #2980b9; }
</style>
@endpush

@section('content')
<div x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'isi_saldo', jenisTerpilih: '', aplikasiTerpilih: '', layananList: {{ \Illuminate\Support\Js::from($masterLayanan) }} }">
    <div class="card mb-4" style="display: flex; gap: 10px; border-bottom: 1px solid #eee; padding-bottom: 0;">
        <div class="tab-btn" :class="{ 'active': tab === 'isi_saldo' }" @click="tab = 'isi_saldo'">Isi Saldo Cabang</div>
        <div class="tab-btn" :class="{ 'active': tab === 'bantuan_digital_b' }" @click="tab = 'bantuan_digital_b'">Bantuan Transaksi (Digital B)</div>
        <div class="tab-btn" :class="{ 'active': tab === 'restok_barang' }" @click="tab = 'restok_barang'">Restok Barang</div>
        <div class="tab-btn" :class="{ 'active': tab === 'restok_cash' }" @click="tab = 'restok_cash'">Restok Cash (Laci)</div>
    </div>

    <div style="max-width: 800px; margin-bottom: 20px;">
        <!-- Area Form -->
        <div>
            <!-- Form Isi Saldo -->
    <div class="card" x-show="tab === 'isi_saldo'">
        <h3 style="margin-top: 0;">Request Isi Saldo Cabang</h3>
        <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 20px;">Meminta tambahan saldo aplikasi (seperti DANA, OVO, dll) dari Admin Pusat ke Cabang ini.</p>
        
        <form action="{{ route('kasir.request_operasional.store') }}" method="POST">
            @csrf
            <input type="hidden" name="jenis_request" value="isi_saldo">
            <div class="form-group">
                <label>Pilih Jenis Layanan</label>
                <select x-model="jenisTerpilih" @change="aplikasiTerpilih = ''" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="ewallet">E-Wallet</option>
                </select>
            </div>
            <div class="form-group" x-show="jenisTerpilih">
                <label>Pilih Aplikasi Layanan</label>
                <select name="master_aplikasi_layanan_id" x-model="aplikasiTerpilih" :required="jenisTerpilih !== ''">
                    <option value="">-- Pilih Layanan --</option>
                    <template x-for="layanan in layananList.filter(l => l.jenis_layanan === jenisTerpilih)" :key="layanan.id">
                        <option :value="layanan.id" x-text="layanan.nama_layanan"></option>
                    </template>
                </select>
            </div>
            <div class="form-group">
                <label>Nominal Saldo (Rp)</label>
                <input type="number" name="nominal_request" placeholder="Contoh: 1000000" min="1" required>
            </div>
            <div class="form-group">
                <label>Keterangan (Opsional)</label>
                <textarea name="keterangan" rows="2" placeholder="Contoh: Saldo harian hampir habis"></textarea>
            </div>
            <button type="submit" class="btn-submit">Kirim Request Isi Saldo</button>
        </form>
    </div>

    <!-- Form Bantuan Digital B -->
    <div class="card" x-show="tab === 'bantuan_digital_b'" style="display: none;">
        <h3 style="margin-top: 0;">Request Bantuan Transaksi Nominal Besar</h3>
        <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 20px;">Meminta Admin Pusat mengeksekusi transfer/topup ke pelanggan. Pelanggan membayar cash ke Kasir.</p>
        
        <form action="{{ route('kasir.bantuan_pusat') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Produk Digital B (Tujuan Top Up / TF)</label>
                <select name="barang_id" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($barangDigitalB as $barang)
                        <option value="{{ $barang->id }}">{{ $barang->nama_barang }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Nominal Transaksi (Rp)</label>
                <input type="number" name="nominal" placeholder="Contoh: 5000000" min="1" required>
            </div>
            <div class="form-group">
                <label>Nomor Tujuan / No. HP</label>
                <input type="text" name="nomor_tujuan" placeholder="Masukkan nomor tujuan">
            </div>
            <div class="form-group">
                <label>Keterangan Tambahan / A.N. Rekening</label>
                <textarea name="keterangan" rows="2" placeholder="Contoh: A.n Budi Susanto"></textarea>
            </div>
            <button type="submit" class="btn-submit" style="background: #e67e22;">Minta Bantuan Transaksi (Digital B)</button>
        </form>
    </div>

    <!-- Form Restok Barang -->
    <div class="card" x-show="tab === 'restok_barang'" style="display: none;">
        <h3 style="margin-top: 0;">Request Restok Barang Fisik</h3>
        
        <form action="{{ route('kasir.request_operasional.store') }}" method="POST">
            @csrf
            <input type="hidden" name="jenis_request" value="restok_barang">
            
            <div id="barang-wrapper">
                <div class="form-group" style="display: flex; gap: 10px;">
                    <select name="items[0][barang_fisik_id]" required style="flex: 2;">
                        <option value="">-- Pilih Barang --</option>
                        @foreach($barangFisik as $brg)
                            <option value="{{ $brg->id }}">{{ $brg->nama_barang }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="items[0][qty]" placeholder="Qty" min="1" required style="flex: 1;">
                </div>
            </div>
            
            <button type="button" onclick="addBarangRow()" style="background: #eee; padding: 5px 10px; border-radius: 4px; border: 1px solid #ccc; margin-bottom: 15px; cursor: pointer; font-size: 12px;">+ Tambah Barang Lain</button>

            <div class="form-group">
                <label>Keterangan Tambahan</label>
                <textarea name="keterangan" rows="2"></textarea>
            </div>
            <button type="submit" class="btn-submit" style="background: #27ae60;">Request Restok Barang</button>
        </form>
    </div>

    <!-- Form Restok Cash -->
    <div class="card" x-show="tab === 'restok_cash'" style="display: none;">
        <h3 style="margin-top: 0;">Request Uang Cash (Fisik)</h3>
        <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 20px;">Untuk modal laci (tarik tunai, dll).</p>
        
        <form action="{{ route('kasir.request_operasional.store') }}" method="POST">
            @csrf
            <input type="hidden" name="jenis_request" value="restok_cash">
            <div class="form-group">
                <label>Nominal Cash yang Diminta (Rp)</label>
                <input type="number" name="nominal_request" min="1" required>
            </div>
            <div class="form-group">
                <label>Alasan / Keterangan</label>
                <textarea name="keterangan" rows="2"></textarea>
            </div>
            <button type="submit" class="btn-submit" style="background: #8e44ad;">Request Uang Cash</button>
        </form>
    </div>
    </div> <!-- End of Form Container -->

    <!-- Tabel Riwayat Request (Di bawah area form) -->
    <div class="card" style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div>
                <h3 style="margin: 0; color: #2c3e50;">Riwayat Request Operasional</h3>
                <p style="font-size: 13px; color: #7f8c8d; margin-top: 5px; margin-bottom: 0;">Daftar request yang telah diajukan.</p>
            </div>
            <div>
                <a href="{{ route('kasir.request_operasional.pdf', request()->all()) }}" target="_blank" style="background: #e74c3c; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 13px;">
                    🖨️ Cetak Laporan
                </a>
            </div>
        </div>
        
        <!-- Filter Form -->
        <form action="{{ route('kasir.request_operasional.index') }}" method="GET" style="display: flex; gap: 15px; align-items: end; background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #eee;">
            <div style="flex: 1;">
                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #333;">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #333;">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="flex: 1.5;">
                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #333;">Jenis Request</label>
                <select name="jenis_request" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">-- Semua Jenis --</option>
                    <option value="isi_saldo" {{ request('jenis_request') === 'isi_saldo' ? 'selected' : '' }}>Isi Saldo Cabang</option>
                    <option value="bantuan_digital_b" {{ request('jenis_request') === 'bantuan_digital_b' ? 'selected' : '' }}>Bantuan Transaksi Digital</option>
                    <option value="restok_barang" {{ request('jenis_request') === 'restok_barang' ? 'selected' : '' }}>Restok Barang</option>
                    <option value="restok_cash" {{ request('jenis_request') === 'restok_cash' ? 'selected' : '' }}>Restok Cash (Laci)</option>
                </select>
            </div>
            <div>
                <button type="submit" style="background: var(--primary); color: white; border: none; padding: 9px 20px; border-radius: 4px; font-weight: bold; cursor: pointer;">Terapkan Filter</button>
                <a href="{{ route('kasir.request_operasional.index') }}" style="background: #95a5a6; color: white; padding: 9px 15px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; display: inline-block; margin-left: 5px;">Reset</a>
            </div>
        </form>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background: #f8f9fa; text-align: left; border-bottom: 2px solid #ddd;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">Waktu</th>
                        <th style="padding: 10px;">Jenis</th>
                        <th style="padding: 10px;">Detail (Nominal/Barang)</th>
                        <th style="padding: 10px;">Status</th>
                        <th style="padding: 10px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    @php 
                        $isBantuanDigitalB = class_basename($req) === 'TransaksiBantuanPusat'; 
                    @endphp
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;">#{{ $req->id }}</td>
                        <td style="padding: 10px; font-size: 13px;">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                        <td style="padding: 10px;">
                            <span style="font-weight: bold; text-transform: uppercase; font-size: 12px; color: #2c3e50;">
                                {{ $isBantuanDigitalB ? 'BANTUAN DIGITAL B' : str_replace('_', ' ', $req->jenis_request) }}
                            </span>
                            @if(!$isBantuanDigitalB && $req->tujuan_rekening)
                                <div style="font-size: 11px; color: #666; margin-top: 4px;">Ke: {{ $req->tujuan_rekening }}</div>
                            @elseif($isBantuanDigitalB && $req->nomor_tujuan)
                                <div style="font-size: 11px; color: #666; margin-top: 4px;">Ke: {{ $req->nomor_tujuan }}</div>
                            @endif
                        </td>
                        <td style="padding: 10px;">
                            @if($isBantuanDigitalB)
                                <div style="font-size: 12px; color: #34495e; font-weight: bold;">
                                    Top Up {{ $req->barang->nama_barang ?? 'Digital B' }}
                                </div>
                                <div style="font-size: 14px; color: #27ae60; font-weight: bold; margin-top: 4px;">
                                    Req: Rp {{ number_format($req->amount, 0, ',', '.') }}
                                </div>
                                @if($req->status !== 'menunggu_pusat')
                                    <div style="font-size: 11px; background: #e8f4fd; padding: 5px; border-radius: 4px; margin-top: 5px; border: 1px dashed #b8daff;">
                                        <strong>Modal:</strong> Rp {{ number_format($req->harga_modal_pusat, 0, ',', '.') }}<br>
                                        <span style="color: #c0392b; font-weight: bold;">Tagihan Pelanggan: Rp {{ number_format($req->total_bayar_pelanggan, 0, ',', '.') }}</span>
                                        @if($req->bukti_gambar)
                                            <br><a href="{{ asset('storage/' . $req->bukti_gambar) }}" target="_blank" style="color: #2980b9; text-decoration: underline;">Lihat Bukti Foto</a>
                                        @endif
                                    </div>
                                @endif
                            @elseif($req->jenis_request === 'isi_saldo')
                                <div style="font-size: 12px; color: #34495e; font-weight: bold;">
                                    Top Up {{ $req->masterAplikasiLayanan ? $req->masterAplikasiLayanan->nama_layanan : 'E-Wallet' }}
                                </div>
                                <div style="font-size: 14px; color: #27ae60; font-weight: bold; margin-top: 4px;">
                                    Rp {{ number_format($req->nominal_request, 0, ',', '.') }}
                                </div>
                            @elseif($req->nominal_request)
                                Rp {{ number_format($req->nominal_request, 0, ',', '.') }}
                                @if($req->total_biaya_pelanggan)
                                    <div style="font-size: 11px; color: #e74c3c; font-weight: bold; margin-top: 4px;">Terima Cash Pelanggan: Rp {{ number_format($req->total_biaya_pelanggan, 0, ',', '.') }}</div>
                                @endif
                            @else
                                <ul style="margin:0; padding-left:15px; font-size: 12px;">
                                @foreach($req->detail as $dtl)
                                    <li>{{ $dtl->barangFisik ? $dtl->barangFisik->nama_barang : 'Barang' }} ({{ $dtl->nominal_atau_qty }}x)</li>
                                @endforeach
                                </ul>
                            @endif
                        </td>
                        <td style="padding: 10px;">
                            @if(($isBantuanDigitalB && $req->status === 'menunggu_pusat') || (!$isBantuanDigitalB && $req->status_request === 'menunggu_admin'))
                                <span style="background: #f39c12; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">Menunggu Pusat</span>
                            @elseif(($isBantuanDigitalB && $req->status === 'diproses_admin') || (!$isBantuanDigitalB && $req->status_request === 'menunggu_konfirmasi_kasir'))
                                <span style="background: #3498db; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">Tunggu Konfirmasi Anda</span>
                            @elseif(($isBantuanDigitalB && $req->status === 'selesai') || (!$isBantuanDigitalB && $req->status_request === 'selesai'))
                                <span style="background: #2ecc71; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">Selesai</span>
                            @endif
                        </td>
                        <td style="padding: 10px;">
                            @if(($isBantuanDigitalB && $req->status === 'menunggu_pusat') || (!$isBantuanDigitalB && $req->status_request === 'menunggu_admin'))
                                <form action="{{ $isBantuanDigitalB ? route('kasir.bantuan_pusat.batal', $req->id) : route('kasir.request_operasional.batal', $req->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan request ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: bold;">Batalkan ❌</button>
                                </form>
                            @elseif($isBantuanDigitalB && $req->status === 'diproses_admin')
                                <form action="{{ route('kasir.bantuan_pusat.konfirmasi', $req->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin sudah menerima pembayaran dari pelanggan sejumlah Rp {{ number_format($req->total_bayar_pelanggan, 0, ',', '.') }}?');">
                                    @csrf
                                    <button type="submit" style="background: #2ecc71; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: bold; width: 100%;">Konfirmasi &<br>Checkout</button>
                                </form>
                            @elseif(!$isBantuanDigitalB && $req->status_request === 'menunggu_konfirmasi_kasir')
                                @if($req->jenis_request === 'isi_saldo')
                                    <form action="{{ route('kasir.request_operasional.konfirmasi', $req->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin saldo sudah bertambah/diterima?');">
                                        @csrf
                                        <button type="submit" style="background: #3498db; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: bold; width: 100%;">Konfirmasi</button>
                                    </form>
                                @else
                                    <form action="{{ route('kasir.request_operasional.konfirmasi', $req->id) }}" method="POST" onsubmit="return confirm('Selesaikan request operasional ini jika Anda SUDAH menerima fisik mutasi di menu Penerimaan Mutasi?');">
                                        @csrf
                                        <button type="submit" style="background: #3498db; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: bold; width: 100%; margin-bottom: 5px;">Selesaikan</button>
                                    </form>
                                    <a href="{{ route('kasir.penerimaan.index') }}" style="display: block; background: #9b59b6; color: white; padding: 6px 12px; border-radius: 4px; font-size: 11px; text-decoration: none; text-align: center; font-weight: bold; width: 100%; margin-top: 5px;">Cek Mutasi &rarr;</a>
                                @endif
                            @else
                                <span style="font-size: 11px; color: #999;">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: #888;">Belum ada request.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div> <!-- End of Table Wrapper -->
</div> <!-- End of x-data -->

@push('scripts')
<script>
    let rowIdx = 1;
    function addBarangRow() {
        const wrapper = document.getElementById('barang-wrapper');
        const firstRow = wrapper.querySelector('.form-group');
        const clone = firstRow.cloneNode(true);
        
        // Update names
        clone.querySelector('select').name = `items[${rowIdx}][barang_fisik_id]`;
        clone.querySelector('select').value = "";
        clone.querySelector('input').name = `items[${rowIdx}][qty]`;
        clone.querySelector('input').value = "";
        
        wrapper.appendChild(clone);
        rowIdx++;
    }
</script>
@endpush
@endsection
