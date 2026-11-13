@extends('layouts.app')
@section('title', 'Historis Dompet & Kas Pusat')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar { z-index: 50; }
        .main-content { min-width: 0; }
        /* Active tab styling */
        [aria-selected="true"] {
            border-bottom-width: 2px;
        }
    </style>
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.setAttribute('aria-selected', 'false');
                el.classList.remove('text-indigo-600', 'border-indigo-600');
                el.classList.add('border-transparent');
            });
            
            document.getElementById(tabId).classList.remove('hidden');
            const activeBtn = document.getElementById(tabId + '-btn');
            activeBtn.setAttribute('aria-selected', 'true');
            activeBtn.classList.add('text-indigo-600', 'border-indigo-600');
            activeBtn.classList.remove('border-transparent');
        }
    </script>
@endpush

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    
    <div class="mb-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 p-2 rounded-lg text-gray-600 hover:bg-gray-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800">Historis Dompet & Kas Pusat (Buku Besar)</h1>
                <p class="text-sm text-gray-500">Log pencatatan seluruh aliran Uang Fisik dan Saldo Digital di Pusat.</p>
            </div>
        </div>
    </div>

    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
        <form action="{{ route('master.dompet_pusat.historis') }}" method="GET" class="flex items-end gap-3 w-full md:w-auto">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-indigo-700 transition">Filter</button>
            @if(request('start_date') || request('end_date'))
                <a href="{{ route('master.dompet_pusat.historis') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>

        <div class="flex gap-2">
            <a href="{{ route('master.dompet_pusat.export_excel', request()->all()) }}" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-green-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel
            </a>
            <a href="{{ route('master.dompet_pusat.export_pdf', request()->all()) }}" target="_blank" class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-red-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                PDF
            </a>
        </div>
    </div>

    <!-- RINGKASAN MUTASI PER DOMPET & KAS -->
    <div class="mb-6 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h2 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Rincian Mutasi Saldo per Layanan ({{ $startDate && $endDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y').' s/d '.\Carbon\Carbon::parse($endDate)->format('d/m/Y') : ($startDate ? 'Sejak '.\Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Keseluruhan / All-Time') }})
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 border border-gray-200 rounded-lg">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 border-r">Nama Layanan / Akun</th>
                        <th class="px-4 py-3 border-r text-right">Saldo Awal (Rp)</th>
                        <th class="px-4 py-3 border-r text-right text-green-700">Total Masuk (Rp)</th>
                        <th class="px-4 py-3 border-r text-right text-red-700">Total Keluar (Rp)</th>
                        <th class="px-4 py-3 border-r text-right font-bold text-indigo-900">Saldo Akhir Periode (Rp)</th>
                        <th class="px-4 py-3 text-right bg-gray-200 font-bold text-gray-800">Saldo Sekarang (Realtime)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($dompetSummaries as $dompet)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-gray-800 border-r flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Digital ({{ $dompet->nama_dompet }})
                        </td>
                        <td class="px-4 py-3 text-right border-r text-gray-600 font-medium">
                            {{ number_format($dompet->saldo_awal, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right border-r text-green-600 font-bold">
                            + {{ number_format($dompet->masuk, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right border-r text-red-600 font-bold">
                            - {{ number_format($dompet->keluar, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right border-r font-black text-indigo-700">
                            {{ number_format($dompet->saldo_akhir, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right bg-gray-50 font-black text-gray-800">
                            Rp {{ number_format($dompet->saldo_sekarang, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="hover:bg-gray-50 bg-green-50/30">
                        <td class="px-4 py-3 font-semibold text-gray-800 border-r flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Kas Fisik (Pusat)
                        </td>
                        <td class="px-4 py-3 text-right border-r text-gray-600 font-medium">
                            {{ number_format($kasSummary->saldo_awal, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right border-r text-green-600 font-bold">
                            + {{ number_format($kasSummary->masuk, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right border-r text-red-600 font-bold">
                            - {{ number_format($kasSummary->keluar, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right border-r font-black text-indigo-700">
                            {{ number_format($kasSummary->saldo_akhir, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right bg-gray-100 font-black text-gray-800">
                            Rp {{ number_format($kasSummary->saldo_sekarang, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABS -->
    <div class="bg-white rounded-t-xl border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500" role="tablist">
            <li class="mr-2" role="presentation">
                <button onclick="switchTab('inflow')" class="tab-btn inline-block p-4 border-b-2 text-indigo-600 border-indigo-600 rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition" id="inflow-btn" type="button" role="tab" aria-selected="true">
                    Uang & Saldo Masuk
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button onclick="switchTab('outflow')" class="tab-btn inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition" id="outflow-btn" type="button" role="tab" aria-selected="false">
                    Pengeluaran & Bantuan Keluar
                </button>
            </li>
        </ul>
    </div>

    <div class="bg-white rounded-b-xl shadow-sm border border-t-0 border-gray-200 mb-8 p-6">
        
        <!-- TAB 1: MASUK (INFLOWS) -->
        <div class="tab-content block" id="inflow" role="tabpanel">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 border border-gray-200">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-6 py-3 border-r">Tanggal / Waktu</th>
                            <th scope="col" class="px-6 py-3 border-r">Tipe Uang</th>
                            <th scope="col" class="px-6 py-3 border-r text-right">Nominal (Rp)</th>
                            <th scope="col" class="px-6 py-3">Keterangan / Sumber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inflows as $item)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 border-r whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 border-r font-bold">
                                @if($item->tipe_uang === 'Digital')
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        Digital ({{ $item->nama_dompet }})
                                    </span>
                                @else
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        Fisik (Kas)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right border-r font-black text-gray-800">
                                + {{ number_format($item->nominal_custom, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                {!! $item->keterangan_custom !!}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada data uang/saldo masuk.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: KELUAR (OUTFLOWS) -->
        <div class="tab-content hidden" id="outflow" role="tabpanel">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 border border-gray-200">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-6 py-3 border-r">Tanggal / Waktu</th>
                            <th scope="col" class="px-6 py-3 border-r">Tipe Uang</th>
                            <th scope="col" class="px-6 py-3 border-r text-right">Nominal (Rp)</th>
                            <th scope="col" class="px-6 py-3">Keterangan / Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outflows as $item)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 border-r whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 border-r font-bold">
                                @if($item->tipe_uang === 'Digital')
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        Digital ({{ $item->nama_dompet }})
                                    </span>
                                @else
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        Fisik (Kas)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right border-r font-black text-red-600">
                                - {{ number_format($item->nominal_custom, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                {!! $item->keterangan_custom !!}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data pengeluaran/bantuan keluar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
