@extends('layouts.app')

@push('styles')
<style>
    .card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 20px;
    }
    .btn {
        padding: 10px 15px;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .btn-primary { background: #3498db; color: white; }
    .btn-success { background: #2ecc71; color: white; }
    
    .mutasi-item {
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .mutasi-icon {
        font-size: 30px;
        margin-right: 15px;
    }
    .mutasi-info {
        flex-grow: 1;
    }
    .badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
        color: white;
    }
    .badge-warning { background: #f39c12; }
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #2c3e50;">Penerimaan Mutasi</h2>
        <a href="{{ route('kasir.dashboard') }}" class="btn btn-primary" style="background: #95a5a6;">Kembali ke Lobi</a>
    </div>

    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <!-- PERMINTAAN KONFIRMASI PENGIRIMAN DARI CABANG INI (ASAL) -->
    @if($mutasiBarangAsalPending->count() > 0 || $mutasiCashAsalPending->count() > 0)
    <div class="card" style="border-left: 5px solid #f39c12; background: #fffdf5;">
        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: #d35400;">
            ⚠️ Permintaan Pengiriman Mutasi dari Cabang Ini (Konfirmasi Asal)
        </h3>
        <p style="color: #7f8c8d; font-size: 13px;">Admin telah menjadwalkan pengiriman mutasi stok/uang dari cabang Anda. Mohon konfirmasi apakah barang/uang fisik tersebut benar-benar Anda kirimkan.</p>
        
        <!-- Barang Asal -->
        @foreach($mutasiBarangAsalPending as $mba)
            <div class="mutasi-item" style="border-left: 4px solid #f39c12; background: #fff;">
                <div class="mutasi-icon">📦</div>
                <div class="mutasi-info">
                    <div style="font-weight: bold; font-size: 16px;">Tujuan: {{ $mba->cabangTujuan->nama_cabang ?? 'Unknown' }}</div>
                    <div style="color: #7f8c8d; font-size: 13px;">Dijadwalkan oleh Admin: {{ \Carbon\Carbon::parse($mba->created_at)->format('d M Y, H:i') }}</div>
                    <div style="margin-top: 8px; font-size: 14px; background: #f9f9f9; padding: 10px; border-radius: 6px; border: 1px solid #eee;">
                        <div style="font-weight: bold; margin-bottom: 5px; color: #34495e;">Kirim {{ $mba->detailMutasi->groupBy('barang_id')->count() }} jenis barang <span style="color: #e67e22;">(Total: {{ $mba->detailMutasi->sum('kuantitas') }} unit)</span>:</div>
                        <ul style="margin: 0; padding-left: 20px; color: #555; font-size: 13px;">
                            @foreach($mba->detailMutasi->groupBy('barang_id') as $barangId => $details)
                                <li>{{ $details->first()->barang->nama_barang ?? 'Barang Tidak Ditemukan' }} - <span style="font-weight: bold;">{{ $details->sum('kuantitas') }} unit</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; flex-direction: column;">
                    <button type="button" class="btn btn-success" onclick="bukaModalKonfirmasiAsalBarang({{ $mba->id }})" style="text-align: center;">Kirim Sekarang</button>
                    <button type="button" class="btn btn-danger" onclick="bukaModalTolakAsalBarang({{ $mba->id }})" style="background: #e74c3c; text-align: center;">Tolak (Stok Kosong)</button>
                </div>
            </div>
        @endforeach

        <!-- Cash Asal -->
        @foreach($mutasiCashAsalPending as $mca)
            <div class="mutasi-item" style="border-left: 4px solid #f39c12; background: #fff;">
                <div class="mutasi-icon">💵</div>
                <div class="mutasi-info">
                    <div style="font-weight: bold; font-size: 16px;">Tujuan: {{ $mca->cabangTujuan->nama_cabang ?? 'Unknown' }}</div>
                    <div style="color: #7f8c8d; font-size: 13px;">Dijadwalkan oleh Admin: {{ \Carbon\Carbon::parse($mca->created_at)->format('d M Y, H:i') }}</div>
                    <div style="margin-top: 5px; font-size: 18px; font-weight: bold; color: #27ae60;">
                        Rp {{ number_format($mca->nominal, 0, ',', '.') }}
                    </div>
                    @if($mca->keterangan)
                        <div style="font-size: 13px; color: #555; margin-top: 5px;">Catatan: {{ $mca->keterangan }}</div>
                    @endif
                </div>
                <div style="display: flex; gap: 10px; flex-direction: column;">
                    <button type="button" class="btn btn-success" onclick="bukaModalKonfirmasiAsalCash({{ $mca->id }})" style="text-align: center;">Kirim Sekarang</button>
                    <button type="button" class="btn btn-danger" onclick="bukaModalTolakAsalCash({{ $mca->id }})" style="background: #e74c3c; text-align: center;">Tolak (Uang Kosong)</button>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <div class="card">
        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">📦 Mutasi Barang Fisik Menunggu Konfirmasi</h3>
        
        @forelse($mutasiBarangPending as $mb)
            <div class="mutasi-item" style="border-left: 4px solid #3498db;">
                <div class="mutasi-icon">📦</div>
                <div class="mutasi-info">
                    <div style="font-weight: bold; font-size: 16px;">Pengiriman dari: {{ $mb->cabangAsal->nama_cabang ?? 'Gudang Pusat' }}</div>
                    <div style="color: #7f8c8d; font-size: 13px;">Dikirim: {{ \Carbon\Carbon::parse($mb->created_at)->format('d M Y, H:i') }}</div>
                    <div style="margin-top: 8px; font-size: 14px; background: #f9f9f9; padding: 10px; border-radius: 6px; border: 1px solid #eee;">
                        <div style="font-weight: bold; margin-bottom: 5px; color: #34495e;">Terdapat {{ $mb->detailMutasi->groupBy('barang_id')->count() }} jenis barang <span style="color: #e67e22;">(Total: {{ $mb->detailMutasi->sum('kuantitas') }} unit)</span>:</div>
                        <ul style="margin: 0; padding-left: 20px; color: #555; font-size: 13px;">
                            @foreach($mb->detailMutasi->groupBy('barang_id') as $barangId => $details)
                                @php
                                    $firstDetail = $details->first();
                                    $totalKuantitas = $details->sum('kuantitas');
                                @endphp
                                <li>{{ $firstDetail->barang->nama_barang ?? 'Barang Tidak Ditemukan' }} - <span style="font-weight: bold;">{{ $totalKuantitas }} unit</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div>
                    <a href="{{ route('kasir.penerimaan.barang', $mb->id) }}" class="btn btn-primary">Konfirmasi Barang</a>
                </div>
            </div>
        @empty
            <div style="text-align: center; color: #95a5a6; padding: 20px;">
                Tidak ada pengiriman mutasi barang saat ini.
            </div>
        @endforelse
    </div>

    <div class="card">
        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">💵 Mutasi Uang Tunai Menunggu Konfirmasi</h3>
        
        @forelse($mutasiCashPending as $mc)
            <div class="mutasi-item" style="border-left: 4px solid #2ecc71;">
                <div class="mutasi-icon">💵</div>
                <div class="mutasi-info">
                    <div style="font-weight: bold; font-size: 16px;">Pengiriman dari: {{ $mc->cabangAsal->nama_cabang ?? 'Gudang Pusat' }}</div>
                    <div style="color: #7f8c8d; font-size: 13px;">Dikirim: {{ \Carbon\Carbon::parse($mc->created_at)->format('d M Y, H:i') }}</div>
                    <div style="margin-top: 5px; font-size: 18px; font-weight: bold; color: #27ae60;">
                        Rp {{ number_format($mc->nominal, 0, ',', '.') }}
                    </div>
                    @if($mc->keterangan)
                        <div style="font-size: 13px; color: #555; margin-top: 5px;">Catatan: {{ $mc->keterangan }}</div>
                    @endif
                </div>
                <div>
                    <form action="{{ route('kasir.mutasi_cash.terima', $mc->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">Terima Uang</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align: center; color: #95a5a6; padding: 20px;">
                Tidak ada pengiriman mutasi uang tunai saat ini.
            </div>
        @endforelse
    </div>
</div>

<!-- MODAL KONFIRMASI ASAL BARANG -->
<div id="modalKonfirmasiAsalBarang" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; margin: 50px auto;">
        <h3>Konfirmasi Pengiriman Stok</h3>
        <form id="formKonfirmasiAsalBarang" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Keterangan / Nama Pengantar</label>
                <input type="text" name="keterangan" class="form-control" style="width: 100%; padding: 8px;" placeholder="Contoh: Diantar oleh Kepala Area...">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Bukti Pengiriman (Resi / Foto Barang)</label>
                <input type="file" name="bukti_gambar" class="form-control" style="width: 100%; padding: 8px;" accept="image/*">
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalKonfirmasiAsalBarang').style.display='none'" style="background: #95a5a6;">Batal</button>
                <button type="submit" class="btn btn-success" style="margin-left: 10px;">Konfirmasi & Potong Stok</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TOLAK ASAL BARANG -->
<div id="modalTolakAsalBarang" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; margin: 50px auto;">
        <h3>Tolak Pengiriman Stok</h3>
        <form id="formTolakAsalBarang" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Alasan Penolakan</label>
                <textarea name="alasan" required class="form-control" style="width: 100%; padding: 8px;" rows="3" placeholder="Contoh: Stok barang di cabang ternyata kosong / rusak..."></textarea>
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalTolakAsalBarang').style.display='none'" style="background: #95a5a6;">Batal</button>
                <button type="submit" class="btn btn-danger" style="margin-left: 10px; background: #e74c3c;">Tolak Mutasi</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL KONFIRMASI ASAL CASH -->
<div id="modalKonfirmasiAsalCash" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; margin: 50px auto;">
        <h3>Konfirmasi Pengiriman Uang Fisik</h3>
        <form id="formKonfirmasiAsalCash" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Keterangan / Nama Pengantar</label>
                <input type="text" name="keterangan" class="form-control" style="width: 100%; padding: 8px;" placeholder="Contoh: Diantar oleh Kepala Area...">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Bukti Pengiriman (Foto Uang/Resi)</label>
                <input type="file" name="bukti_gambar" class="form-control" style="width: 100%; padding: 8px;" accept="image/*">
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalKonfirmasiAsalCash').style.display='none'" style="background: #95a5a6;">Batal</button>
                <button type="submit" class="btn btn-success" style="margin-left: 10px;">Konfirmasi & Potong Uang Laci</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TOLAK ASAL CASH -->
<div id="modalTolakAsalCash" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; margin: 50px auto;">
        <h3>Tolak Pengiriman Uang Fisik</h3>
        <form id="formTolakAsalCash" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Alasan Penolakan</label>
                <textarea name="alasan" required class="form-control" style="width: 100%; padding: 8px;" rows="3" placeholder="Contoh: Uang di laci ternyata tidak mencukupi..."></textarea>
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalTolakAsalCash').style.display='none'" style="background: #95a5a6;">Batal</button>
                <button type="submit" class="btn btn-danger" style="margin-left: 10px; background: #e74c3c;">Tolak Mutasi</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function bukaModalKonfirmasiAsalBarang(id) {
        document.getElementById('formKonfirmasiAsalBarang').action = `/kasir/mutasi/barang/${id}/konfirmasi-asal`;
        document.getElementById('modalKonfirmasiAsalBarang').style.display = 'block';
    }
    function bukaModalTolakAsalBarang(id) {
        document.getElementById('formTolakAsalBarang').action = `/kasir/mutasi/barang/${id}/tolak-asal`;
        document.getElementById('modalTolakAsalBarang').style.display = 'block';
    }
    function bukaModalKonfirmasiAsalCash(id) {
        document.getElementById('formKonfirmasiAsalCash').action = `/kasir/mutasi/cash/${id}/konfirmasi-asal`;
        document.getElementById('modalKonfirmasiAsalCash').style.display = 'block';
    }
    function bukaModalTolakAsalCash(id) {
        document.getElementById('formTolakAsalCash').action = `/kasir/mutasi/cash/${id}/tolak-asal`;
        document.getElementById('modalTolakAsalCash').style.display = 'block';
    }
</script>
@endpush
@endsection
