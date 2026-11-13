@extends('layouts.app')
@section('title', 'Dompet Pusat & Setoran Owner')

@section('content')
<div class="p-6">
    <div class="mb-8 bg-blue-600 rounded-xl p-6 text-white shadow-lg flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-black mb-1">🏦 Dompet Admin Pusat</h2>
            <p class="text-blue-100 text-sm">Kelola rekening penampung pendapatan perusahaan dan tujuan akhir QRIS kasir.</p>
        </div>
        <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-white text-blue-600 px-5 py-2.5 rounded-lg font-bold shadow-md hover:bg-gray-50 transition transform hover:scale-105">
            + Tambah Dompet
        </button>
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

    @if(!$qrisMaster)
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded shadow-sm">
            <p class="text-yellow-700 font-bold">Peringatan: Belum ada tujuan QRIS yang diatur di Master Layanan!</p>
            <p class="text-sm text-yellow-600 mt-1">Sistem kasir akan menolak pembayaran QRIS sampai Anda mengatur layanan QRIS di menu <a href="{{ route('master.aplikasi_layanan.index') }}" class="underline font-bold">Master Aplikasi Layanan</a> dan membuat dompet penampungnya di sini.</p>
        </div>
    @endif

    <div class="mb-6 flex gap-4">
        <button onclick="document.getElementById('modalDaftarSetoran').classList.remove('hidden')" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg font-bold shadow-md hover:bg-indigo-700 transition transform hover:scale-105 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Daftar Permintaan Setoran
            @php $pendingCount = $setorans->where('status', 'pending_owner')->count(); @endphp
            @if($pendingCount > 0)
                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $pendingCount }}</span>
            @endif
        </button>
    </div>

    @if(!$kasPusat)
    <!-- FORM INISIASI KAS PUSAT -->
    <div class="bg-green-50 border-2 border-green-400 rounded-xl p-8 mb-8 shadow-sm">
        <div class="text-center mb-6">
            <div class="bg-green-100 text-green-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-black text-green-800">Inisiasi Laci Cash Pusat</h2>
            <p class="text-green-600 mt-2">Tentukan saldo awal kas fisik (uang kertas/koin) di pusat. Aksi ini hanya dilakukan <strong class="uppercase">satu kali</strong>.</p>
        </div>
        <form action="{{ route('master.dompet_pusat.init_kas') }}" method="POST" class="max-w-md mx-auto">
            @csrf
            <div class="mb-4">
                <label class="block text-green-800 font-bold mb-2">Saldo Awal Kas Fisik</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 font-bold text-gray-500">Rp</span>
                    <input type="number" name="saldo_awal" class="w-full border-2 border-green-200 rounded-lg p-3 pl-10 focus:border-green-500 focus:outline-none transition" placeholder="0" min="0" required>
                </div>
            </div>
            <button type="submit" onclick="return confirm('Apakah Anda yakin ini adalah saldo awal yang benar? Anda tidak bisa mengeditnya secara bebas setelah ini!')" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg shadow hover:bg-green-700 transition">
                Simpan & Mulai Operasional
            </button>
        </form>
    </div>
    @endif

    <div x-data="{ search: '', filterJenis: '' }">
        <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex gap-4 w-full md:w-auto flex-1">
                <input type="text" x-model="search" placeholder="Cari nama layanan / rekening..." class="w-full md:max-w-xs border-2 border-gray-200 rounded-lg p-2 focus:border-blue-500 outline-none">
                <select x-model="filterJenis" class="border-2 border-gray-200 rounded-lg p-2 focus:border-blue-500 outline-none bg-white">
                    <option value="">Semua Jenis</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="ewallet">E-Wallet</option>
                </select>
            </div>
        </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        @if($kasPusat)
        <!-- KAS PUSAT FISIK CARD -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg hover:shadow-xl transition-all overflow-hidden relative text-white" x-show="search === '' && filterJenis === ''">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-extrabold text-xl">Laci Cash Pusat</h3>
                        <p class="text-sm text-green-100 uppercase font-bold tracking-wider mt-1">
                            UANG FISIK KERTAS/KOIN
                        </p>
                    </div>
                    <div class="bg-green-700 bg-opacity-50 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                
                <div class="mb-5">
                    <p class="text-green-100 text-sm font-semibold mb-1">Saldo Tersedia</p>
                    <div class="text-3xl font-black tracking-tight">
                        Rp {{ number_format($kasPusat->saldo, 0, ',', '.') }}
                    </div>
                </div>

                <div class="pt-4 border-t border-green-400">
                    <button onclick="document.getElementById('modalSetorOwner').classList.remove('hidden')" class="w-full bg-white text-green-700 hover:bg-green-50 text-sm font-bold px-4 py-2 rounded shadow transition">
                        💸 Request Setoran (ke Admin)
                    </button>
                </div>
            </div>
        </div>
        @endif

        @forelse($pusatLayanan as $dompet)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all overflow-hidden relative"
             x-show="(search === '' || '{{ strtolower($dompet->masterAplikasiLayanan->nama_layanan ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($dompet->nomor_rekening) }}'.includes(search.toLowerCase())) && (filterJenis === '' || '{{ $dompet->masterAplikasiLayanan->jenis_layanan ?? '' }}' === filterJenis)">
            
            @if($dompet->is_qris_tujuan)
                <div class="absolute top-0 right-0 bg-green-500 text-white text-xs font-black px-3 py-1 rounded-bl-lg shadow-sm">
                    ✅ PENAMPUNG QRIS UTAMA
                </div>
            @endif
            
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-extrabold text-xl text-gray-800">
                            {{ $dompet->masterAplikasiLayanan ? $dompet->masterAplikasiLayanan->nama_layanan : 'Layanan Terhapus' }}
                        </h3>
                        <p class="text-sm text-gray-500 uppercase font-bold tracking-wider mt-1">
                            {{ $dompet->nomor_rekening }}
                        </p>
                    </div>
                </div>
                
                <div class="mb-5">
                    <p class="text-gray-500 text-sm font-semibold mb-1">Saldo Tersedia</p>
                    <div class="text-3xl font-black text-blue-600 tracking-tight">
                        Rp {{ number_format($dompet->saldo_sekarang, 0, ',', '.') }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-4 border-t border-gray-100">
                    <button onclick="openTopUpModal({{ $dompet->id }}, '{{ $dompet->masterAplikasiLayanan ? $dompet->masterAplikasiLayanan->nama_layanan : 'Layanan' }}')" class="text-center bg-blue-50 text-blue-600 hover:bg-blue-100 text-sm font-bold px-2 py-2 rounded transition">
                        ↑ Top Up
                    </button>
                    <button onclick="openSetorDigitalModal({{ $dompet->id }}, '{{ $dompet->masterAplikasiLayanan ? $dompet->masterAplikasiLayanan->nama_layanan : 'Layanan' }}', {{ $dompet->saldo_sekarang }})" class="text-center bg-green-50 text-green-600 hover:bg-green-100 text-sm font-bold px-2 py-2 rounded transition">
                        💸 Request Setor
                    </button>
                    <button onclick="openEditModal({{ $dompet->id }}, '{{ $dompet->nomor_rekening }}', '{{ $dompet->masterAplikasiLayanan ? $dompet->masterAplikasiLayanan->nama_layanan : 'Layanan' }}')" class="text-center bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-bold px-2 py-2 rounded transition">
                        Edit
                    </button>
                    <button onclick="openHapusModal({{ $dompet->id }}, '{{ $dompet->masterAplikasiLayanan ? $dompet->masterAplikasiLayanan->nama_layanan : 'Layanan' }} - {{ $dompet->nomor_rekening }}')" class="flex-none w-full text-center bg-red-50 text-red-600 hover:bg-red-100 text-sm font-bold px-2 py-2 rounded transition">
                        Hapus
                    </button>
                    
                    @if(!$dompet->is_qris_tujuan)
                        <form action="{{ route('master.dompet_pusat.set_qris', $dompet->id) }}" method="POST" class="col-span-2" onsubmit="return confirm('Apakah Anda yakin ingin mengubah rekening tujuan QRIS sistem ke dompet ini? Semua pembayaran QRIS kasir akan diarahkan ke saldo ini.');">
                            @csrf
                            <button type="submit" class="w-full text-center bg-yellow-100 text-yellow-800 hover:bg-yellow-200 text-sm font-bold px-2 py-2 rounded transition shadow-sm border border-yellow-300">
                                ⭐ Jadikan QRIS Tujuan Utama
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm border border-dashed border-gray-300 p-10 text-center">
            <div class="mx-auto bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Belum Ada Dompet</h3>
            <p class="text-gray-500">Anda belum mengatur rekening penampung pendapatan untuk Admin Pusat.</p>
            <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded shadow font-bold hover:bg-blue-700">
                Buat Dompet Pertama
            </button>
        </div>
        @endforelse
    </div>
    </div> <!-- End x-data -->

<!-- Modal Daftar Permintaan Setoran -->
<div id="modalDaftarSetoran" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h3 class="text-xl font-extrabold text-gray-800">Daftar Permintaan Setoran</h3>
            <button onclick="document.getElementById('modalDaftarSetoran').classList.add('hidden')" class="text-gray-400 hover:text-gray-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto">
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
                                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-bold">Anda (Owner)</span>
                            @else
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">Admin Pusat</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if($setoran->status === 'pending_admin')
                                <span class="text-yellow-600 font-bold"><i class="fas fa-clock"></i> Menunggu Admin</span>
                            @elseif($setoran->status === 'pending_owner')
                                <span class="text-yellow-600 font-bold"><i class="fas fa-clock"></i> Menunggu Anda</span>
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
                                <form action="{{ route('master.dompet_pusat.setoran.batal', $setoran->id) }}" method="POST" onsubmit="return confirm('Batalkan permintaan ini?')">
                                    @csrf
                                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs px-3 py-1 rounded font-bold shadow">
                                        Batal
                                    </button>
                                </form>
                            @endif
                            @if($setoran->status === 'pending_owner' && $setoran->jenis_pengaju === 'admin')
                                <button onclick="document.getElementById('modalKonfirmasiOwner_{{ $setoran->id }}').classList.remove('hidden')" class="bg-green-500 hover:bg-green-600 text-white text-xs px-3 py-1 rounded font-bold shadow">
                                    Konfirmasi
                                </button>
                            @endif
                            @if($setoran->bukti_transfer)
                                <a href="{{ asset('storage/' . $setoran->bukti_transfer) }}" target="_blank" class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs px-3 py-1 rounded font-bold shadow">
                                    Bukti
                                </a>
                            @endif
                        </td>
                    </tr>

                    <!-- Modal Konfirmasi / Tolak dari Owner -->
                    @if($setoran->status === 'pending_owner' && $setoran->jenis_pengaju === 'admin')
                    <div id="modalKonfirmasiOwner_{{ $setoran->id }}" class="fixed inset-0 z-50 overflow-y-auto hidden text-left">
                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity"></div>
                            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                                    <h3 class="text-lg font-bold">Proses Setoran Admin</h3>
                                    <button onclick="document.getElementById('modalKonfirmasiOwner_{{ $setoran->id }}').classList.add('hidden')" class="text-indigo-200 hover:text-white">&times;</button>
                                </div>
                                <div class="p-6">
                                    <div class="mb-4 bg-gray-50 p-3 rounded border">
                                        <p class="text-sm"><strong>Nominal:</strong> Rp {{ number_format($setoran->nominal, 0, ',', '.') }}</p>
                                        <p class="text-sm"><strong>Catatan Admin:</strong> {{ $setoran->catatan_pengaju ?? '-' }}</p>
                                        @if($setoran->bukti_transfer)
                                            <a href="{{ asset('storage/' . $setoran->bukti_transfer) }}" target="_blank" class="text-sm text-blue-600 underline mt-2 inline-block">Lihat Bukti Foto/Transfer</a>
                                        @endif
                                    </div>
                                    
                                    <!-- Form Konfirmasi (Terima) -->
                                    <form action="{{ route('master.dompet_pusat.setoran.terima', $setoran->id) }}" method="POST" class="mb-4">
                                        @csrf
                                        <h4 class="font-bold text-green-600 mb-2">Terima Setoran</h4>
                                        <button type="submit" onclick="return confirm('Yakin menerima setoran ini? Saldo dompet akan berkurang.')" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded">Terima & Konfirmasi</button>
                                    </form>

                                    <hr class="my-4">

                                    <!-- Form Tolak -->
                                    <form action="{{ route('master.dompet_pusat.setoran.tolak', $setoran->id) }}" method="POST">
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
</div>

<!-- Modal Tambah Dompet -->
<div id="modalTambah" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h3 class="text-xl font-extrabold text-gray-800">Setup Dompet Pusat</h3>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-gray-400 hover:text-gray-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form action="{{ route('master.dompet_pusat.store') }}" method="POST" class="p-6" onsubmit="return confirm('Apakah Anda yakin data dompet ini sudah benar? Tolong cek kembali untuk menghindari salah input/typo.')">
            @csrf
            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Pilih Aplikasi Layanan</label>
                <select name="master_aplikasi_layanan_id" class="w-full border-2 border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:border-blue-500 focus:outline-none transition" required>
                    <option value="">-- Pilih Layanan --</option>
                    @foreach($masterLayanan as $master)
                        <option value="{{ $master->id }}">
                            {{ $master->nama_layanan }} ({{ strtoupper($master->jenis_layanan) }})
                            @if($master->is_qris_tujuan) - [PENAMPUNG QRIS] @endif
                        </option>
                    @endforeach
                </select>
                @if($masterLayanan->isEmpty())
                    <p class="text-red-500 text-xs mt-1">Anda belum membuat Master Aplikasi Layanan!</p>
                @endif
            </div>
            
            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Nomor Rekening / No. HP</label>
                <input type="text" name="nomor_rekening" class="w-full border-2 border-gray-200 rounded-lg p-3 focus:border-blue-500 focus:outline-none transition font-mono" placeholder="Masukkan nomor" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Saldo Awal</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 font-bold text-gray-500">Rp</span>
                    <input type="number" name="saldo_awal" class="w-full border-2 border-gray-200 rounded-lg p-3 pl-10 focus:border-blue-500 focus:outline-none transition" value="0" min="0" required>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-bold shadow-md hover:bg-blue-700 transition transform hover:-translate-y-0.5">Simpan Dompet</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Dompet -->
<div id="modalEdit" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h3 class="text-xl font-extrabold text-gray-800">Edit Nomor Rekening</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-gray-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form id="formEdit" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Aplikasi Layanan</label>
                <input type="text" id="edit_nama_layanan" class="w-full border-2 border-gray-200 rounded-lg p-3 bg-gray-100 cursor-not-allowed" readonly>
            </div>

            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Nomor Rekening / No. HP</label>
                <input type="text" id="edit_nomor_rekening" name="nomor_rekening" class="w-full border-2 border-gray-200 rounded-lg p-3 focus:border-blue-500 focus:outline-none transition font-mono" required>
            </div>
            
            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-bold shadow-md hover:bg-blue-700 transition transform hover:-translate-y-0.5">Update Rekening</button>
            </div>
        </form>
    </div>
</div>

@if($kasPusat)
<!-- Modal Setor Owner -->
<div id="modalSetorOwner" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-green-500 border-b flex justify-between items-center text-white">
            <h3 class="text-xl font-extrabold">Request Setor Cash Pusat (ke Admin)</h3>
            <button onclick="document.getElementById('modalSetorOwner').classList.add('hidden')" class="text-green-100 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form action="{{ route('master.dompet_pusat.setor_owner') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            
            <div class="mb-4 bg-green-50 p-3 rounded-lg border border-green-100">
                <p class="text-sm text-green-700 font-bold mb-1">Saldo Kas Pusat Saat Ini:</p>
                <p class="text-xl font-black text-green-800">Rp {{ number_format($kasPusat->saldo, 0, ',', '.') }}</p>
            </div>

            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Nominal Setoran</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 font-bold text-gray-500">Rp</span>
                    <input type="number" name="nominal" class="w-full border-2 border-gray-200 rounded-lg p-3 pl-10 focus:border-green-500 focus:outline-none transition" max="{{ $kasPusat->saldo }}" placeholder="Maks: {{ $kasPusat->saldo }}" required>
                </div>
            </div>
            
            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Bukti Setoran (Opsional)</label>
                <input type="file" name="bukti_foto" accept="image/*" class="w-full border-2 border-gray-200 rounded-lg p-2 focus:border-green-500 transition">
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maks: 2MB.</p>
            </div>
            
            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Catatan (Opsional)</label>
                <textarea name="catatan" rows="2" class="w-full border-2 border-gray-200 rounded-lg p-3 focus:border-green-500 focus:outline-none transition" placeholder="Contoh: Setoran minggu ke-4"></textarea>
            </div>
            
            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalSetorOwner').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white rounded-lg font-bold shadow-md hover:bg-green-700 transition transform hover:-translate-y-0.5">Kirim Setoran</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Modal Top Up Dompet -->
<div id="modalTopUp" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b flex justify-between items-center">
            <h3 class="text-xl font-extrabold text-blue-800">Top Up Saldo Dompet</h3>
            <button onclick="document.getElementById('modalTopUp').classList.add('hidden')" class="text-gray-400 hover:text-gray-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form id="formTopUp" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Tujuan Top Up</label>
                <input type="text" id="topup_nama_layanan" class="w-full border-2 border-gray-200 rounded-lg p-3 bg-gray-100 cursor-not-allowed font-bold" readonly>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nominal Top Up</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 font-bold text-gray-500">Rp</span>
                    <input type="number" name="nominal" class="w-full border-2 border-gray-200 rounded-lg p-3 pl-10 focus:border-blue-500 focus:outline-none transition" min="1" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Bukti Transfer (Foto/Gambar)</label>
                <input type="file" name="bukti_foto" accept="image/*" class="w-full border-2 border-gray-200 rounded-lg p-2 focus:border-blue-500 transition" required>
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maks: 2MB.</p>
            </div>
            
            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Catatan (Opsional)</label>
                <textarea name="catatan" rows="2" class="w-full border-2 border-gray-200 rounded-lg p-3 focus:border-blue-500 focus:outline-none transition" placeholder="Contoh: Top up rutin bulanan"></textarea>
            </div>
            
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalTopUp').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-bold shadow-md hover:bg-blue-700 transition transform hover:-translate-y-0.5">Top Up Sekarang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Setor Saldo Digital -->
<div id="modalSetorDigital" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 bg-green-500 border-b flex justify-between items-center text-white">
            <h3 class="text-xl font-extrabold">Request Setor Saldo Digital (ke Admin)</h3>
            <button onclick="document.getElementById('modalSetorDigital').classList.add('hidden')" class="text-green-100 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form id="formSetorDigital" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            
            <div class="mb-4 bg-green-50 p-3 rounded-lg border border-green-100">
                <p class="text-sm text-green-700 font-bold mb-1">Dompet: <span id="setordigital_nama_layanan"></span></p>
                <p class="text-xl font-black text-green-800" id="setordigital_saldo_teks">Rp 0</p>
            </div>

            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Nominal Setoran</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 font-bold text-gray-500">Rp</span>
                    <input type="number" id="setordigital_nominal" name="nominal" class="w-full border-2 border-gray-200 rounded-lg p-3 pl-10 focus:border-green-500 focus:outline-none transition" required>
                </div>
            </div>
            
            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Bukti Setoran (Opsional)</label>
                <input type="file" name="bukti_foto" accept="image/*" class="w-full border-2 border-gray-200 rounded-lg p-2 focus:border-green-500 transition">
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maks: 2MB.</p>
            </div>
            
            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2">Catatan (Opsional)</label>
                <textarea name="catatan" rows="2" class="w-full border-2 border-gray-200 rounded-lg p-3 focus:border-green-500 focus:outline-none transition" placeholder="Contoh: Penarikan saldo ke owner"></textarea>
            </div>
            
            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalSetorDigital').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white rounded-lg font-bold shadow-md hover:bg-green-700 transition transform hover:-translate-y-0.5">Kirim Setoran</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Dompet -->
<div id="modalHapusDompet" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50 transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border-t-8 border-red-600">
        <div class="p-6 text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-2xl font-black text-gray-800 mb-2">PERINGATAN KERAS!</h3>
            <p class="text-gray-600 mb-4 text-sm">
                Apakah Anda benar-benar yakin ingin menghapus dompet <strong id="hapus_nama_dompet" class="text-gray-900"></strong>?
            </p>
            <div class="bg-red-50 p-4 rounded-lg border border-red-200 mb-6 text-left">
                <p class="text-red-700 text-sm font-bold flex items-start gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Aksi ini akan MENGHILANGKAN seluruh data saldo dan riwayat transaksi secara permanen!
                </p>
            </div>
            <form id="formHapusDompet" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-center gap-3">
                    <button type="button" onclick="document.getElementById('modalHapusDompet').classList.add('hidden')" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-bold hover:bg-gray-300 transition w-full">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-600 text-white rounded-lg font-bold shadow-md hover:bg-red-700 transition w-full">
                        Ya, Hapus Permanen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@push('scripts')
<script>
    function openEditModal(id, nomorRekening, namaLayanan) {
        document.getElementById('formEdit').action = '/master/dompet-pusat/' + id;
        document.getElementById('edit_nama_layanan').value = namaLayanan;
        document.getElementById('edit_nomor_rekening').value = nomorRekening;
        document.getElementById('modalEdit').classList.remove('hidden');
    }

    function openTopUpModal(id, namaLayanan) {
        document.getElementById('formTopUp').action = '/master/dompet-pusat/' + id + '/topup';
        document.getElementById('topup_nama_layanan').value = namaLayanan;
        document.getElementById('modalTopUp').classList.remove('hidden');
    }

    function openSetorDigitalModal(id, namaLayanan, saldo) {
        document.getElementById('formSetorDigital').action = '/master/dompet-pusat/' + id + '/setor-digital';
        document.getElementById('setordigital_nama_layanan').innerText = namaLayanan;
        document.getElementById('setordigital_saldo_teks').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(saldo);
        document.getElementById('setordigital_nominal').max = saldo;
        document.getElementById('setordigital_nominal').placeholder = 'Maks: ' + saldo;
        document.getElementById('modalSetorDigital').classList.remove('hidden');
    }

    function openHapusModal(id, namaDompet) {
        document.getElementById('formHapusDompet').action = '/master/dompet-pusat/' + id;
        document.getElementById('hapus_nama_dompet').innerText = namaDompet;
        document.getElementById('modalHapusDompet').classList.remove('hidden');
    }
</script>
@endpush
@endsection
