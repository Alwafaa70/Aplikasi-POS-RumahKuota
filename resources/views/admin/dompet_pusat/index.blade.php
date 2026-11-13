@extends('layouts.app')
@section('title', 'Dompet Pusat (Admin View)')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="p-6">
    <div class="mb-8 bg-blue-600 rounded-xl p-6 text-white shadow-lg flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-black mb-1">🏦 Dompet & Kas Pusat (Admin)</h2>
            <p class="text-blue-100 text-sm">Lihat Saldo Dompet Pusat dan kelola permintaan setoran antara Owner dan Admin.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div x-data="{ search: '', filterJenis: '' }">
        <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex gap-4 w-full md:w-auto flex-1">
                <input type="text" x-model="search" placeholder="Cari nama layanan / rekening..." class="w-full md:max-w-xs border-2 border-gray-200 rounded-lg p-2 focus:border-blue-500 outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            
            @if($kasPusat)
            <!-- KAS PUSAT FISIK CARD -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg hover:shadow-xl transition-all overflow-hidden relative text-white" x-show="search === '' && filterJenis === ''">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-extrabold text-xl">Laci Cash Pusat</h3>
                            <p class="text-sm text-green-100 uppercase font-bold tracking-wider mt-1">
                                UANG FISIK
                            </p>
                        </div>
                        <div class="bg-green-700 bg-opacity-50 p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-green-100 text-sm mb-1">Total Saldo (Real-time)</p>
                        <p class="text-3xl font-black tracking-tight">Rp {{ number_format($kasPusat->saldo, 0, ',', '.') }}</p>
                    </div>

                    <div class="flex gap-2">
                        <button onclick="document.getElementById('modalSetorKasAdmin').classList.remove('hidden')" class="flex-1 bg-white text-green-700 font-bold py-2 px-4 rounded-lg shadow hover:bg-gray-100 transition text-sm flex items-center justify-center gap-2">
                        💸 Setor (Request ke Owner)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Setor Kas Fisik Admin -->
            <div id="modalSetorKasAdmin" class="fixed inset-0 z-50 overflow-y-auto hidden">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity"></div>
                    <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                        <div class="bg-green-600 px-6 py-4 flex justify-between items-center text-white">
                            <h3 class="text-lg font-bold">Request Setor Kas Fisik (dari Admin)</h3>
                            <button onclick="document.getElementById('modalSetorKasAdmin').classList.add('hidden')" class="text-green-200 hover:text-white">&times;</button>
                        </div>
                        <form action="{{ route('admin.dompet_pusat.setor_kas') }}" method="POST" enctype="multipart/form-data" class="p-6">
                            @csrf
                            <p class="text-sm text-gray-500 mb-4">Uang ini akan disetorkan ke Owner, tetapi menunggu konfirmasi/persetujuan Owner. Saldo tidak akan terpotong sampai disetujui.</p>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2 text-sm">Nominal (Rp)</label>
                                <input type="number" name="nominal" class="w-full border-gray-300 rounded-lg p-2" min="1" max="{{ $kasPusat->saldo }}" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2 text-sm">Bukti Foto / Transfer</label>
                                <input type="file" name="bukti_transfer" class="w-full border-gray-300 rounded-lg p-2" accept="image/*" required>
                                <p class="text-xs text-gray-500 mt-1">Wajib menyertakan foto bukti setor/uang fisik.</p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2 text-sm">Catatan (Opsional)</label>
                                <textarea name="catatan" class="w-full border-gray-300 rounded-lg p-2" rows="2"></textarea>
                            </div>
                            
                            <div class="flex justify-end gap-2 mt-6">
                                <button type="button" onclick="document.getElementById('modalSetorKasAdmin').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg">Batal</button>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Kirim Permintaan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @foreach($pusatLayanan as $dompet)
                <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden hover:shadow-md transition-shadow relative"
                     x-show="(search === '' || '{{ strtolower($dompet->masterAplikasiLayanan->nama_layanan) }}'.includes(search.toLowerCase()) || '{{ strtolower($dompet->nomor_rekening) }}'.includes(search.toLowerCase()))">
                    
                    @if($dompet->is_qris_tujuan)
                        <div class="absolute top-0 right-0 bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-bl-lg flex items-center gap-1 shadow-sm">
                            â­ QRIS UTAMA
                        </div>
                    @endif

                    <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            @if($dompet->masterAplikasiLayanan->logo_bank)
                                <img src="{{ asset('storage/' . $dompet->masterAplikasiLayanan->logo_bank) }}" class="h-10 w-auto object-contain rounded" alt="Logo">
                            @else
                                <div class="bg-blue-100 text-blue-600 rounded-full w-10 h-10 flex items-center justify-center font-bold">
                                    {{ substr($dompet->masterAplikasiLayanan->nama_layanan, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h4 class="font-bold text-gray-800 leading-tight">{{ $dompet->masterAplikasiLayanan->nama_layanan }}</h4>
                                <p class="text-xs text-gray-500 font-mono tracking-wider">{{ $dompet->nomor_rekening }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex justify-between items-end mb-4">
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Saldo Tersedia</p>
                                <p class="text-2xl font-black text-gray-800">Rp {{ number_format($dompet->saldo_sekarang, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="document.getElementById('modalSetorDigitalAdmin_{{ $dompet->id }}').classList.remove('hidden')" class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-bold py-2 px-3 rounded-lg text-sm transition">
                                Setor
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Setor Digital Admin -->
                <div id="modalSetorDigitalAdmin_{{ $dompet->id }}" class="fixed inset-0 z-50 overflow-y-auto hidden">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity"></div>
                        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                            <div class="bg-red-600 px-6 py-4 flex justify-between items-center text-white">
                                <h3 class="text-lg font-bold">Setor Saldo: {{ $dompet->masterAplikasiLayanan->nama_layanan }}</h3>
                                <button onclick="document.getElementById('modalSetorDigitalAdmin_{{ $dompet->id }}').classList.add('hidden')" class="text-red-200 hover:text-white">&times;</button>
                            </div>
                            <form action="{{ route('admin.dompet_pusat.setor_digital', $dompet->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
                                @csrf
                                <p class="text-sm text-gray-500 mb-4">Buat permintaan setoran digital ke Owner. Saldo tidak akan terpotong sampai disetujui Owner.</p>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-bold mb-2 text-sm">Nominal Setor (Rp)</label>
                                    <input type="number" name="nominal" class="w-full border-gray-300 rounded-lg p-2" min="1" max="{{ $dompet->saldo_sekarang }}" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-bold mb-2 text-sm">Bukti Foto / Transfer</label>
                                    <input type="file" name="bukti_transfer" class="w-full border-gray-300 rounded-lg p-2" accept="image/*" required>
                                    <p class="text-xs text-gray-500 mt-1">Wajib menyertakan foto bukti transfer / setoran.</p>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-bold mb-2 text-sm">Catatan Tambahan</label>
                                    <textarea name="catatan" class="w-full border-gray-300 rounded-lg p-2" rows="2"></textarea>
                                </div>
                                <div class="flex justify-end gap-2 mt-6">
                                    <button type="button" onclick="document.getElementById('modalSetorDigitalAdmin_{{ $dompet->id }}').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg">Batal</button>
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">Kirim Permintaan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 bg-white p-6 rounded-xl shadow">
            <h3 class="text-xl font-bold mb-4">Daftar Permintaan Setoran</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <th class="p-3">Waktu</th>
                            <th class="p-3">Sumber</th>
                            <th class="p-3">Nominal</th>
                            <th class="p-3">Pengaju</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($setorans as $setoran)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">{{ $setoran->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3 font-semibold">
                                @if($setoran->pusat_aplikasi_layanan_id === null)
                                    <span class="text-green-600">Kas Fisik Pusat</span>
                                @else
                                    <span class="text-blue-600">{{ $setoran->dompet->masterAplikasiLayanan->nama_layanan ?? 'Digital' }}</span>
                                @endif
                            </td>
                            <td class="p-3 font-bold">Rp {{ number_format($setoran->nominal, 0, ',', '.') }}</td>
                            <td class="p-3">
                                @if($setoran->jenis_pengaju === 'owner')
                                    <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-bold">Owner</span>
                                @else
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">Admin</span>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($setoran->status === 'pending_admin')
                                    <span class="text-yellow-600 font-bold"><i class="fas fa-clock"></i> Menunggu Anda</span>
                                @elseif($setoran->status === 'pending_owner')
                                    <span class="text-yellow-600 font-bold"><i class="fas fa-clock"></i> Menunggu Owner</span>
                                @elseif($setoran->status === 'selesai')
                                    <span class="text-green-600 font-bold"><i class="fas fa-check-circle"></i> Selesai</span>
                                @elseif($setoran->status === 'ditolak')
                                    <span class="text-red-600 font-bold"><i class="fas fa-times-circle"></i> Ditolak</span>
                                @else
                                    <span class="text-gray-500 font-bold"><i class="fas fa-ban"></i> Batal</span>
                                @endif
                            </td>
                            <td class="p-3 text-center flex justify-center gap-2">
                                @if($setoran->status === 'pending_admin' && $setoran->jenis_pengaju === 'owner')
                                    <button onclick="document.getElementById('modalKonfirmasi_{{ $setoran->id }}').classList.remove('hidden')" class="bg-green-500 hover:bg-green-600 text-white text-xs px-3 py-1 rounded font-bold shadow">
                                        Proses
                                    </button>
                                @endif
                                @if($setoran->status === 'pending_owner' && $setoran->jenis_pengaju === 'admin' && $setoran->admin_id === auth()->id())
                                    <form action="{{ route('admin.dompet_pusat.batal', $setoran->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan ini?')">
                                        @csrf
                                        <button class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded font-bold shadow">
                                            Batal
                                        </button>
                                    </form>
                                @endif
                                @if($setoran->bukti_transfer)
                                    <a href="{{ asset('storage/' . $setoran->bukti_transfer) }}" target="_blank" class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs px-3 py-1 rounded font-bold shadow">
                                        Bukti
                                    </a>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Konfirmasi / Tolak dari Admin -->
                        @if($setoran->status === 'pending_admin' && $setoran->jenis_pengaju === 'owner')
                        <div id="modalKonfirmasi_{{ $setoran->id }}" class="fixed inset-0 z-50 overflow-y-auto hidden text-left">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity"></div>
                                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                                    <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                                        <h3 class="text-lg font-bold">Proses Setoran Owner</h3>
                                        <button onclick="document.getElementById('modalKonfirmasi_{{ $setoran->id }}').classList.add('hidden')" class="text-indigo-200 hover:text-white">&times;</button>
                                    </div>
                                    <div class="p-6">
                                        <div class="mb-4 bg-gray-50 p-3 rounded border">
                                            <p class="text-sm"><strong>Nominal:</strong> Rp {{ number_format($setoran->nominal, 0, ',', '.') }}</p>
                                            <p class="text-sm"><strong>Catatan Owner:</strong> {{ $setoran->catatan_pengaju ?? '-' }}</p>
                                        </div>
                                        
                                        <!-- Form Konfirmasi -->
                                        <form action="{{ route('admin.dompet_pusat.konfirmasi', $setoran->id) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                                            @csrf
                                            <h4 class="font-bold text-green-600 mb-2">Terima & Konfirmasi</h4>
                                            <div class="mb-3">
                                                <label class="block text-xs font-bold text-gray-700 mb-1">Upload Bukti Transfer/Foto Uang</label>
                                                <input type="file" name="bukti_transfer" class="w-full border p-2 rounded text-sm" required accept="image/*">
                                            </div>
                                            <div class="mb-3">
                                                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                                                <input type="text" name="catatan_konfirmasi" class="w-full border p-2 rounded text-sm">
                                            </div>
                                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded">Konfirmasi & Potong Saldo</button>
                                        </form>

                                        <hr class="my-4">

                                        <!-- Form Tolak -->
                                        <form action="{{ route('admin.dompet_pusat.tolak', $setoran->id) }}" method="POST">
                                            @csrf
                                            <h4 class="font-bold text-red-600 mb-2">Tolak Permintaan</h4>
                                            <div class="mb-3">
                                                <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Penolakan</label>
                                                <input type="text" name="catatan_konfirmasi" class="w-full border p-2 rounded text-sm" required>
                                            </div>
                                            <button type="submit" onclick="return confirm('Tolak setoran ini?')" class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-bold py-2 rounded border border-red-300">Tolak Permintaan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">Tidak ada data setoran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
