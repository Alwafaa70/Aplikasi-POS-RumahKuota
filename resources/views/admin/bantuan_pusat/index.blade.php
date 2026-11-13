@extends('layouts.app')
@section('title', 'Admin - Bantuan Pusat')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Manajemen Bantuan Pusat</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Kasir / Cabang</th>
                            <th>Barang / Layanan</th>
                            <th>No. Tujuan</th>
                            <th>Keterangan</th>
                            <th>Nominal Req</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr>
                            <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <strong>{{ $req->kasir->name ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $req->cabang->nama_cabang ?? '-' }}</small>
                            </td>
                            <td>{{ $req->barang->nama_barang ?? '-' }}</td>
                            <td>{{ $req->nomor_tujuan }}</td>
                            <td>{{ $req->keterangan ?? '-' }}</td>
                            <td>Rp {{ number_format($req->amount, 0, ',', '.') }}</td>
                            <td>
                                @if($req->status == 'menunggu_pusat')
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                @elseif($req->status == 'diproses_admin')
                                    <span class="badge bg-info text-white">Menunggu Konfirmasi Kasir</span>
                                @else
                                    <span class="badge bg-success">Selesai</span>
                                @endif
                            </td>
                            <td>
                                @if($req->status == 'menunggu_pusat')
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProses{{ $req->id }}">
                                        Proses Bantuan
                                    </button>

                                    <!-- Modal Proses -->
                                    <div class="modal fade" id="modalProses{{ $req->id }}" tabindex="-1" aria-labelledby="modalProsesLabel{{ $req->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.bantuan_pusat.proses', $req->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalProsesLabel{{ $req->id }}">Proses Bantuan Transfer/Topup</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body bg-light">
                                                        <div class="text-center mb-4">
                                                            <h3 class="mb-0 text-primary"><i class="fas fa-mobile-alt"></i> Transaksi Digital B</h3>
                                                            <div class="badge bg-dark mt-2" style="font-size: 14px;">{{ $req->barang->nama_barang ?? 'Produk Digital B' }}</div>
                                                        </div>

                                                        <div class="card shadow-sm mb-3 border-0">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between mb-2">
                                                                    <span class="text-muted">Nominal Request:</span>
                                                                    <strong>Rp {{ number_format($req->amount, 0, ',', '.') }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between">
                                                                    <span class="text-muted">Nomor Tujuan:</span>
                                                                    <strong class="text-danger">{{ $req->nomor_tujuan }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label font-weight-bold">1. Pilih Aplikasi Sumber Dana (Pusat)</label>
                                                            <select name="apk_layanan_pusat_id" class="form-select form-select-lg" required>
                                                                <option value="">-- Pilih Aplikasi --</option>
                                                                @foreach($apkLayananPusat as $apk)
                                                                    <option value="{{ $apk->id }}">{{ $apk->masterAplikasiLayanan->nama_layanan ?? 'Apk' }} (Saldo: Rp {{ number_format($apk->saldo_sekarang, 0, ',', '.') }})</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label font-weight-bold">2. Input Modal Sebenarnya (Rp)</label>
                                                            <input type="number" name="harga_modal_pusat" class="form-control form-control-lg text-center font-weight-bold text-success" value="{{ $req->amount }}" required>
                                                            <div class="form-text text-center">Fee akan otomatis ditambahkan ke tagihan kasir berdasarkan modal ini.</div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label font-weight-bold">3. Upload Bukti Transfer (Wajib untuk Kasir)</label>
                                                            <input type="file" name="bukti_gambar" class="form-control" accept="image/*" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        <button type="submit" class="btn btn-primary" onclick="return confirm('Proses sekarang? Saldo pusat akan dikurangi.')">Proses & Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($req->status == 'diproses_admin' || $req->status == 'selesai')
                                    <small>
                                        <strong>Apk:</strong> {{ $req->apkLayananPusat->masterAplikasiLayanan->nama_layanan ?? '-' }}<br>
                                        <strong>Modal:</strong> Rp {{ number_format($req->harga_modal_pusat, 0, ',', '.') }}<br>
                                        <strong>Total Pelanggan:</strong> Rp {{ number_format($req->total_bayar_pelanggan, 0, ',', '.') }}<br>
                                        @if($req->bukti_gambar)
                                            <a href="{{ asset('storage/' . $req->bukti_gambar) }}" target="_blank" class="btn btn-sm btn-outline-info mt-1"><i class="fas fa-image"></i> Lihat Bukti</a>
                                        @endif
                                    </small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data bantuan pusat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
