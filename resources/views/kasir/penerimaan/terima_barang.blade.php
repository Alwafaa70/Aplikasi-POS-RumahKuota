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
    .btn-success { background: #2ecc71; color: white; width: 100%; font-size: 16px; padding: 15px;}
    
    .table-penerimaan {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .table-penerimaan th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #ddd;
    }
    .table-penerimaan td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    .form-control {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        text-align: center;
    }
    .row-error {
        background-color: #ffeaa7;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

@section('content')
<div class="container-fluid" style="padding: 20px;" x-data="penerimaanApp()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #2c3e50;">Penerimaan Mutasi Fisik #{{ $mutasi->id }}</h2>
        <a href="{{ route('kasir.penerimaan.index') }}" class="btn" style="background: #95a5a6; color: white;">Kembali</a>
    </div>

    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #3498db;">
            <strong>Pengirim:</strong> {{ $mutasi->cabangAsal->nama_cabang ?? 'Gudang Pusat' }}<br>
            <strong>Waktu Dikirim:</strong> {{ \Carbon\Carbon::parse($mutasi->created_at)->format('d M Y, H:i') }}
        </div>

        <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px;">
            <strong>ℹ️ Panduan:</strong> Cek fisik barang. Isikan berapa jumlah yang baik, rusak, atau hilang. Pastikan Totalnya sama dengan Kuantitas Dikirim.
        </div>

        <form action="{{ route('kasir.mutasi_barang.terima', $mutasi->id) }}" method="POST" id="form-terima" enctype="multipart/form-data">
            @csrf
            
            <div style="overflow-x: auto;">
                <table class="table-penerimaan">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th style="text-align: center;">Qty Dikirim</th>
                            <th style="text-align: center; width: 100px;">Diterima Baik</th>
                            <th style="text-align: center; width: 100px;">Rusak</th>
                            <th style="text-align: center; width: 100px;">Hilang</th>
                            <th style="text-align: center;">Total Input</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Aggregate detailMutasi by barang_id
                            $aggregatedDetails = [];
                            foreach($mutasi->detailMutasi as $detail) {
                                if(!isset($aggregatedDetails[$detail->barang_id])) {
                                    $aggregatedDetails[$detail->barang_id] = [
                                        'barang_id' => $detail->barang_id,
                                        'nama_barang' => $detail->barang->nama_barang,
                                        'total_qty' => 0
                                    ];
                                }
                                $aggregatedDetails[$detail->barang_id]['total_qty'] += $detail->kuantitas;
                            }
                            $aggregatedDetails = array_values($aggregatedDetails); // reset index
                        @endphp
                        @foreach($aggregatedDetails as $index => $agg)
                        <tr :class="{'row-error': !isValid({{ $index }})}">
                            <td>
                                <strong>{{ $agg['nama_barang'] }}</strong>
                                <input type="hidden" name="items[{{ $index }}][barang_id]" value="{{ $agg['barang_id'] }}">
                            </td>
                            <td style="text-align: center; font-weight: bold; font-size: 16px;">
                                <span x-text="items[{{ $index }}].qty_dikirim"></span>
                            </td>
                            <td>
                                <input type="number" class="form-control" name="items[{{ $index }}][baik]" x-model.number="items[{{ $index }}].baik" min="0" required>
                            </td>
                            <td>
                                <input type="number" class="form-control" name="items[{{ $index }}][rusak]" x-model.number="items[{{ $index }}].rusak" min="0" required>
                            </td>
                            <td>
                                <input type="number" class="form-control" name="items[{{ $index }}][hilang]" x-model.number="items[{{ $index }}].hilang" min="0" required>
                            </td>
                            <td style="text-align: center; font-weight: bold;" :style="isValid({{ $index }}) ? 'color: #27ae60;' : 'color: #c0392b;'">
                                <span x-text="getTotal({{ $index }})"></span>
                                <div x-show="!isValid({{ $index }})" style="font-size: 11px; color: #c0392b; margin-top: 5px;">Harus sama dgn Dikirim</div>
                            </td>
                        </tr>
                        <!-- Baris Catatan (Opsional) -->
                        <tr x-show="items[{{ $index }}].rusak > 0 || items[{{ $index }}].hilang > 0">
                            <td colspan="6" style="padding-top: 0; padding-bottom: 20px; border-bottom: 2px solid #eee;">
                                <input type="text" class="form-control" style="text-align: left;" placeholder="Tulis catatan kerusakan/kehilangan barang ini..." name="items[{{ $index }}][catatan]">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; margin-bottom: 30px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Catatan Penerimaan (Opsional)</label>
                <textarea name="catatan_penerimaan" class="form-control" style="text-align: left; height: 80px;" placeholder="Tulis jika ada catatan untuk seluruh mutasi ini..."></textarea>
            </div>

            <div style="margin-bottom: 30px; background: #fdfbfb; padding: 15px; border-radius: 6px; border: 1px dashed #ccc;" x-show="hasDiscrepancy()">
                <label style="font-weight: bold; display: block; margin-bottom: 5px; color: #e74c3c;">📸 Bukti Gambar (Wajib jika ada selisih)</label>
                <p style="font-size: 12px; color: #7f8c8d; margin-top: 0; margin-bottom: 10px;">Harap unggah foto bukti fisik barang yang rusak/hilang. Max 2MB.</p>
                <input type="file" name="bukti_gambar" class="form-control" accept="image/*" :required="hasDiscrepancy()" style="padding: 10px; background: white;">
            </div>

            <button type="submit" class="btn btn-success" :disabled="!isAllValid()" :style="!isAllValid() ? 'background: #95a5a6; cursor: not-allowed;' : ''">
                ✅ Konfirmasi Penerimaan
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('penerimaanApp', () => ({
            items: [
                @foreach($aggregatedDetails as $agg)
                {
                    qty_dikirim: {{ $agg['total_qty'] }},
                    baik: {{ $agg['total_qty'] }},
                    rusak: 0,
                    hilang: 0
                },
                @endforeach
            ],

            getTotal(index) {
                return (this.items[index].baik || 0) + (this.items[index].rusak || 0) + (this.items[index].hilang || 0);
            },

            isValid(index) {
                return this.getTotal(index) === this.items[index].qty_dikirim;
            },

            isAllValid() {
                for (let i = 0; i < this.items.length; i++) {
                    if (!this.isValid(i)) return false;
                }
                return true;
            },

            hasDiscrepancy() {
                for (let i = 0; i < this.items.length; i++) {
                    if ((this.items[i].rusak || 0) > 0 || (this.items[i].hilang || 0) > 0) return true;
                }
                return false;
            }
        }));
    });
</script>
@endsection
