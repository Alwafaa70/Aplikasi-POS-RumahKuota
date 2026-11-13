{{-- MODAL DETAIL STATISTIK & PERHITUNGAN EWS DINAMIS (AI BUFFER) --}}
<div id="modalEwsStats" class="fixed inset-0 z-[999] overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    <!-- Backdrop Blur & Fade -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" onclick="closeEwsStatsModal()"></div>

    <div class="flex min-h-screen items-center justify-center p-3 sm:p-4 text-center">
        <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl border border-gray-100">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-slate-900 via-gray-900 to-indigo-950 p-6 text-white relative">
                <button type="button" onclick="closeEwsStatsModal()" class="absolute top-5 right-5 text-gray-400 hover:text-white bg-white/10 hover:bg-white/20 rounded-full w-9 h-9 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
                <div class="flex items-center gap-2 mb-2">
                    <span id="modalEwsLokasiBadge" class="bg-blue-500/20 text-blue-300 border border-blue-400/30 text-[11px] font-bold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot"></i> <span id="modalEwsLokasi">Gudang Pusat</span>
                    </span>
                    <span id="modalEwsKategori" class="bg-gray-700/60 text-gray-300 text-[11px] font-semibold px-2 py-0.5 rounded-full">Aksesoris</span>
                </div>
                <h3 id="modalEwsNamaBarang" class="text-xl sm:text-2xl font-black tracking-tight text-white leading-tight">Nama Produk</h3>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                    <span>Transparansi Algoritma Single Exponential Smoothing (SES)</span>
                    <span>•</span>
                    <span id="modalEwsTargetHari">Target: 30 Hari Restok</span>
                </p>
            </div>

            <!-- Loading State -->
            <div id="modalEwsLoading" class="p-12 text-center">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-blue-600 border-t-transparent mb-3"></div>
                <p class="text-sm font-bold text-gray-600">Menghubungkan ke Mesin Kalkulasi EWS AI...</p>
                <p class="text-xs text-gray-400 mt-1">Mengurai data historis penjualan & menghitung batas buffer</p>
            </div>

            <!-- Modal Content (Rendered dynamically) -->
            <div id="modalEwsContent" class="p-6 space-y-6 hidden max-h-[75vh] overflow-y-auto">

                <!-- 1. Status Indicator & Stok Highlights -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Stok Saat Ini -->
                    <div class="bg-slate-50 border border-slate-200/80 p-4 rounded-2xl text-center">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Stok Fisik Tersisa</span>
                        <div class="text-3xl font-black text-gray-900" id="modalEwsStok">0</div>
                        <span class="text-[11px] text-gray-500 font-medium">Unit di rak/gudang</span>
                    </div>

                    <!-- Batas Kritis EWS -->
                    <div class="bg-red-50 border border-red-200/80 p-4 rounded-2xl text-center">
                        <span class="text-[11px] font-bold text-red-500 uppercase tracking-wider block mb-1">Batas Kritis EWS</span>
                        <div class="text-3xl font-black text-red-600" id="modalEwsBatas">0</div>
                        <span class="text-[11px] text-red-500 font-medium">Kebutuhan 7 Hari (Lead Time)</span>
                    </div>

                    <!-- Rekomendasi Restok -->
                    <div class="bg-emerald-50 border border-emerald-200/80 p-4 rounded-2xl text-center">
                        <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block mb-1">Rekomendasi Restok</span>
                        <div class="text-3xl font-black text-emerald-700" id="modalEwsRestok">+0</div>
                        <span class="text-[11px] text-emerald-600 font-medium" id="modalEwsRestokLabel">Unit untuk 30 hari</span>
                    </div>
                </div>

                <!-- Alert Status Kritis atau Aman -->
                <div id="modalEwsAlertBox" class="p-4 rounded-2xl flex items-start gap-3.5 border">
                    <div id="modalEwsAlertIcon" class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-base"></div>
                    <div>
                        <h4 id="modalEwsAlertTitle" class="text-sm font-black"></h4>
                        <p id="modalEwsAlertDesc" class="text-xs mt-0.5 leading-relaxed"></p>
                    </div>
                </div>

                <!-- 2. Analisis FSN (Fast, Medium, Slow) & Safety Stock -->
                <div class="bg-gradient-to-br from-indigo-50/70 to-blue-50/50 border border-indigo-100 rounded-2xl p-5 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-indigo-600"></i>
                            <span class="text-xs font-black uppercase tracking-wider text-indigo-950">Klasifikasi Kecepatan (FSN Analysis)</span>
                        </div>
                        <div id="modalEwsFsnBadge" class="text-xs font-black px-3 py-1 rounded-full border shadow-2xs">
                            Fast-Moving
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="bg-white/80 p-3 rounded-xl border border-indigo-100/60">
                            <span class="text-[11px] text-gray-500 block">Tren Penjualan Harian (AI):</span>
                            <span class="text-base font-black text-gray-800" id="modalEwsDaily">0.00 Unit / Hari</span>
                        </div>
                        <div class="bg-white/80 p-3 rounded-xl border border-indigo-100/60">
                            <span class="text-[11px] text-gray-500 block">Safety Stock Dinamis:</span>
                            <span class="text-base font-black text-indigo-700" id="modalEwsSafety">20% (+0 Unit)</span>
                        </div>
                    </div>

                    <!-- Alasan Ilmiah Buffer -->
                    <div class="bg-white p-3.5 rounded-xl border border-indigo-100 shadow-2xs text-xs text-gray-700 leading-relaxed">
                        <span class="font-bold text-indigo-950 block mb-1">📖 Alasan Ilmiah Pemberian Persentase Buffer:</span>
                        <p id="modalEwsAlasan" class="text-gray-600"></p>
                    </div>
                </div>

                <!-- 3. Rincian Bedah Rumus Transparan -->
                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-calculator text-blue-600"></i>
                        <span>Audit Perhitungan Matematika Transparan</span>
                    </h4>

                    <!-- Rumus Batas EWS -->
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200/80 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-red-700">1. Rumus Batas EWS (Lead Time Logistik)</span>
                            <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded">Lead Time: 7 Hari</span>
                        </div>
                        <div class="font-mono text-xs font-bold text-gray-800 bg-white p-2.5 rounded-xl border border-gray-200" id="modalEwsRumusBatas">
                            Batas = Tren Harian × 7 Hari
                        </div>
                        <p class="text-[11px] text-gray-500 leading-relaxed">
                            <b>Kenapa 7 Hari?</b> 7 hari adalah estimasi siklus pengadaan (approval PO, packing supplier, pengiriman kurir, hingga barang masuk rak). Jika stok &le; kebutuhan 7 hari, peringatan harus segera berbunyi agar toko tidak keburu kosong total.
                        </p>
                    </div>

                    <!-- Rumus Rekomendasi Restok -->
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200/80 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-700">2. Rumus Rekomendasi Jumlah Restok</span>
                            <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded">Target: Maks 30 Hari</span>
                        </div>
                        <div class="font-mono text-xs font-bold text-gray-800 bg-white p-2.5 rounded-xl border border-gray-200" id="modalEwsRumusRestok">
                            Restok = (Kebutuhan Hari + Safety Stock) - Sisa Stok
                        </div>
                        <p class="text-[11px] text-gray-500 leading-relaxed">
                            <b>Kenapa Maksimal 30 Hari?</b> Peramalan SES dioptimalkan khusus untuk 1 bulan operasional. Meramal lebih dari 30 hari (seperti 60-90 hari) meningkatkan galat prediksi dan berisiko membekukan uang kas toko pada barang yang belum tentu habis (*dead capital*).
                        </p>
                    </div>
                </div>

            </div>

            <!-- Modal Footer & Action -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-[11px] text-gray-400 text-center sm:text-left">
                    Algoritma: Single Exponential Smoothing (SES) Auto-Alpha • Standar FSN Inventory
                </div>
                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" onclick="closeEwsStatsModal()" class="w-full sm:w-auto px-4 py-2 bg-white hover:bg-gray-100 border border-gray-300 text-gray-700 rounded-xl font-bold text-xs transition">
                        Tutup
                    </button>
                    <a id="modalEwsLabBtn" href="#" target="_blank" class="w-full sm:w-auto px-4 py-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 rounded-xl font-bold text-xs transition inline-flex items-center justify-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-flask"></i>
                        <span>Uji di Lab SES</span>
                    </a>
                    <a id="modalEwsActionBtn" href="#" class="w-full sm:w-auto px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs transition shadow-sm inline-flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span id="modalEwsActionLabel">Proses Restok</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
window.openEwsStatsModal = async function(barangId, cabangId = null, targetHari = 30) {
    const modal = document.getElementById('modalEwsStats');
    const loading = document.getElementById('modalEwsLoading');
    const content = document.getElementById('modalEwsContent');
    
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.style.display = 'block';
    loading.classList.remove('hidden');
    content.classList.add('hidden');

    try {
        let url = `/api/ews/detail-stats?barang_id=${barangId}&target_hari=${targetHari}`;
        if (cabangId !== null && cabangId !== '' && cabangId !== 0 && cabangId !== '0') {
            url += `&cabang_id=${cabangId}`;
        }

        const response = await fetch(url);
        const result = await response.json();

        if (!result.success || !result.data) {
            alert('Data statistik EWS tidak ditemukan.');
            closeEwsStatsModal();
            return;
        }

        const d = result.data;

        // Header
        document.getElementById('modalEwsNamaBarang').innerText = d.nama_barang;
        document.getElementById('modalEwsKategori').innerText = d.kategori + ' • ' + d.tipe_barang;
        document.getElementById('modalEwsLokasi').innerText = d.lokasi_nama;
        document.getElementById('modalEwsTargetHari').innerText = `Target Restok: ${d.target_hari} Hari Kedepan`;

        // Metrics
        document.getElementById('modalEwsStok').innerText = d.stok_saat_ini + ' Pcs';
        document.getElementById('modalEwsBatas').innerText = d.batas_ews + ' Pcs';
        document.getElementById('modalEwsRestok').innerText = d.rekomendasi_restok > 0 ? '+ ' + d.rekomendasi_restok + ' Pcs' : '0 Pcs';
        document.getElementById('modalEwsRestokLabel').innerText = d.rekomendasi_restok > 0 ? `Perlu ditambah untuk ${d.target_hari} hari` : 'Stok sudah mencukupi target';

        // Alert Box
        const alertBox = document.getElementById('modalEwsAlertBox');
        const alertIcon = document.getElementById('modalEwsAlertIcon');
        const alertTitle = document.getElementById('modalEwsAlertTitle');
        const alertDesc = document.getElementById('modalEwsAlertDesc');

        if (d.is_kritis) {
            alertBox.className = 'p-4 rounded-2xl flex items-start gap-3.5 border bg-red-50/90 border-red-200 text-red-900';
            alertIcon.className = 'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-base bg-red-100 text-red-600';
            alertIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
            alertTitle.innerText = 'STATUS KRITIS: Stok Telah Menyentuh / Berada Di Bawah Batas EWS!';
            alertDesc.innerText = `Sisa stok saat ini (${d.stok_saat_ini} pcs) tidak aman karena kurang dari atau sama dengan kebutuhan lead time 7 hari (${d.batas_ews} pcs). Segera lakukan tindakan restok sebanyak ${d.rekomendasi_restok} pcs agar tidak kehabisan stok total (stockout).`;
        } else {
            alertBox.className = 'p-4 rounded-2xl flex items-start gap-3.5 border bg-emerald-50/90 border-emerald-200 text-emerald-900';
            alertIcon.className = 'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-base bg-emerald-100 text-emerald-600';
            alertIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
            alertTitle.innerText = 'STATUS AMAN: Stok Masih Mencukupi Batas EWS & Buffer';
            alertDesc.innerText = `Sisa stok (${d.stok_saat_ini} pcs) masih berada di atas ambang batas kritis (${d.batas_ews} pcs). Belum ada ancaman darurat kehabisan stok dalam waktu dekat.`;
        }

        // FSN Analysis
        const fsnBadge = document.getElementById('modalEwsFsnBadge');
        if (d.klasifikasi_badge === 'fast') {
            fsnBadge.className = 'text-xs font-black px-3 py-1 rounded-full border bg-purple-100 text-purple-800 border-purple-200';
            fsnBadge.innerText = '🚀 Fast-Moving (Sangat Laris)';
        } else if (d.klasifikasi_badge === 'medium') {
            fsnBadge.className = 'text-xs font-black px-3 py-1 rounded-full border bg-blue-100 text-blue-800 border-blue-200';
            fsnBadge.innerText = '⚖️ Medium-Moving (Stabil)';
        } else {
            fsnBadge.className = 'text-xs font-black px-3 py-1 rounded-full border bg-gray-100 text-gray-700 border-gray-300';
            fsnBadge.innerText = '🐢 Slow-Moving (Kurang Laris)';
        }

        document.getElementById('modalEwsDaily').innerText = parseFloat(d.daily_forecast).toFixed(2) + ' Unit / Hari';
        document.getElementById('modalEwsSafety').innerText = d.safety_percent + '% (+' + d.safety_stock_units + ' Unit)';
        document.getElementById('modalEwsAlasan').innerText = d.alasan_ilmiah;

        // Rumus
        document.getElementById('modalEwsRumusBatas').innerText = d.rumus_batas;
        document.getElementById('modalEwsRumusRestok').innerText = d.rumus_restok;

        // Action Button
        const actionBtn = document.getElementById('modalEwsActionBtn');
        const actionLabel = document.getElementById('modalEwsActionLabel');
        if (d.cabang_id) {
            actionBtn.href = `/distribusi-mutasi?tab=mutasi&cabang_tujuan=${d.cabang_id}&barang_id=${d.barang_id}&qty=${d.rekomendasi_restok}`;
            actionLabel.innerText = `Kirim Mutasi (${d.rekomendasi_restok} Pcs)`;
            actionBtn.className = 'w-full sm:w-auto px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition shadow-sm inline-flex items-center justify-center gap-1.5';
        } else {
            actionBtn.href = `/admin/pembelian/create?barang_id=${d.barang_id}&qty=${d.rekomendasi_restok}`;
            actionLabel.innerText = `Beli ke Supplier (${d.rekomendasi_restok} Pcs)`;
            actionBtn.className = 'w-full sm:w-auto px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition shadow-sm inline-flex items-center justify-center gap-1.5';
        }

        // Link ke Laboratorium SES
        const labBtn = document.getElementById('modalEwsLabBtn');
        if (labBtn) {
            const cid = (d.cabang_id !== null && d.cabang_id !== undefined && d.cabang_id !== '') ? d.cabang_id : 0;
            labBtn.href = `/owner/ses-debugger?barang_id=${d.barang_id}&cabang_id=${cid}`;
        }

        loading.classList.add('hidden');
        content.classList.remove('hidden');

    } catch (err) {
        console.error('Error fetching EWS stats:', err);
        alert('Gagal memuat statistik EWS.');
        closeEwsStatsModal();
    }
};

window.closeEwsStatsModal = function() {
    const modal = document.getElementById('modalEwsStats');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
};
</script>
