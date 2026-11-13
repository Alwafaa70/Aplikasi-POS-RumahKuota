@extends('layouts.app')
@section('title', 'Daftar Request Bantuan Pusat')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Daftar Bantuan Pusat (Kasir)</h2>
        <a href="{{ route('kasir.dashboard') }}" class="btn btn-secondary">Kembali ke POS</a>
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
                            <th>Layanan (Barang)</th>
                            <th>Nomor Tujuan</th>
                            <th>Request Nominal (Rp)</th>
                            <th>Status</th>
                            <th>Info Proses (Pusat)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daftar as $item)
                        <tr>
                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                            <td>{{ $item->nomor_tujuan }}</td>
                            <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                            <td>
                                @if($item->status == 'menunggu_pusat')
                                    <span class="badge bg-warning text-dark">Menunggu Pusat</span>
                                @elseif($item->status == 'diproses_admin')
                                    <span class="badge bg-info text-white">Diproses Admin</span>
                                @else
                                    <span class="badge bg-success">Selesai</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status != 'menunggu_pusat')
                                    <small>
                                        <strong>Apk:</strong> {{ $item->apkLayananPusat->masterAplikasiLayanan->nama_layanan ?? '-' }}<br>
                                        <strong>Modal:</strong> Rp {{ number_format($item->harga_modal_pusat, 0, ',', '.') }}<br>
                                        <strong>Total Pelanggan:</strong> Rp {{ number_format($item->total_bayar_pelanggan, 0, ',', '.') }}<br>
                                        @if($item->bukti_gambar)
                                            <a href="{{ asset('storage/' . $item->bukti_gambar) }}" target="_blank" class="btn btn-sm btn-outline-info mt-1"><i class="fas fa-image"></i> Lihat Bukti Tf</a>
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status == 'diproses_admin')
                                    @php $jenis = $item->barang->jenis_transaksi ?? 'cash_in'; @endphp
                                    @if($jenis == 'cash_in')
                                        <form action="{{ route('kasir.bantuan_pusat.konfirmasi', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin sudah menerima uang cash/pembayaran dari pelanggan sesuai Total Pelanggan (Rp {{ number_format($item->total_bayar_pelanggan, 0, ',', '.') }}) dan pelanggan sudah menerima kirimannya? Saldo kas laci Anda akan BERTAMBAH sejumlah ini.')">
                                    @else
                                        <form action="{{ route('kasir.bantuan_pusat.konfirmasi', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin pelanggan sudah mentransfer sebesar Total Pelanggan (Rp {{ number_format($item->total_bayar_pelanggan, 0, ',', '.') }}) ke pusat dan Anda memberikan tunai ke pelanggan sebesar Modal? Saldo kas laci Anda akan BERKURANG.')">
                                    @endif
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Konfirmasi & Checkout</button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>Tidak Ada Aksi</button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada request bantuan pusat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
