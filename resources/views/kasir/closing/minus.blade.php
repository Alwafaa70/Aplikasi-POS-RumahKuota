@extends('layouts.app')
@section('title', 'Riwayat Minus & Reimburse')

@push('styles')
<style>
    .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .btn { padding: 8px 16px; border-radius: 4px; border: none; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; }
    .btn-primary { background: var(--primary); color: white; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #f8f9fa; color: #333; font-weight: bold; }
    .text-danger { color: #e74c3c; font-weight: bold; }
    .text-success { color: #27ae60; font-weight: bold; }
    .badge-warning { background: #f39c12; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .badge-success { background: #27ae60; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .form-group { margin-bottom: 10px; }
    .form-control { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
</style>
@endpush

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="color: #2c3e50; margin: 0;">Riwayat Minus & Reimburse</h2>
    <p style="color: #7f8c8d; margin-top: 5px;">Jika Anda memiliki catatan minus saat closing, Anda dapat melakukan pelunasan/pengembalian uang yang kurang di sini.</p>
</div>

@if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    <h3 style="margin-top: 0; color: #2c3e50;">🔴 Riwayat Minus Uang Fisik (Laci)</h3>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Waktu Closing</th>
                    <th>Total Minus</th>
                    <th>Status Reimburse</th>
                    <th>Aksi / Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($minusFisik as $mf)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($mf->created_at)->format('d M Y, H:i') }}</td>
                    <td>
                        @php
                            $totalMinus = abs($mf->selisih_fisik);
                            $sudahDikembalikan = $mf->nominal_pengembalian ?? 0;
                            $sisaMinus = $totalMinus - $sudahDikembalikan;
                        @endphp
                        <span class="text-danger">- Rp {{ number_format($totalMinus, 0, ',', '.') }}</span><br>
                        @if($sudahDikembalikan > 0)
                            <small style="color: green;">Telah dikembalikan: Rp {{ number_format($sudahDikembalikan, 0, ',', '.') }}</small><br>
                            @if($sisaMinus > 0)
                                <small class="text-danger">Sisa Minus: Rp {{ number_format($sisaMinus, 0, ',', '.') }}</small>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($mf->status_pengembalian_minus == 'dikembalikan' || $sisaMinus <= 0)
                            <span class="badge-success">Lunas</span>
                        @elseif($sudahDikembalikan > 0)
                            <span class="badge-warning">Dicicil</span>
                        @else
                            <span class="badge-warning">Belum Dikembalikan</span>
                        @endif
                    </td>
                    <td>
                        @if($mf->status_pengembalian_minus == 'dikembalikan' || $sisaMinus <= 0)
                            <div style="font-size: 12px; color: #555;"><i>"{{ $mf->catatan_pengembalian }}"</i></div>
                        @elseif($mf->status_konfirmasi == 'dikonfirmasi')
                            <span class="text-danger" style="font-size: 12px;"><i>Terkunci (Sudah dikonfirmasi Pusat)</i></span>
                        @else
                            <form action="{{ route('kasir.closing.reimburse', ['id' => $mf->id, 'type' => 'fisik']) }}" method="POST">
                                @csrf
                                <div class="form-group" style="display: flex; gap: 10px;">
                                    <input type="number" name="nominal_pengembalian" class="form-control" placeholder="Nominal (Rp)" max="{{ $sisaMinus }}" required style="width: 150px;">
                                    <input type="text" name="catatan_pengembalian" class="form-control" placeholder="Keterangan..." required>
                                </div>
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin mereimburse nominal ini?');">Kembalikan Uang</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">Tidak ada catatan minus uang fisik.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-top: 0; color: #2c3e50;">🔴 Riwayat Minus Saldo Digital (Aplikasi)</h3>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Waktu Closing</th>
                    <th>Aplikasi</th>
                    <th>Total Minus</th>
                    <th>Status Reimburse</th>
                    <th>Aksi / Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($minusApk as $ma)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($ma->created_at)->format('d M Y, H:i') }}</td>
                    <td>{{ $ma->masterAplikasiLayanan->nama_layanan ?? '-' }}</td>
                    <td>
                        @php
                            $totalMinus = abs($ma->selisih);
                            $sudahDikembalikan = $ma->nominal_pengembalian ?? 0;
                            $sisaMinus = $totalMinus - $sudahDikembalikan;
                        @endphp
                        <span class="text-danger">- Rp {{ number_format($totalMinus, 0, ',', '.') }}</span><br>
                        @if($sudahDikembalikan > 0)
                            <small style="color: green;">Telah dikembalikan: Rp {{ number_format($sudahDikembalikan, 0, ',', '.') }}</small><br>
                            @if($sisaMinus > 0)
                                <small class="text-danger">Sisa Minus: Rp {{ number_format($sisaMinus, 0, ',', '.') }}</small>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($ma->status_pengembalian_minus == 'dikembalikan' || $sisaMinus <= 0)
                            <span class="badge-success">Lunas</span>
                        @elseif($sudahDikembalikan > 0)
                            <span class="badge-warning">Dicicil</span>
                        @else
                            <span class="badge-warning">Belum Dikembalikan</span>
                        @endif
                    </td>
                    <td>
                        @if($ma->status_pengembalian_minus == 'dikembalikan' || $sisaMinus <= 0)
                            <div style="font-size: 12px; color: #555;"><i>"{{ $ma->catatan_pengembalian }}"</i></div>
                        @elseif($ma->laporanClosing->status_konfirmasi == 'dikonfirmasi')
                            <span class="text-danger" style="font-size: 12px;"><i>Terkunci (Sudah dikonfirmasi Pusat)</i></span>
                        @else
                            <form action="{{ route('kasir.closing.reimburse', ['id' => $ma->id, 'type' => 'apk']) }}" method="POST">
                                @csrf
                                <div class="form-group" style="display: flex; gap: 10px;">
                                    <input type="number" name="nominal_pengembalian" class="form-control" placeholder="Nominal (Rp)" max="{{ $sisaMinus }}" required style="width: 150px;">
                                    <input type="text" name="catatan_pengembalian" class="form-control" placeholder="Keterangan..." required>
                                </div>
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin mereimburse nominal ini?');">Kembalikan Saldo</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">Tidak ada catatan minus saldo digital.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
