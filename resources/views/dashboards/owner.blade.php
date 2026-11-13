@extends('layouts.app')
@section('title', 'Dashboard Owner')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Premium Modern Dashboard CSS */
    body { background-color: #f3f4f6; }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-radius: 1rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .net-worth-widget {
        background: linear-gradient(135deg, #0f172a 0%, #312e81 100%);
        border-radius: 1.5rem;
        padding: 2.5rem;
        color: white;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
    }
    
    .net-worth-widget::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%; width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        animation: rotate 20s linear infinite;
    }
    
    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .badge-soft-red { background-color: #fee2e2; color: #991b1b; }
    .badge-soft-green { background-color: #dcfce7; color: #166534; }
    
    /* Custom Scrollbar for Modal */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
</style>
@endpush

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-8" x-data="{ ewsModalOpen: false, selectedLocation: '', selectedItems: [] }">
    
    <!-- 1. GRAND TOTAL NET WORTH -->
    <div class="net-worth-widget">
        <div class="relative z-10">
            <h2 class="text-indigo-200 text-sm font-bold tracking-widest uppercase mb-2">Total Aset Keseluruhan (Net Worth)</h2>
            <div class="text-5xl font-extrabold flex items-baseline gap-2">
                <span class="text-indigo-300 text-3xl">Rp</span> {{ number_format($netWorth, 0, ',', '.') }}
            </div>
            <p class="text-indigo-200/80 text-sm mt-3">Kombinasi dari kas pusat, kas cabang, setoran tertunda, dan e-wallet digital.</p>
        </div>
    </div>

    <!-- AKSES CEPAT (PREMIUM) -->
    <a href="{{ route('owner.ses_debugger') }}" class="block w-full rounded-2xl p-6 md:p-8 mb-6 text-white relative overflow-hidden group shadow-lg transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="absolute -right-4 -top-10 md:-right-10 md:-top-10 opacity-10 group-hover:opacity-20 transition-opacity duration-500">
            <i class="fa-solid fa-brain text-9xl"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-4 md:gap-6">
                <div class="bg-white/20 p-3 md:p-4 rounded-xl backdrop-blur-sm">
                    <i class="fa-solid fa-robot text-2xl md:text-3xl text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-black mb-1">Lab Peramalan AI & Parameter EWS</h2>
                    <p class="text-white/80 text-xs md:text-sm">Lihat prediksi batas aman seluruh stok dengan AI dan konfigurasikan peringatan stok menipis secara manual.</p>
                </div>
            </div>
            <div class="bg-white/20 px-5 py-2 md:px-6 md:py-3 rounded-full backdrop-blur-sm font-bold text-xs md:text-sm tracking-wider group-hover:bg-white group-hover:text-[#764ba2] transition-colors duration-300 whitespace-nowrap self-start md:self-center">
                Akses Sekarang <i class="fa-solid fa-arrow-right ml-2"></i>
            </div>
        </div>
    </a>

    <!-- 2. GRID INFORMASI FINANSIAL -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Cash Laci -->
        <div class="glass-card p-6 border-b-4 border-b-green-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Cash Fisik di Laci</h3>
            <div class="text-2xl font-black text-gray-800 mb-1">Rp {{ number_format($totalCashCabang, 0, ',', '.') }}</div>
            <p class="text-gray-400 text-xs">Tersebar di {{ $cabangs->count() }} cabang aktif</p>
        </div>
        <!-- Setoran Menggantung -->
        <div class="glass-card p-6 border-b-4 border-b-yellow-400">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Setoran Pending</h3>
            <div class="text-2xl font-black text-gray-800 mb-1">Rp {{ number_format($setoranMenggantung, 0, ',', '.') }}</div>
            <p class="text-gray-400 text-xs">Menunggu konfirmasi admin pusat</p>
        </div>
        <!-- Kas Pusat -->
        <div class="glass-card p-6 border-b-4 border-b-indigo-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Kas Fisik Pusat</h3>
            <div class="text-2xl font-black text-gray-800 mb-1">Rp {{ number_format($totalKasPusat, 0, ',', '.') }}</div>
            <p class="text-gray-400 text-xs">Uang fisik yang sudah disetor</p>
        </div>
        <!-- Saldo Digital -->
        <div class="glass-card p-6 border-b-4 border-b-blue-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Total Aset Digital</h3>
            <div class="text-2xl font-black text-gray-800 mb-1">Rp {{ number_format($totalDigitalCabang + $totalDigitalPusat, 0, ',', '.') }}</div>
            <p class="text-gray-400 text-xs">Gabungan e-wallet cabang & pusat</p>
        </div>
    </div>

    <!-- 3. EWS GROUPED ALERT WIDGET -->
    @if(count($ewsAlerts) > 0)
    <div class="bg-red-50 border-l-4 border-red-500 rounded-r-xl p-5 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 transition-all hover:bg-red-100">
        <div class="flex items-center gap-4">
            <div class="bg-red-100 p-3 rounded-full text-red-600">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <h3 class="text-red-700 font-bold text-lg">Peringatan Stok Menipis (EWS)</h3>
                <p class="text-red-600 text-sm">
                    @if($totalBarangKritisGudang > 0)
                        {{ $totalBarangKritisGudang }} Barang kritis di Gudang Pusat. 
                    @endif
                    @if($totalCabangKritis > 0)
                        {{ $totalCabangKritis }} Cabang memiliki stok barang kritis.
                    @endif
                </p>
            </div>
        </div>
        <div>
            <button @click="ewsModalOpen = true" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-5 rounded-lg shadow-md transition-colors text-sm">
                Lihat Detail Peringatan
            </button>
        </div>
    </div>
    @else
    <div class="bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl p-5 shadow-sm flex items-center gap-4">
        <div class="bg-emerald-100 p-3 rounded-full text-emerald-600">
            <i class="fa-solid fa-check-circle text-xl"></i>
        </div>
        <div>
            <h3 class="text-emerald-700 font-bold text-lg">Stok Aman</h3>
            <p class="text-emerald-600 text-sm">Seluruh stok cabang dan gudang berada di atas ambang batas kritis (EWS).</p>
        </div>
    </div>
    @endif

    <!-- EWS Modal (Alpine.js) -->
    <div x-show="ewsModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="ewsModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="ewsModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <!-- Modal panel -->
            <div x-show="ewsModalOpen" x-transition.scale class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-100">
                <div class="bg-white px-5 pt-6 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-red-100 sm:mx-0 sm:h-11 sm:w-11 text-red-600 shadow-xs">
                            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <div class="flex items-center justify-between border-b pb-3 mb-4">
                                <div>
                                    <h3 class="text-xl leading-6 font-black text-gray-900" id="modal-title">Peringatan Stok Kritis (EWS)</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Daftar barang fisik yang telah menyentuh batas aman lead time logistik</p>
                                </div>
                                <a href="{{ route('owner.ews.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-200 transition">
                                    Buka Manajemen EWS &rarr;
                                </a>
                            </div>
                            <div class="mt-2 max-h-[60vh] overflow-y-auto pr-2 space-y-6">
                                @foreach($ewsAlerts as $lokasi => $items)
                                <div class="bg-gray-50/70 p-4 rounded-2xl border border-gray-200/80">
                                    <h4 class="font-black text-gray-800 text-sm mb-3 flex items-center gap-2">
                                        <i class="fa-solid fa-location-dot text-indigo-500"></i>
                                        <span>{{ $lokasi }}</span>
                                        <span class="text-xs bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded-full">{{ count($items) }} Kritis</span>
                                    </h4>
                                    <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-2xs">
                                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                                            <thead class="bg-gray-800 text-white font-bold uppercase tracking-wider">
                                                <tr>
                                                    <th class="px-3.5 py-3 text-left">Nama Produk</th>
                                                    <th class="px-3 py-3 text-center">Stok Fisik</th>
                                                    <th class="px-3 py-3 text-center bg-red-700/90">Batas EWS (7 Hari)</th>
                                                    <th class="px-3 py-3 text-center">Tren & Klasifikasi</th>
                                                    <th class="px-3 py-3 text-center bg-emerald-700/90">Rekomendasi</th>
                                                    <th class="px-3 py-3 text-center">Aksi Cepat Restok & Audit</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 bg-white">
                                                @foreach($items as $item)
                                                <tr class="hover:bg-red-50/40 transition">
                                                    <td class="px-3.5 py-3 text-gray-900 font-bold">
                                                        <div class="text-sm font-black">{{ $item->nama_barang }}</div>
                                                        <span class="text-[10px] text-gray-400 font-medium">{{ $item->kategori }} • {{ $item->tipe_barang }}</span>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-black leading-none text-red-100 bg-red-600 rounded-full animate-pulse">{{ $item->stok_saat_ini }}</span>
                                                    </td>
                                                    <td class="px-3 py-3 text-center text-red-700 font-extrabold text-xs">
                                                        <span class="bg-red-50 border border-red-200 px-2 py-0.5 rounded-lg">{{ $item->batas_ews }} Pcs</span>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <div class="font-bold text-gray-700">{{ number_format($item->daily_forecast, 2) }} /hari</div>
                                                        <span class="text-[10px] font-bold text-indigo-700">{{ $item->klasifikasi_nama }}</span>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        @if($item->rekomendasi_restok > 0)
                                                            <span class="bg-emerald-100 text-emerald-800 border border-emerald-300 font-black px-2.5 py-1 rounded-xl text-xs inline-block shadow-2xs">
                                                                +{{ $item->rekomendasi_restok }} Pcs
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-gray-400 font-semibold bg-gray-100 px-2 py-0.5 rounded">Cukup</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                                        <div class="inline-flex items-center gap-1.5">
                                                            @if(empty($item->cabang_id) || $item->tipe_lokasi === 'Gudang Pusat')
                                                                <a href="{{ route('admin.pembelian.create') }}?barang_id={{ $item->barang_id }}&qty={{ $item->rekomendasi_restok }}" 
                                                                   class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1.5 rounded-xl font-bold text-xs inline-flex items-center gap-1 transition shadow-2xs active:scale-95"
                                                                   title="Pesan PO ke Supplier">
                                                                    <i class="fa-solid fa-cart-plus text-xs"></i>
                                                                    <span>Pesan PO</span>
                                                                </a>
                                                            @else
                                                                <a href="{{ route('distribusi.mutasi.index') }}?tab=mutasi&cabang_asal=&cabang_tujuan={{ $item->cabang_id }}&barang_id={{ $item->barang_id }}&qty={{ $item->rekomendasi_restok }}" 
                                                                   class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1.5 rounded-xl font-bold text-xs inline-flex items-center gap-1 transition shadow-2xs active:scale-95"
                                                                   title="Kirim Mutasi dari Gudang Pusat ke Cabang Ini">
                                                                    <i class="fa-solid fa-truck-fast text-xs"></i>
                                                                    <span>Mutasi</span>
                                                                </a>
                                                            @endif
                                                            <button type="button" 
                                                                    onclick="window.openEwsStatsModal({{ $item->barang_id }}, {{ $item->cabang_id ? $item->cabang_id : 'null' }}, 30)" 
                                                                    class="bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 border border-indigo-200 px-2.5 py-1.5 rounded-xl font-bold text-xs inline-flex items-center gap-1 transition shadow-2xs active:scale-95">
                                                                <i class="fa-solid fa-chart-pie"></i>
                                                                <span>Bedah Rumus</span>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t">
                    <button type="button" @click="ewsModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-2xs px-4 py-2 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. TOP & WORST SELLING ITEMS (1 Bulan Terakhir) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 5 Terlaris -->
        <div class="glass-card overflow-hidden flex flex-col h-full">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-4 shrink-0">
                <h2 class="text-white font-bold text-lg"><i class="fa-solid fa-arrow-trend-up mr-2"></i> 5 Barang Terlaris (Bulan Ini)</h2>
            </div>
            <div class="p-0 flex-grow bg-white">
                <ul class="divide-y divide-gray-100 h-full">
                    @forelse($topSelling as $index => $item)
                    <li class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $item->nama_barang }}</p>
                                <p class="text-xs text-gray-500">Penjualan 30 Hari Terakhir</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1.5 text-sm font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                {{ number_format($item->total_qty, 0, ',', '.') }} Terjual
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="px-6 py-8 text-center text-gray-500">Belum ada data penjualan bulan ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Bottom 5 Kurang Laku -->
        <div class="glass-card overflow-hidden flex flex-col h-full">
            <div class="bg-gradient-to-r from-rose-500 to-red-500 px-6 py-4 shrink-0">
                <h2 class="text-white font-bold text-lg"><i class="fa-solid fa-arrow-trend-down mr-2"></i> 5 Barang Kurang Diminati (Bulan Ini)</h2>
            </div>
            <div class="p-0 flex-grow bg-white">
                <ul class="divide-y divide-gray-100 h-full">
                    @forelse($worstSelling as $index => $item)
                    <li class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-exclamation text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $item->nama_barang }}</p>
                                <p class="text-xs text-gray-500">Penjualan 30 Hari Terakhir</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1.5 text-sm font-bold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                {{ number_format($item->total_qty, 0, ',', '.') }} Terjual
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="px-6 py-8 text-center text-gray-500">Belum ada data penjualan bulan ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- 5. CHART GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 1. Area Chart -->
        <div class="glass-card p-6">
            <h2 class="text-gray-800 font-bold text-lg border-b border-gray-100 pb-3 mb-4"><i class="fa-solid fa-chart-area text-blue-500 mr-2"></i> Proyeksi Laba (30 Hari Terakhir)</h2>
            <div id="area-chart" style="height: 320px;"></div>
        </div>

        <!-- 2. Bar Chart -->
        <div class="glass-card p-6">
            <h2 class="text-gray-800 font-bold text-lg border-b border-gray-100 pb-3 mb-4"><i class="fa-solid fa-ranking-star text-yellow-500 mr-2"></i> Top 5 Cabang Terlaris <span class="text-xs text-gray-500 font-normal ml-1">(30 Hari Terakhir)</span></h2>
            <div id="bar-chart" style="height: 320px;"></div>
        </div>

        <!-- 3. Donut Chart (Metode Pembayaran) -->
        <div class="glass-card p-6">
            <h2 class="text-gray-800 font-bold text-lg border-b border-gray-100 pb-3 mb-4"><i class="fa-solid fa-wallet text-teal-500 mr-2"></i> Metode Pembayaran Terpopuler</h2>
            <div id="metode-chart" style="height: 320px;"></div>
        </div>
        
        <!-- 4. Donut Chart (Fisik vs Digital) -->
        <div class="glass-card p-6">
            <h2 class="text-gray-800 font-bold text-lg border-b border-gray-100 pb-3 mb-4"><i class="fa-solid fa-boxes-stacked text-purple-500 mr-2"></i> Proporsi Transaksi Fisik vs Layanan Digital</h2>
            <div id="kategori-chart" style="height: 320px;"></div>
        </div>
    </div>

</div>

@include('partials.modal_ews_stats')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let apiUrl = '/owner/reports/chart-data';

        const formatRp = (value) => {
            return 'Rp ' + value.toLocaleString('id-ID');
        };

        fetch(apiUrl)
            .then(res => res.json())
            .then(data => {
                
                // 1. AREA CHART (Omzet vs Laba)
                if(data.area_chart.categories.length > 0) {
                    new ApexCharts(document.querySelector("#area-chart"), {
                        series: data.area_chart.series,
                        chart: { type: 'area', height: '100%', toolbar: { show: false }, fontFamily: 'inherit' },
                        colors: ['#8b5cf6', '#10b981'],
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        xaxis: { categories: data.area_chart.categories },
                        yaxis: { labels: { formatter: (value) => { return 'Rp ' + (value/1000000).toFixed(1) + 'M'; } } },
                        tooltip: { y: { formatter: formatRp } },
                        legend: { position: 'top' }
                    }).render();
                } else {
                    document.querySelector("#area-chart").innerHTML = "<div class='flex h-full items-center justify-center text-gray-400'>Belum ada data</div>";
                }

                // 2. BAR CHART (Top Cabang)
                if(data.bar_chart.categories.length > 0) {
                    new ApexCharts(document.querySelector("#bar-chart"), {
                        series: data.bar_chart.series,
                        chart: { type: 'bar', height: '100%', toolbar: { show: false }, fontFamily: 'inherit' },
                        plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
                        colors: ['#f59e0b'],
                        dataLabels: { enabled: false },
                        xaxis: { categories: data.bar_chart.categories, labels: { formatter: (val) => { return (val/1000000).toFixed(0) + 'M'; } } },
                        tooltip: { y: { formatter: formatRp } }
                    }).render();
                } else {
                    document.querySelector("#bar-chart").innerHTML = "<div class='flex h-full items-center justify-center text-gray-400'>Belum ada data</div>";
                }

                // 3. METODE PEMBAYARAN (Donut Chart)
                let totalMetode = data.metode_chart.series.reduce((a,b) => a+b, 0);
                if(totalMetode > 0) {
                    new ApexCharts(document.querySelector("#metode-chart"), {
                        series: data.metode_chart.series,
                        chart: { type: 'donut', height: '100%', fontFamily: 'inherit' },
                        labels: data.metode_chart.labels,
                        colors: ['#10b981', '#3b82f6'], // Green, Blue
                        legend: { position: 'bottom' },
                        tooltip: { y: { formatter: (val) => { return val + " Transaksi"; } } }
                    }).render();
                } else {
                    document.querySelector("#metode-chart").innerHTML = "<div class='flex h-full items-center justify-center text-gray-400'>Belum ada data</div>";
                }
                
                // 4. FISIK VS DIGITAL (Donut Chart)
                let totalKategori = data.kategori_chart.series.reduce((a,b) => a+b, 0);
                if(totalKategori > 0) {
                    new ApexCharts(document.querySelector("#kategori-chart"), {
                        series: data.kategori_chart.series,
                        chart: { type: 'donut', height: '100%', fontFamily: 'inherit' },
                        labels: data.kategori_chart.labels,
                        colors: ['#ec4899', '#0ea5e9'], // Pink, Sky Blue
                        legend: { position: 'bottom' },
                        tooltip: { y: { formatter: formatRp } }
                    }).render();
                } else {
                    document.querySelector("#kategori-chart").innerHTML = "<div class='flex h-full items-center justify-center text-gray-400'>Belum ada data</div>";
                }

            })
            .catch(err => console.error('Gagal mengambil data chart', err));
    });
</script>
@endpush
