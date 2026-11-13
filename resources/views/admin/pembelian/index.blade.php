@extends('layouts.app')
@section('title', 'Penerimaan & Pembelian (PO)')

@push('styles')
<style>
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn-create { background: var(--primary); color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; border: none; cursor: pointer; }
    .btn-create:hover { opacity: 0.9; }
    
    .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .table th, .table td { border: 1px solid var(--border); padding: 12px; text-align: left; font-size: 14px; }
    .table th { background: #f9f9f9; font-weight: bold; color: #555; }
    .table tr:hover { background: #fdfdfd; }
    
    .badge { padding: 4px 8px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    .badge-draft { background: #ecf0f1; color: #7f8c8d; border: 1px solid #bdc3c7; }
    .badge-jalan { background: #fcf3cf; color: #d35400; border: 1px solid #f1c40f; }
    .badge-selesai { background: #d5f5e3; color: #27ae60; border: 1px solid #2ecc71; }

    .btn-action { padding: 6px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; cursor: pointer; border: none; color: white; }
    .btn-pay { background: #8e44ad; }
    .btn-receive { background: #27ae60; }
    
    /* Modal CSS */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
    .modal.active { display: flex; }
    .modal-content { background: white; padding: 0; border-radius: 8px; width: 400px; max-width: 90%; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
    .modal-header { background: #8e44ad; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; font-weight: bold; }
    .modal-header button { background: none; border: none; color: white; font-size: 20px; cursor: pointer; }
    .modal-body { padding: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; color: #555; }
    .form-control { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 4px; font-size: 14px; box-sizing: border-box; }
    .btn-submit { width: 100%; background: #8e44ad; color: white; border: none; padding: 12px; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 10px;}
</style>
@endpush

@section('content')
@if(session('success'))
    <div style="background: #d5f5e3; color: #27ae60; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; border-left: 4px solid #2ecc71;">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #fadbd8; color: #c0392b; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; border-left: 4px solid #e74c3c;">
        âš ï¸  {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div style="background: #fadbd8; color: #c0392b; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; border-left: 4px solid #e74c3c;">
        âš ï¸  Terjadi kesalahan:
        <ul style="margin-top: 5px; margin-bottom: 0;">
            @foreach($errors->all() as $error)
                <li>- {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="header-action">
    <div>
        <h1 style="margin: 0; font-size: 24px; color: #2c3e50;">Penerimaan & Pembelian (PO)</h1>
        <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 14px;">Daftar riwayat pembelian stok untuk gudang pusat.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ auth()->user()->role === 'owner' ? route('owner.gudang.index') : route('admin.dashboard') }}" class="btn-create" style="background: #16a085;">📊 Monitoring Stok</a>
        <a href="{{ route('admin.pembelian.historis') }}" class="btn-create" style="background: #34495e;">📄 Lihat Historis & Cetak Laporan</a>
        <a href="{{ route('admin.pembelian.create') }}" class="btn-create">➕ Buat Pembelian Baru</a>
    </div>
</div>

<div class="card">
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>PO ID & Tanggal</th>
                    <th>Supplier</th>
                    <th>Total Harga</th>
                    <th style="text-align: center;">Status & Pembayaran</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembelians as $p)
                <tr>
                    <td>
                        <div style="font-weight: bold; color: #2c3e50; font-size: 15px;">PO-{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}</div>
                        <div style="font-size: 12px; color: #7f8c8d;">{{ \Carbon\Carbon::parse($p->tanggal_pembelian)->format('d M Y') }}</div>
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #34495e;">{{ $p->supplier->nama_supplier ?? '-' }}</div>
                        <div style="font-size: 12px; color: #95a5a6;">{{ $p->detailPembelian->sum('qty') }} Item dipesan</div>
                    </td>
                    <td>
                        <div style="font-weight: bold; color: var(--primary); font-size: 16px;">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</div>
                    </td>
                    <td style="text-align: center;">
                        @if($p->status === 'draft')
                            <span class="badge badge-draft">DRAFT</span>
                        @elseif($p->status === 'dalam_perjalanan')
                            <span class="badge badge-jalan">DALAM PERJALANAN</span>
                        @elseif($p->status === 'selesai')
                            <span class="badge badge-selesai">SELESAI (DITERIMA)</span>
                        @endif

                        @if($p->metode_pembayaran)
                            <div style="font-size: 10px; color: #7f8c8d; margin-top: 5px; text-transform: uppercase; font-weight: bold;">
                                {{ str_replace('_', ' ', $p->metode_pembayaran) }}
                            </div>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        @if($p->status === 'draft')
                            <div style="display: flex; gap: 5px; justify-content: flex-end;">
                                <a href="{{ route('admin.pembelian.edit', $p->id) }}" class="btn-action" style="background: #3498db; text-decoration: none;">✏️ Edit Draft</a>
                                <button type="button" onclick="openModal({{ $p->id }})" class="btn-action btn-pay">💳 Bayar & Pesan</button>
                            </div>
                        @elseif($p->status === 'dalam_perjalanan')
                            <form action="{{ route('admin.pembelian.selesai', $p->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" onclick="return confirm('Apakah barang sudah tiba di gudang pusat? Stok pusat akan bertambah.')" class="btn-action btn-receive">📦 Terima Barang</button>
                            </form>
                        @else
                            <span style="color: #bdc3c7; font-size: 12px; font-style: italic; font-weight: bold;">Stok Masuk</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: #7f8c8d;">Belum ada riwayat pembelian supplier.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Bayar untuk Draft PO (Di Luar Tabel agar Bebas Overflow Stacking Context) -->
@foreach($pembelians->where('status', 'draft') as $p)
    <div id="modal-bayar-{{ $p->id }}" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Pembayaran PO-{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}</span>
                <button type="button" onclick="closeModal({{ $p->id }})">&times;</button>
            </div>
            <form action="{{ route('admin.pembelian.submit', $p->id) }}" method="POST" class="modal-body" style="text-align: left;" onsubmit="return confirm('Apakah Anda yakin data dan total bayar sudah benar? Saldo Anda akan otomatis terpotong.');">
                @csrf
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 13px; color: #7f8c8d;">Total Tagihan PO</div>
                    <div style="font-size: 24px; font-weight: bold; color: #8e44ad;">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</div>
                </div>
                
                <div class="form-group">
                    <label>Pilih Sumber Dana</label>
                    <select name="metode_pembayaran" id="metode-{{ $p->id }}" class="form-control" required onchange="toggleDompet(this, {{ $p->id }})">
                        <option value="">-- Pilih Sumber Kas --</option>
                        <option value="cash_pusat">Cash Kas Pusat (Fisik)</option>
                        <option value="apk_layanan">Dompet Digital Pusat (Transfer)</option>
                    </select>
                </div>

                <div class="form-group" id="div-dompet-{{ $p->id }}" style="display: none;">
                    <label>Pilih Dompet Digital</label>
                    <select name="apk_layanan_id" class="form-control">
                        <option value="">-- Pilih Akun Dompet --</option>
                        @foreach(\App\Models\PusatAplikasiLayanan::with('masterAplikasiLayanan')->get() as $dompet)
                            <option value="{{ $dompet->id }}">{{ $dompet->masterAplikasiLayanan->nama_layanan ?? $dompet->masterAplikasiLayanan->nama_aplikasi }} (Saldo: Rp {{ number_format($dompet->saldo_sekarang, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-submit">Bayar Sekarang & Masukkan ke Stok</button>
            </form>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
function openModal(id) {
    document.getElementById('modal-bayar-' + id).classList.add('active');
}
function closeModal(id) {
    document.getElementById('modal-bayar-' + id).classList.remove('active');
}
function toggleDompet(select, id) {
    if(select.value === 'apk_layanan') {
        document.getElementById('div-dompet-'+id).style.display = 'block';
    } else {
        document.getElementById('div-dompet-'+id).style.display = 'none';
    }
}
</script>
@endpush
@endsection
