@extends('layouts.app')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .topbar { background-color: white !important; }
</style>
@endpush

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Laporan Eksekutif</h1>
                <p class="text-gray-500 text-sm mt-1">Analisis performa bisnis dan pergerakan aset Anda secara komprehensif.</p>
            </div>
        </div>

        <!-- Filter Form & Action Buttons -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8 flex flex-col md:flex-row gap-4 items-end justify-between">
            <form action="{{ route('owner.reports.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 w-full md:w-3/4">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                <div class="w-full md:w-1/3">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full border-2 border-gray-200 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none">
                </div>

                <div class="w-full md:w-1/3">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full border-2 border-gray-200 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none">
                </div>
                
                <div class="w-full md:w-1/3">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Pilih Cabang</label>
                    <select name="cabang_id" class="w-full border-2 border-gray-200 rounded-lg p-2.5 bg-white focus:border-blue-500 focus:outline-none">
                        <option value="">Semua Cabang (Konsolidasi)</option>
                        @foreach($semuaCabang as $cb)
                            <option value="{{ $cb->id }}" {{ $cabangId == $cb->id ? 'selected' : '' }}>{{ $cb->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-auto">
                    <button type="submit" class="w-full h-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition transform hover:-translate-y-0.5 shadow-md">
                        Analisa Data
                    </button>
                </div>
            </form>
            
            <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
                <a href="{{ route('owner.reports.export_excel', request()->all()) }}" class="flex-1 md:flex-none text-center bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 px-4 rounded-lg shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Excel
                </a>
                <a href="{{ route('owner.reports.export_pdf', request()->all()) }}" class="flex-1 md:flex-none text-center bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-lg shadow-md transition flex items-center justify-center gap-2" target="_blank">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    PDF
                </a>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="mb-6 overflow-x-auto">
            <ul class="flex whitespace-nowrap border-b-2 border-gray-200">
                <li class="mr-1">
                    <a href="{{ route('owner.reports.index', array_merge(request()->all(), ['tab' => 'profitability'])) }}" 
                       class="inline-block px-6 py-3 font-bold text-sm {{ $tab == 'profitability' ? 'text-blue-600 border-b-4 border-blue-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                       📊 Profitabilitas & Chart
                    </a>
                </li>
                <li class="mr-1">
                    <a href="{{ route('owner.reports.index', array_merge(request()->all(), ['tab' => 'performa'])) }}" 
                       class="inline-block px-6 py-3 font-bold text-sm {{ $tab == 'performa' ? 'text-blue-600 border-b-4 border-blue-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                       🏆 Ranking Cabang
                    </a>
                </li>
                <li class="mr-1">
                    <a href="{{ route('owner.reports.index', array_merge(request()->all(), ['tab' => 'distribusi'])) }}" 
                       class="inline-block px-6 py-3 font-bold text-sm {{ $tab == 'distribusi' ? 'text-blue-600 border-b-4 border-blue-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                       📦 Distribusi Aset
                    </a>
                </li>
                <li class="mr-1">
                    <a href="{{ route('owner.reports.index', array_merge(request()->all(), ['tab' => 'closing'])) }}" 
                       class="inline-block px-6 py-3 font-bold text-sm {{ $tab == 'closing' ? 'text-blue-600 border-b-4 border-blue-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                       🔒 Audit Closing
                    </a>
                </li>
                <li class="mr-1">
                    <a href="{{ route('owner.reports.index', array_merge(request()->all(), ['tab' => 'rincian'])) }}" 
                       class="inline-block px-6 py-3 font-bold text-sm {{ $tab == 'rincian' ? 'text-blue-600 border-b-4 border-blue-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                       🧾 Rincian Transaksi
                    </a>
                </li>
            </ul>
        </div>

        <!-- Tab Contents -->
        <div>
            <!-- 1. Profitabilitas & Chart -->
            @if($tab == 'profitability')
            <div class="space-y-6">
                <!-- Highlight Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-20"><svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Pendapatan (Omzet)</p>
                        <p class="text-3xl font-black text-gray-900">Rp {{ number_format($profitability['total_pendapatan'], 0, ',', '.') }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-20"><svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg></div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total HPP (Modal Barang)</p>
                        <p class="text-3xl font-black text-red-600">- Rp {{ number_format($profitability['total_hpp'], 0, ',', '.') }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-20"><svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg></div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Fee Digital B</p>
                        <p class="text-3xl font-black text-blue-600">+ Rp {{ number_format($profitability['total_fee_digital_b'], 0, ',', '.') }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg relative overflow-hidden text-white">
                        <div class="absolute top-0 right-0 p-4 opacity-20"><svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></div>
                        <p class="text-xs font-bold text-green-100 uppercase tracking-widest mb-1 text-shadow">Total Laba Kotor (Profit)</p>
                        <p class="text-4xl font-black drop-shadow-md">Rp {{ number_format($profitability['laba_kotor'], 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <!-- Line Chart -->
                    <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-4">📈 Tren Omzet Harian</h3>
                        <div class="relative h-72">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Doughnut Chart -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-4">💳 Metode Pembayaran</h3>
                        <div class="relative h-64 flex justify-center">
                            <canvas id="doughnutChart"></canvas>
                        </div>
                        <div class="mt-4 flex justify-center gap-4 text-xs font-bold text-gray-600">
                            <div class="flex items-center"><span class="w-3 h-3 bg-[#4ade80] rounded-full mr-1"></span> Cash</div>
                            <div class="flex items-center"><span class="w-3 h-3 bg-[#3b82f6] rounded-full mr-1"></span> QRIS</div>
                            <div class="flex items-center"><span class="w-3 h-3 bg-[#a855f7] rounded-full mr-1"></span> Digital</div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Line Chart Data
                    const lineCtx = document.getElementById('lineChart').getContext('2d');
                    new Chart(lineCtx, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($profitability['chart_labels']) !!},
                            datasets: [{
                                label: 'Omzet (Rp)',
                                data: {!! json_encode($profitability['chart_data']) !!},
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#3b82f6',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { borderDash: [5, 5] },
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + (value/1000) + 'k';
                                        }
                                    }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });

                    // Doughnut Chart Data
                    const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
                    new Chart(doughnutCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Cash', 'QRIS', 'Digital/Tarik Tunai'],
                            datasets: [{
                                data: [
                                    {{ $profitability['metode_cash'] }}, 
                                    {{ $profitability['metode_qris'] }}, 
                                    {{ $profitability['metode_digital'] }}
                                ],
                                backgroundColor: ['#4ade80', '#3b82f6', '#a855f7'],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                });
            </script>
            @endif

            <!-- 2. Performa Cabang -->
            @if($tab == 'performa')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cabang</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Transaksi</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pendapatan</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fee B</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($performaCabang as $index => $cabang)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($loop->iteration == 1)
                                    <span class="px-3 py-1 bg-yellow-400 text-yellow-900 rounded-full font-black shadow-sm">🥇 #1</span>
                                @elseif($loop->iteration == 2)
                                    <span class="px-3 py-1 bg-gray-300 text-gray-800 rounded-full font-black shadow-sm">🥈 #2</span>
                                @elseif($loop->iteration == 3)
                                    <span class="px-3 py-1 bg-amber-600 text-white rounded-full font-black shadow-sm">🥉 #3</span>
                                @else
                                    <span class="font-bold text-gray-500 px-3">#{{ $loop->iteration }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">{{ $cabang->nama_cabang }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">{{ $cabang->total_transaksi }}x</td>
                            <td class="px-6 py-4 whitespace-nowrap text-green-600 font-bold">Rp {{ number_format($cabang->total_pendapatan, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-blue-600 font-bold">Rp {{ number_format($cabang->total_fee, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- 3. Distribusi -->
            @if($tab == 'distribusi')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tgl Mutasi</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tujuan</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item Barang</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nilai Modal Aset</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($distribusi as $mutasi)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">{{ $mutasi->cabangTujuan->nama_cabang }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <ul class="list-disc pl-4 space-y-1">
                                @foreach($mutasi->details as $d)
                                    <li>{{ $d->barang->nama_barang }} <span class="font-bold">({{ $d->kuantitas }} pcs)</span></li>
                                @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-black text-indigo-600">Rp {{ number_format($mutasi->nilai_modal_dipindahkan, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($mutasi->status == 'dikirim')
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">Perjalanan</span>
                                @else
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-200">Diterima</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada riwayat distribusi pada tanggal ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif

            <!-- 4. Closing -->
            @if($tab == 'closing')
            <div class="space-y-6">
                @forelse($monitoringClosing as $closing)
                <div class="bg-white border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition">
                    <div class="p-4 flex justify-between items-center border-b {{ $closing->status_fisik == 'minus' ? 'bg-red-50' : ($closing->status_fisik == 'plus' ? 'bg-blue-50' : 'bg-green-50') }}">
                        <div>
                            <p class="font-black text-lg text-gray-800">{{ $closing->cabang->nama_cabang }}</p>
                            <p class="text-sm font-medium text-gray-600">Kasir: {{ $closing->kasir->name }} | Tgl: {{ \Carbon\Carbon::parse($closing->tanggal)->format('d M Y') }}</p>
                        </div>
                        <div>
                            @if($closing->status_fisik == 'minus')
                                <span class="px-4 py-1.5 bg-red-600 text-white font-black rounded-lg text-sm shadow-sm">MINUS</span>
                            @elseif($closing->status_fisik == 'plus')
                                <span class="px-4 py-1.5 bg-blue-600 text-white font-black rounded-lg text-sm shadow-sm">PLUS</span>
                            @else
                                <span class="px-4 py-1.5 bg-green-600 text-white font-black rounded-lg text-sm shadow-sm">BALANCE</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Uang Fisik -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2"><svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> Rekap Fisik Laci</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Hitungan Kasir:</span>
                                    <span class="font-bold text-gray-900">Rp {{ number_format($closing->uang_fisik_aktual, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-500">Catatan Sistem:</span>
                                    <span class="font-bold text-gray-900">Rp {{ number_format($closing->uang_fisik_sistem, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between pt-1">
                                    <span class="font-bold text-gray-700">Selisih:</span>
                                    <span class="font-black {{ $closing->status_fisik == 'minus' ? 'text-red-600' : 'text-gray-900' }}">Rp {{ number_format($closing->selisih_fisik, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Uang Digital -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2"><svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> Rekap EDC/Digital</h4>
                            <div class="space-y-3 text-sm">
                                @foreach($closing->details as $detail)
                                <div>
                                    <p class="font-bold text-gray-700">{{ $detail->masterAplikasiLayanan->nama_layanan ?? '-' }}</p>
                                    <div class="flex justify-between text-gray-600 text-xs mt-1">
                                        <span>Aktual: Rp{{ number_format($detail->saldo_aktual, 0, ',', '.') }}</span>
                                        <span>Sistem: Rp{{ number_format($detail->saldo_sistem, 0, ',', '.') }}</span>
                                    </div>
                                    @if($detail->selisih != 0)
                                        <p class="text-xs font-bold text-red-600 mt-1">Selisih: Rp{{ number_format($detail->selisih, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    @if($closing->catatan_kasir)
                    <div class="px-6 py-4 bg-yellow-50 text-sm border-t border-yellow-100">
                        <span class="font-bold text-yellow-800">Catatan Kasir:</span> <span class="text-yellow-700">{{ $closing->catatan_kasir }}</span>
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center bg-white rounded-xl p-10 border border-gray-200">
                    <p class="text-gray-500">Belum ada history closing pada rentang tanggal ini.</p>
                </div>
                @endforelse
            </div>
            @endif
            
            <!-- 5. Rincian Transaksi Mentah -->
            @if($tab == 'rincian')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Struk</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu & Kasir</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item Beli</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Metode</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Rp</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($rincianTransaksi as $trx)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-800">#TRX-{{ $trx->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->format('d M Y, H:i') }}</p>
                                <p class="text-xs text-gray-500">Cab: {{ $trx->cabang->nama_cabang }} | Kasir: {{ $trx->kasir->name }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach($trx->detail as $dt)
                                        <li>{{ $dt->barang->nama_barang ?? 'Produk Digital' }} (x{{ $dt->kuantitas }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($trx->metode_pembayaran == 'cash')
                                    <span class="bg-green-100 text-green-800 font-bold px-3 py-1 rounded-full text-xs">CASH</span>
                                @elseif($trx->metode_pembayaran == 'qris')
                                    <span class="bg-blue-100 text-blue-800 font-bold px-3 py-1 rounded-full text-xs">QRIS</span>
                                @else
                                    <span class="bg-purple-100 text-purple-800 font-bold px-3 py-1 rounded-full text-xs uppercase">{{ str_replace('_', ' ', $trx->metode_pembayaran) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-black text-gray-900">
                                Rp {{ number_format($trx->total_tagihan, 0, ',', '.') }}
                                @if($trx->fee_transaksi > 0)
                                    <p class="text-xs font-bold text-blue-600 mt-1">+ Fee Rp{{ number_format($trx->fee_transaksi, 0, ',', '.') }}</p>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada transaksi ditemukan pada filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
