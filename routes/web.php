<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminGudangController;
use App\Http\Controllers\PosController;

Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        if ($role === 'owner') return redirect()->route('owner.dashboard');
        if ($role === 'admin_gudang') return redirect()->route('admin.dashboard');
        if ($role === 'kasir') return redirect()->route('kasir.dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard Owner
    Route::middleware('role:owner')->group(function () {
        Route::get('/owner/dashboard', [\App\Http\Controllers\OwnerController::class, 'index'])->name('owner.dashboard');
        
        // Laporan Eksekutif (Tahap 10)
        Route::get('/owner/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('owner.reports.index');
        Route::get('/owner/reports/chart-data', [\App\Http\Controllers\ReportController::class, 'getChartData'])->name('owner.reports.chart_data');
        
        // Import Historis Penjualan (Tahap 7)
        Route::get('/owner/historical-data', [\App\Http\Controllers\HistoricalDataController::class, 'index'])->name('owner.historical.index');
        Route::post('/owner/historical-data/manual', [\App\Http\Controllers\HistoricalDataController::class, 'storeManual'])->name('owner.historical.store_manual');
        Route::post('/owner/historical-data/excel', [\App\Http\Controllers\HistoricalDataController::class, 'uploadExcel'])->name('owner.historical.upload_excel');
        Route::get('/owner/historical-data/template', [\App\Http\Controllers\HistoricalDataController::class, 'downloadTemplate'])->name('owner.historical.download_template');
        
        // Export Laporan Eksekutif (Tahap 19)
        Route::get('/owner/reports/export/pdf', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('owner.reports.export_pdf');
        Route::get('/owner/reports/export/excel', [\App\Http\Controllers\ReportController::class, 'exportExcel'])->name('owner.reports.export_excel');

        Route::post('/owner/import', [\App\Http\Controllers\OwnerController::class, 'importExcel'])->name('owner.import');
        Route::post('/owner/prediksi', [\App\Http\Controllers\OwnerController::class, 'jalankanPrediksi'])->name('owner.prediksi');

        // Manajemen Akun Pegawai (Kasir & Admin Gudang)
        Route::resource('owner/users', \App\Http\Controllers\UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('owner/users/{id}/toggle-active', [\App\Http\Controllers\UserController::class, 'toggleActive'])->name('users.toggle_active');
        
        // Monitoring Aset Gudang
        Route::get('/owner/gudang', [\App\Http\Controllers\OwnerController::class, 'gudang'])->name('owner.gudang.index');
        Route::get('/owner/gudang/cabang/{id}/detail', [\App\Http\Controllers\OwnerController::class, 'detailCabang'])->name('owner.gudang.cabang.detail');
        Route::get('/owner/riwayat-apk', [\App\Http\Controllers\RiwayatApkController::class, 'index'])->name('owner.riwayat_apk');
        
        // Laporan Keuangan
        Route::get('owner/laporan', [\App\Http\Controllers\LaporanController::class, 'index'])->name('laporan.index');
        Route::get('owner/laporan/export/excel', [\App\Http\Controllers\LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('owner/laporan/export/pdf', [\App\Http\Controllers\LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');

        // Laporan Setoran
        Route::get('owner/laporan-setoran', [\App\Http\Controllers\LaporanSetoranController::class, 'index'])->name('owner.laporan_setoran.index');

        // Pusat Laporan & Historis (Konsolidasi Read-Only Owner)
        Route::prefix('owner/pusat-laporan')->name('owner.laporan_pusat.')->group(function () {
            Route::get('/', [\App\Http\Controllers\OwnerLaporanController::class, 'index'])->name('index');
            Route::get('/transaksi', [\App\Http\Controllers\OwnerLaporanController::class, 'transaksiKasir'])->name('transaksi');
            Route::get('/transaksi/{id}/struk', [\App\Http\Controllers\OwnerLaporanController::class, 'strukKasir'])->name('struk');
            Route::get('/pembelian', [\App\Http\Controllers\OwnerLaporanController::class, 'pembelianSupplier'])->name('pembelian');
            Route::get('/mutasi', [\App\Http\Controllers\OwnerLaporanController::class, 'mutasiStok'])->name('mutasi');
            Route::get('/request-operasional', [\App\Http\Controllers\OwnerLaporanController::class, 'requestOperasional'])->name('request_operasional');
            Route::get('/closing', [\App\Http\Controllers\OwnerLaporanController::class, 'closingCabang'])->name('closing');
            
            // Export Routes
            Route::get('/transaksi/export-pdf', [\App\Http\Controllers\OwnerLaporanController::class, 'transaksiPdf'])->name('transaksi.export_pdf');
            Route::get('/pembelian/export-pdf', [\App\Http\Controllers\PembelianController::class, 'exportPdf'])->name('pembelian.export_pdf');
            Route::get('/pembelian/export-excel', [\App\Http\Controllers\PembelianController::class, 'exportExcel'])->name('pembelian.export_excel');
            Route::get('/request-operasional/pdf', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'exportPdf'])->name('request_operasional.pdf');
            Route::get('/closing/pdf', [\App\Http\Controllers\AdminClosingController::class, 'exportPdf'])->name('closing.pdf');

            // New Reports
            Route::get('/setoran', [\App\Http\Controllers\OwnerLaporanController::class, 'riwayatSetoran'])->name('setoran');
            Route::get('/setoran/pdf', [\App\Http\Controllers\OwnerLaporanController::class, 'riwayatSetoranPdf'])->name('setoran.pdf');
            Route::get('/aset-stok', [\App\Http\Controllers\OwnerLaporanController::class, 'asetStok'])->name('aset_stok');
            Route::get('/aset-stok/pdf', [\App\Http\Controllers\OwnerLaporanController::class, 'asetStokPdf'])->name('aset_stok.pdf');
            Route::get('/keuangan-global', [\App\Http\Controllers\OwnerLaporanController::class, 'keuanganGlobal'])->name('keuangan_global');
            Route::get('/keuangan-global/pdf', [\App\Http\Controllers\OwnerLaporanController::class, 'keuanganGlobalPdf'])->name('keuangan_global.pdf');
        });

        // Master Data khusus Owner
        Route::resource('/master/aplikasi-layanan', \App\Http\Controllers\MasterAplikasiLayananController::class)
            ->names('master.aplikasi_layanan')
            ->except(['create', 'show', 'edit']);

        Route::post('/master/dompet-pusat/{id}/set-qris', [\App\Http\Controllers\PusatAplikasiLayananController::class, 'setQrisTujuan'])->name('master.dompet_pusat.set_qris');
        Route::post('/master/dompet-pusat/kas-pusat/init', [\App\Http\Controllers\PusatAplikasiLayananController::class, 'initKasPusat'])->name('master.dompet_pusat.init_kas');
        Route::post('/master/dompet-pusat/setor-owner', [\App\Http\Controllers\PusatAplikasiLayananController::class, 'setorOwner'])->name('master.dompet_pusat.setor_owner');
        Route::post('/master/dompet-pusat/{id}/topup', [\App\Http\Controllers\PusatAplikasiLayananController::class, 'topUp'])->name('master.dompet_pusat.topup');
        Route::post('/master/dompet-pusat/{id}/setor-digital', [\App\Http\Controllers\PusatAplikasiLayananController::class, 'setorDigitalOwner'])->name('master.dompet_pusat.setor_digital');
        Route::post('/master/dompet-pusat/setoran/{id}/batal', [\App\Http\Controllers\PusatAplikasiLayananController::class, 'batalSetoranOwner'])->name('master.dompet_pusat.setoran.batal');
        Route::post('/master/dompet-pusat/setoran/{id}/terima', [\App\Http\Controllers\PusatAplikasiLayananController::class, 'terimaSetoranAdmin'])->name('master.dompet_pusat.setoran.terima');
        Route::post('/master/dompet-pusat/setoran/{id}/tolak', [\App\Http\Controllers\PusatAplikasiLayananController::class, 'tolakSetoranAdmin'])->name('master.dompet_pusat.setoran.tolak');
        Route::resource('/master/dompet-pusat', \App\Http\Controllers\PusatAplikasiLayananController::class)
            ->names('master.dompet_pusat')
            ->except(['create', 'show', 'edit']);

        Route::get('/master/cabang', [\App\Http\Controllers\CabangController::class, 'index'])->name('master.cabang.index');
        Route::post('/master/cabang', [\App\Http\Controllers\CabangController::class, 'store'])->name('master.cabang.store');
        Route::put('/master/cabang/{id}', [\App\Http\Controllers\CabangController::class, 'update'])->name('master.cabang.update');
        Route::post('/master/cabang/{id}/toggle-active', [\App\Http\Controllers\CabangController::class, 'toggleActive'])->name('master.cabang.toggle_active');
    });

    // Fitur Bersama: Owner & Admin Gudang
    Route::middleware('role:owner,admin_gudang')->group(function () {
        // Alat Peramalan (SES Debugger)
        Route::get('/owner/ses-debugger', [\App\Http\Controllers\OwnerController::class, 'sesDebugger'])->name('owner.ses_debugger');
        Route::post('/owner/ses-debugger', [\App\Http\Controllers\OwnerController::class, 'sesDebugger']);
        Route::post('/owner/ses-debugger/export', [\App\Http\Controllers\OwnerController::class, 'exportDebuggerPdf'])->name('owner.ses_debugger.export');
        Route::get('owner/api/ews-settings', [\App\Http\Controllers\OwnerController::class, 'getEwsSettings'])->name('owner.api.ews_settings');
        Route::post('owner/update-batas-ews', [\App\Http\Controllers\OwnerController::class, 'updateBatasEws'])->name('owner.update_batas_ews');
    });

    // Manajemen Buffer EWS (Dynamic AI Buffer) - Dapat diakses oleh Owner & Admin Gudang
    Route::middleware('role:owner,admin_gudang')->group(function () {
        Route::get('/owner/ews', [\App\Http\Controllers\EwsController::class, 'index'])->name('owner.ews.index');
        Route::post('/owner/ews/recalculate', [\App\Http\Controllers\EwsController::class, 'runAutoCalculation'])->name('owner.ews.recalculate');
        Route::get('/owner/ews/export-pdf', [\App\Http\Controllers\EwsController::class, 'exportPdf'])->name('owner.ews.export_pdf');
    });

    // Dashboard Admin Gudang / Pusat
    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/admin/dashboard', [AdminGudangController::class, 'index'])->name('admin.dashboard');
        
        // Route Laporan Gudang (contoh)
        Route::get('/admin/laporan', [AdminGudangController::class, 'laporan'])->name('admin.laporan');

        // Modul Setoran Cabang (Closing)
        Route::get('/admin/setoran', [\App\Http\Controllers\SetoranPusatController::class, 'index'])->name('admin.setoran.index');
        Route::post('/admin/setoran/{id}/confirm', [\App\Http\Controllers\SetoranPusatController::class, 'confirm'])->name('admin.setoran.confirm');
        
        // Bantuan Pusat
        Route::get('/admin/bantuan-pusat', [\App\Http\Controllers\AdminBantuanPusatController::class, 'index'])->name('admin.bantuan_pusat.index');
        Route::post('/admin/bantuan-pusat/{id}/proses', [\App\Http\Controllers\AdminBantuanPusatController::class, 'proses'])->name('admin.bantuan_pusat.proses');
        
        // Fulfillment Request Operasional
        Route::get('/admin/fulfillment', [\App\Http\Controllers\AdminFulfillmentController::class, 'index'])->name('admin.fulfillment.index');
        Route::post('/admin/fulfillment/{id}/approve-digital', [\App\Http\Controllers\AdminFulfillmentController::class, 'approveDigital'])->name('admin.fulfillment.approve_digital');
        Route::post('/admin/fulfillment/{id}/approve-cash', [\App\Http\Controllers\AdminFulfillmentController::class, 'approveCash'])->name('admin.fulfillment.approve_cash');
        Route::post('/admin/fulfillment/{id}/approve-barang', [\App\Http\Controllers\AdminFulfillmentController::class, 'approveBarang'])->name('admin.fulfillment.approve_barang');
        Route::post('/admin/fulfillment/{id}/reject', [\App\Http\Controllers\AdminFulfillmentController::class, 'reject'])->name('admin.fulfillment.reject');
        
        // Monitoring Aset
        Route::get('/admin/monitoring', [\App\Http\Controllers\DashboardController::class, 'monitoring'])->name('admin.monitoring.index');

        // Admin Request Operasional
        Route::get('/admin/request-operasional/pdf', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'exportPdf'])->name('admin.request_operasional.pdf');
        Route::get('/admin/request-operasional', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'index'])->name('admin.request_operasional.index');
        Route::post('/admin/request-operasional/{id}/proses', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'proses'])->name('admin.request_operasional.proses');
        Route::post('/admin/request-operasional/{id}/bantuan', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'bantuan'])->name('admin.request_operasional.bantuan');
        Route::post('/admin/request-operasional/{id}/isi-saldo', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'prosesIsiSaldo'])->name('admin.request_operasional.isi_saldo');

        // Dompet Pusat (Admin View & Action)
        Route::get('/admin/dompet-pusat', [\App\Http\Controllers\AdminDompetPusatController::class, 'index'])->name('admin.dompet_pusat.index');
        Route::post('/admin/dompet-pusat/setor-kas', [\App\Http\Controllers\AdminDompetPusatController::class, 'setorKasPusat'])->name('admin.dompet_pusat.setor_kas');
        Route::post('/admin/dompet-pusat/setor-digital/{id}', [\App\Http\Controllers\AdminDompetPusatController::class, 'setorDigitalPusat'])->name('admin.dompet_pusat.setor_digital');
        Route::post('/admin/dompet-pusat/konfirmasi/{id}', [\App\Http\Controllers\AdminDompetPusatController::class, 'konfirmasiSetoranOwner'])->name('admin.dompet_pusat.konfirmasi');
        Route::post('/admin/dompet-pusat/tolak/{id}', [\App\Http\Controllers\AdminDompetPusatController::class, 'tolakSetoranOwner'])->name('admin.dompet_pusat.tolak');
        Route::post('/admin/dompet-pusat/batal/{id}', [\App\Http\Controllers\AdminDompetPusatController::class, 'batalSetoranAdmin'])->name('admin.dompet_pusat.batal');
        
        Route::post('/admin/request-operasional/{id}/bantuan-transaksi', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'prosesBantuanTransaksi'])->name('admin.request_operasional.bantuan_transaksi');
        Route::post('/admin/request-operasional/{id}/bantuan-transaksi-tolak', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'tolakBantuanTransaksi'])->name('admin.request_operasional.bantuan_transaksi_tolak');
        Route::post('/admin/request-operasional/{id}/restok-barang', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'prosesRestokBarang'])->name('admin.request_operasional.restok_barang');
        Route::post('/admin/request-operasional/{id}/restok-cash', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'prosesRestokCash'])->name('admin.request_operasional.restok_cash');
        Route::post('/admin/request-operasional/{id}/tolak', [\App\Http\Controllers\AdminRequestOperasionalController::class, 'tolakRequest'])->name('admin.request_operasional.tolak');
        
        // Admin Closing
        Route::get('/admin/closing/pdf', [\App\Http\Controllers\AdminClosingController::class, 'exportPdf'])->name('admin.closing.pdf');
        Route::get('/admin/closing', [\App\Http\Controllers\AdminClosingController::class, 'index'])->name('admin.closing.index');
        Route::get('/admin/closing/{id}', [\App\Http\Controllers\AdminClosingController::class, 'show'])->name('admin.closing.show');
        Route::post('/admin/closing/{id}/konfirmasi', [\App\Http\Controllers\AdminClosingController::class, 'konfirmasi'])->name('admin.closing.konfirmasi');

        Route::post('/admin/mutasi/store', [AdminGudangController::class, 'storeMutasi'])->name('admin.mutasi.store');
        Route::post('/admin/mutasi-uang-fisik/store', [AdminGudangController::class, 'storeMutasiCash'])->name('admin.mutasi_uang.store');
        Route::get('/admin/mutasi/print', [AdminGudangController::class, 'printMutasi'])->name('admin.mutasi.print');
        Route::post('/admin/mutasi/{id}/proses-draft', [AdminGudangController::class, 'prosesDraft'])->name('admin.mutasi.proses_draft');
        Route::delete('/admin/mutasi/{id}/hapus-draft', [AdminGudangController::class, 'hapusDraft'])->name('admin.mutasi.hapus_draft');
        
        Route::post('/admin/mutasi-uang-fisik/{id}/proses-draft', [AdminGudangController::class, 'prosesDraftCash'])->name('admin.mutasi_cash.proses_draft');
        Route::delete('/admin/mutasi-uang-fisik/{id}/hapus-draft', [AdminGudangController::class, 'hapusDraftCash'])->name('admin.mutasi_cash.hapus_draft');
        
        // Pengaturan Diskon Barang
        Route::post('/admin/master-barang/{id}/diskon', [\App\Http\Controllers\MasterBarangController::class, 'updateMaxDiskon'])->name('admin.master_barang.diskon');
        
        // Penerimaan / Pembelian Supplier
        Route::get('/admin/pembelian/historis', [\App\Http\Controllers\PembelianController::class, 'historis'])->name('admin.pembelian.historis');
        Route::get('/admin/pembelian/historis/export-pdf', [\App\Http\Controllers\PembelianController::class, 'exportPdf'])->name('admin.pembelian.export_pdf');
        Route::get('/admin/pembelian/historis/export-excel', [\App\Http\Controllers\PembelianController::class, 'exportExcel'])->name('admin.pembelian.export_excel');
        
        Route::get('/admin/pembelian', [\App\Http\Controllers\PembelianController::class, 'index'])->name('admin.pembelian.index');
        Route::get('/admin/pembelian/create', [\App\Http\Controllers\PembelianController::class, 'create'])->name('admin.pembelian.create');
        Route::post('/admin/pembelian/draft', [\App\Http\Controllers\PembelianController::class, 'createDraft'])->name('admin.pembelian.draft');
        Route::get('/admin/pembelian/{id}/edit', [\App\Http\Controllers\PembelianController::class, 'edit'])->name('admin.pembelian.edit');
        Route::put('/admin/pembelian/{id}', [\App\Http\Controllers\PembelianController::class, 'update'])->name('admin.pembelian.update');
        Route::post('/admin/pembelian/{id}/submit', [\App\Http\Controllers\PembelianController::class, 'submitDalamPerjalanan'])->name('admin.pembelian.submit');
        Route::post('/admin/pembelian/{id}/selesai', [\App\Http\Controllers\PembelianController::class, 'selesaikanPembelian'])->name('admin.pembelian.selesai');
        
        // Retur Barang
        Route::post('/admin/pembelian/{id}/retur', [\App\Http\Controllers\ReturController::class, 'ajukan'])->name('admin.retur.ajukan');
        Route::post('/admin/retur/{id}/selesai', [\App\Http\Controllers\ReturController::class, 'selesaikan'])->name('admin.retur.selesaikan');

        // Distribusi & Mutasi Monitoring
        Route::get('/distribusi-mutasi', [\App\Http\Controllers\DistribusiController::class, 'index'])->name('distribusi.mutasi.index');
        Route::get('/distribusi-mutasi/stats/{cabangId}', [\App\Http\Controllers\DistribusiController::class, 'getStats'])->name('distribusi.mutasi.stats');
        Route::get('/api/stok-asal/{cabang_id?}', [\App\Http\Controllers\DistribusiController::class, 'getStokAsal'])->name('api.stok_asal');
        Route::get('/api/saldo-kas/{cabang_id?}', [\App\Http\Controllers\DistribusiController::class, 'getSaldoKas'])->name('api.saldo_kas');
    });

    // Master Data (Bisa diakses Owner dan Admin Gudang)
    Route::middleware('role:owner,admin_gudang')->group(function () {
        // Redirect rute lama yang sudah dihapus agar tidak error 405 jika user merefresh browser
        Route::get('/master/dompet-pusat/historis', function() {
            if (auth()->user()->role === 'owner') return redirect()->route('owner.pusat_laporan.index');
            return redirect()->route('admin.dompet_pusat.index');
        });

        Route::get('/master/kategori', [\App\Http\Controllers\CategoryController::class, 'index'])->name('master.kategori.index');
        Route::post('/master/kategori', [\App\Http\Controllers\CategoryController::class, 'store'])->name('master.kategori.store');
        Route::delete('/master/kategori/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('master.kategori.destroy');
        
        Route::get('/master/barang', [\App\Http\Controllers\MasterBarangController::class, 'index'])->name('master.barang.index');
        Route::get('/master/barang/cetak', [\App\Http\Controllers\MasterBarangController::class, 'cetak'])->name('master.barang.cetak');
        Route::post('/master/barang', [\App\Http\Controllers\MasterBarangController::class, 'store'])->name('master.barang.store');
        Route::put('/master/barang/{id}', [\App\Http\Controllers\MasterBarangController::class, 'update'])->name('master.barang.update');
        Route::delete('/master/barang/{id}', [\App\Http\Controllers\MasterBarangController::class, 'destroy'])->name('master.barang.destroy');
        // Master Kategori
        Route::post('/master/kategori', [\App\Http\Controllers\MasterBarangController::class, 'storeKategori'])->name('master.kategori.store_induk');
        Route::put('/master/kategori/{id}', [\App\Http\Controllers\MasterBarangController::class, 'updateKategori'])->name('master.kategori.update');
        Route::delete('/master/kategori/{id}', [\App\Http\Controllers\MasterBarangController::class, 'destroyKategori'])->name('master.kategori.destroy');
    });

    // Dashboard Kasir (PWA)
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir/dashboard', [PosController::class, 'dashboard'])->name('kasir.dashboard');
        Route::get('/kasir/pos', [PosController::class, 'index'])->name('kasir.pos');
        // Transaksi
        Route::post('/kasir/transaksi', [\App\Http\Controllers\PosController::class, 'checkoutFisikDigitalA'])->name('kasir.transaksi.store');
        Route::post('/kasir/transaksi-digital-b', [\App\Http\Controllers\PosController::class, 'checkoutDigitalB'])->name('kasir.transaksi.digital_b');
        Route::post('/kasir/sync-transactions', [\App\Http\Controllers\PosController::class, 'syncOffline'])->name('kasir.transaksi.sync');
        Route::get('/kasir/struk/{id}', [PosController::class, 'showStrukQris'])->name('kasir.struk_qris');
        
        Route::post('/kasir/bantuan-pusat', [PosController::class, 'requestBantuanPusat'])->name('kasir.bantuan_pusat');
        Route::get('/kasir/bantuan-pusat/daftar', [PosController::class, 'daftarBantuanKasir'])->name('kasir.bantuan_pusat.daftar');
        Route::post('/kasir/bantuan-pusat/{id}/konfirmasi', [PosController::class, 'konfirmasiBantuanKasir'])->name('kasir.bantuan_pusat.konfirmasi');
        Route::delete('/kasir/bantuan-pusat/{id}/batal', [PosController::class, 'batalBantuanPusat'])->name('kasir.bantuan_pusat.batal');
        
        // Penerimaan Mutasi (Halaman Khusus Kasir)
        Route::get('/kasir/penerimaan', [\App\Http\Controllers\KasirPenerimaanController::class, 'index'])->name('kasir.penerimaan.index');
        Route::get('/kasir/penerimaan/barang/{id}', [\App\Http\Controllers\KasirPenerimaanController::class, 'terimaBarangView'])->name('kasir.penerimaan.barang');
        Route::post('/kasir/penerimaan/barang/{id}', [\App\Http\Controllers\KasirPenerimaanController::class, 'terimaBarangProcess'])->name('kasir.mutasi_barang.terima');
        Route::post('/kasir/penerimaan/cash/{id}', [\App\Http\Controllers\KasirPenerimaanController::class, 'terimaCashProcess'])->name('kasir.mutasi_cash.terima');
        
        // Konfirmasi Pengiriman (Asal) Mutasi oleh Kasir
        Route::post('/kasir/mutasi/barang/{id}/konfirmasi-asal', [\App\Http\Controllers\KasirPenerimaanController::class, 'konfirmasiAsalBarang'])->name('kasir.mutasi_barang.konfirmasi_asal');
        Route::post('/kasir/mutasi/barang/{id}/tolak-asal', [\App\Http\Controllers\KasirPenerimaanController::class, 'tolakAsalBarang'])->name('kasir.mutasi_barang.tolak_asal');
        Route::post('/kasir/mutasi/cash/{id}/konfirmasi-asal', [\App\Http\Controllers\KasirPenerimaanController::class, 'konfirmasiAsalCash'])->name('kasir.mutasi_cash.konfirmasi_asal');
        Route::post('/kasir/mutasi/cash/{id}/tolak-asal', [\App\Http\Controllers\KasirPenerimaanController::class, 'tolakAsalCash'])->name('kasir.mutasi_cash.tolak_asal');

        // EWS & SES Restok (Kasir)
        Route::get('/kasir/ews/detail/{barang_id}', [PosController::class, 'ewsDetail'])->name('kasir.ews.detail');
        Route::post('/kasir/ews/minta-bantuan', [PosController::class, 'ewsMintaBantuan'])->name('kasir.ews.minta_bantuan');

        // Request Operasional (Kasir)
        Route::get('/kasir/request-operasional/pdf', [\App\Http\Controllers\RequestOperasionalController::class, 'exportPdf'])->name('kasir.request_operasional.pdf');
        Route::get('/kasir/request-operasional', [\App\Http\Controllers\RequestOperasionalController::class, 'index'])->name('kasir.request_operasional.index');
        Route::post('/kasir/request-operasional', [\App\Http\Controllers\RequestOperasionalController::class, 'store'])->name('kasir.request_operasional.store');
        Route::post('/kasir/request-operasional/{id}/konfirmasi', [\App\Http\Controllers\RequestOperasionalController::class, 'konfirmasiKasir'])->name('kasir.request_operasional.konfirmasi');
        Route::delete('/kasir/request-operasional/{id}/batal', [\App\Http\Controllers\RequestOperasionalController::class, 'batalkanRequest'])->name('kasir.request_operasional.batal');
        
        // Blind Closing (Kasir)
        Route::get('/kasir/closing', [\App\Http\Controllers\ClosingController::class, 'index'])->name('kasir.closing.index');
        Route::post('/kasir/closing', [\App\Http\Controllers\ClosingController::class, 'store'])->name('kasir.closing.store');
        
        Route::get('/kasir/closing/riwayat-minus', [\App\Http\Controllers\ClosingController::class, 'riwayatMinus'])->name('kasir.closing.minus');
        Route::post('/kasir/closing/reimburse/{id}/{type}', [\App\Http\Controllers\ClosingController::class, 'reimburse'])->name('kasir.closing.reimburse');
    });

    // API Umum Statistik Perhitungan EWS Transparan (Owner, Admin Gudang, Kasir)
    Route::get('/api/ews/detail-stats', [\App\Http\Controllers\EwsController::class, 'apiDetailStats'])->name('api.ews.detail_stats');
});
