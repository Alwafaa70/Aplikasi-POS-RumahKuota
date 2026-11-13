# 🗺️ ROADMAP STRATEGI PENGEMBANGAN SISTEM POS
## Dari Tugas Akhir Menuju Produk Bisnis Multi-Tenant SaaS
*Dokumen Cetak Biru (Blueprint) Strategi Produk, Arsitektur Sistem, dan Panduan Pengembangan Masa Depan*

---

> **CATATAN UNTUK PENGGUNA (MAS RIDAN / ALWAFAA)**:  
> Simpan berkas ini baik-baik. Ketika Anda nanti ingin melanjutkan pengembangan ke tahap bisnis atau ingin berdiskusi kembali dengan AI asisten, Anda cukup menyalin isi dokumen ini atau memberi tahu AI:  
> *"Lanjutkan pengembangan aplikasi POS saya berdasarkan panduan di berkas ROADMAP_PENGEMBANGAN_BISNIS.md pada Fase [sebutkan fase yang dituju]"*.

---

## 📌 BAGIAN 1: STATUS TERKINI & NILAI JUAL DI CV / PORTOFOLIO

### 1.1 Status Arsitektur Saat Ini
* **Model Arsitektur**: *Single-Tenant Enterprise POS* (Dirancang untuk studi kasus operasional *Rumah Kuota* dengan 9 cabang dan 1 gudang pusat).
* **Repositori GitHub**: [https://github.com/Alwafaa70/Aplikasi-POS-RumahKuota](https://github.com/Alwafaa70/Aplikasi-POS-RumahKuota)
* **Kondisi Akun**: Akun role `owner` saat ini bertindak sebagai *co-owner* dari satu ekosistem perusahaan yang sama karena tabel-tabel utama (`cabang`, `master_barang`, `transaksi`, `kas_pusat`) belum disekat oleh `perusahaan_id`.

### 1.2 Tiga Nilai Unggulan untuk CV & Rekruter Perusahaan
Repositori ini saat ini sudah berada di level **Top 5% Portfolio Developer (Tingkat Fresh Graduate hingga Mid-Level)** berkat 3 pilar:
1. **Intelligence & Forecasting**: Peramalan deret waktu *Single Exponential Smoothing (SES)* dengan optimasi otomatis *Grid Search Alpha* (MAD minimum) dan tolok ukur akurasi ilmiah *C.D. Lewis (1982)*.
2. **Supply Chain Dynamic Buffer EWS**: Sistem deteksi dini stok kritis otomatis berbasis formula $\text{Batas EWS} = \text{Tren Harian} \times \text{Lead Time (7 Hari)}$ dan persentase cadangan adaptif *FSN Analysis* (Fast 30%, Medium 20%, Slow 10%).
3. **PWA Offline-First Resilience**: Layar kasir tetap dapat bertransaksi saat internet padam melalui *Service Worker*, *Cache Storage*, dan *client-side IndexedDB (W3C)* yang otomatis tersinkronisasi saat jaringan pulih.
4. **Dokumentasi Bilingual**: README profesional dwibahasa (*English & Bahasa Indonesia*) berstandar industri internasional.

---

## 🚀 BAGIAN 2: FASE 1 (PRIORITAS UTAMA) — UJI COBA LAPANGAN (PILOT TESTING)

### 2.1 Mengapa Fokus ke Uji Coba 1–2 Cabang Dulu?
Perusahaan mitra Anda (*Rumah Kuota*) bersedia melakukan pengujian operasional nyata di 1–2 cabang. Ini adalah **momen emas** yang wajib dimaksimalkan sebelum mengubah arsitektur kode:
* **Bukti Nyata di CV**: Anda akan bisa menulis pencapaian tingkat tinggi di portofolio:
  > *"Successfully piloted and maintained a web-based PWA POS system across 2 operational retail stores, handling daily offline/online sales and automating stockout prevention alerts."*
* **Mencegah Kerusakan Kode (*Prevent Regression Bug*)**: Menjaga sistem yang sudah stabil dan lolos sidang agar tidak rusak sebelum diuji coba oleh kasir nyata.

### 2.2 Checklist Evaluasi Uji Coba di Lapangan
Selama uji coba di 1–2 cabang, pantau dan catat poin-poin berikut:
- [ ] **Kelancaran Kasir**: Apakah antarmuka kasir mudah dipahami oleh staf kasir tanpa kebingungan?
- [ ] **Keandalan Offline PWA**: Coba matikan Wi-Fi/koneksi saat transaksi kasir berjalan. Pastikan transaksi tersimpan di *IndexedDB* dan berhasil sinkron saat internet kembali menyala.
- [ ] **Akurasi Alarm EWS**: Apakah sinyal stok `KRITIS` berbunyi tepat waktu saat barang menipis mendekati siklus 7 hari?
- [ ] **Audit Kas Blind Closing**: Apakah fitur tutup kas buta berhasil mendeteksi selisih fisik uang kasir secara akurat?
- [ ] **Cetak Struk**: Apakah format struk belanja sesuai dengan printer thermal yang ada di gerai cabang?

### 2.3 Aturan Git Selama Fase 1
* **Pertahankan Branch `main`**: Branch `main` tetap menjadi rujukan versi stabil Rumah Kuota.
* **Commit Rutin Hasil Perbaikan Lapangan**: Setiap kali ada penyesuaian kecil saat uji coba (misal: perbaikan cetak struk, penyesuaian tata letak tombol, perbaikan teks), langsung lakukan commit ke `main` dengan pesan jelas:
  - `fix: penyesuaian format cetak struk printer thermal 58mm`
  - `perf: optimasi waktu sinkronisasi IndexedDB saat offline`
* *Manfaat*: Riwayat GitHub Anda akan terus aktif dengan commit berkualitas tinggi yang membuktikan proyek ini dirawat di dunia nyata (*real-world maintenance*).

---

## 💼 BAGIAN 3: FASE 2 (MASA DEPAN) — TRANSFORMASI MENJADI PRODUK SAAS MULTI-TENANT

Setelah uji coba di 1–2 cabang sukses dan stabil, sistem siap ditingkatkan menjadi platform bisnis perangkat lunak (*Software as a Service / SaaS*) di mana siapa pun pemilik toko di luar sana bisa mendaftar dan memakai sistem ini.

### 3.1 Konsep Multi-Tenancy (Owner Memiliki Perusahaan Sendiri)
* **1 Owner = 1 Perusahaan / Tenant**: Setiap Owner yang mendaftar memiliki data perusahaan terpisah total. Owner A tidak bisa mengintip cabang, barang, keuangan, dan kasir milik Owner B.
* **Kasir & Admin = Karyawan**: Karyawan **TIDAK BISA** mendaftar sendiri dari luar. Akun kasir dan admin dibuatkan oleh Owner dari dalam menu Manajemen Pegawai.
* **Halaman Login Tetap 1 Pintu**: Tetap menggunakan satu form login tunggal (Username/Email + Password). Sistem di backend yang otomatis mengarahkan ke dashboard yang sesuai berdasarkan peran (*role*).

### 3.2 Alur Registrasi Owner Baru
Pada halaman login, ditambahkan tombol: *"Daftar Akun Toko Baru (Register)"*.
1. Calon Owner mengisi: Nama Pemilik, Nama Toko/Perusahaan (misal: *Berkah Kuota Cell*), Email/Username, dan Password.
2. Sistem otomatis membuat:
   - Data entitas baru di tabel `perusahaans`.
   - Data pengguna baru di tabel `users` dengan peran `owner` yang terhubung ke `perusahaan_id`.
   - Data 1 cabang default (*Cabang Utama*).
3. Owner langsung diarahkan masuk ke dashboard tokonya sendiri.

### 3.3 Konsep Skema Paket (Free vs Pro / Berlangganan)
*Di masa depan, jika Anda ingin memonetisasi aplikasi ini, gunakan konsep pembatasan berbasis fitur:*
* **Paket Gratis (Free / Trial)**:
  - Maksimal 1 Cabang Toko.
  - Maksimal 1 Akun Kasir.
  - Fitur POS dasar dan pencatatan transaksi manual.
* **Paket Berlangganan (Pro / Business)**:
  - Bebas membuat banyak cabang (*Unlimited Branches*).
  - Bebas membuat banyak akun kasir dan admin gudang.
  - Akses fitur kecerdasan buatan: **Laboratorium Peramalan SES** dan **Dynamic Buffer Early Warning System (EWS)** otomatis.
  - Ekspor laporan audit keuangan dan PDF resmi.

### 3.4 Rencana Teknis Arsitektur Basis Data (Database Migration)
Untuk mengubah sistem menjadi multi-tenant tanpa merusak kode lama:
1. Buat tabel baru `perusahaans`:
   - `id`, `nama_perusahaan`, `owner_id`, `paket_langganan`, `is_active`, `timestamps`.
2. Tambahkan kolom `perusahaan_id` (Foreign Key ber-index) pada:
   - `users`, `cabang`, `master_barang`, `transaksi`, `kas_pusat`, `pengaturan_ews`, `mutasi_stok`, dll.
3. Tetapkan data lama (*Rumah Kuota*) menjadi `perusahaan_id = 1` agar data uji coba tidak hilang.
4. Pasang mekanisme **Laravel Global Scope**:
   Setiap kali model dipanggil, sistem otomatis menyaring data:  
   `where('perusahaan_id', Auth::user()->perusahaan_id)`.

---

## 📸 BAGIAN 4: PENYEMPURNA PORTOFOLIO GITHUB (OPSIONAL, BISA DILAKUKAN KAPAN SAJA)

Untuk membuat halaman GitHub Anda memukau para perekrut kerja luar negeri:
1. Ambil 3–4 tangkapan layar (*screenshots*) dari aplikasi:
   - Tampilan Dashboard Utama & Monitor EWS.
   - Tampilan Grafik Kurva Peramalan SES di Laboratorium Debugger.
   - Tampilan Antarmuka Kasir POS.
   - Tampilan Modal Transparansi Rumus (Stats AI).
2. Simpan gambar-gambar tersebut ke dalam folder `docs/screenshots/` di dalam proyek.
3. Sematkan ke dalam `README.md` menggunakan tag markdown:
   `![Dashboard EWS](docs/screenshots/dashboard_ews.png)`

---

## 💬 CARA CEPAT MELANJUTKAN SESI DI MASA DEPAN

Bila Anda ingin memulai pengembangan fitur baru bersama AI di masa depan, Anda cukup menyalin pesan singkat ini:

> *"Halo, saya ingin melanjutkan pengembangan aplikasi POS Multi-Cabang saya berdasarkan dokumen panduan `ROADMAP_PENGEMBANGAN_BISNIS.md`. Saya sekarang ingin fokus mengerjakan [PILIH SALAH SATU: (A) Evaluasi & perbaikan bug hasil uji coba lapangan 1-2 cabang / (B) Mulai membuat tabel perusahaans & registrasi Owner baru di Fase 2]. Mohon bantu saya langkah demi langkah secara bertahap."*

---
*Dokumen ini dibuat secara resmi pada tanggal 13 November 2026 sebagai panduan roadmap strategis pengembangan proyek Aplikasi POS Rumah Kuota.*
