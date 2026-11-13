# 🛒 Rumah Kuota POS — Sistem Informasi Ritel Multi-Cabang & Smart Inventory Management

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11" />
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js" />
  <img src="https://img.shields.io/badge/PWA-Offline--First-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white" alt="PWA Offline First" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License MIT" />
</p>

---

## 📌 Ringkasan Eksekutif (Executive Summary)

**Rumah Kuota POS** adalah platform sistem informasi manajemen ritel dan kasir (*Point of Sale*) multi-cabang terpadu yang memadukan operasional kasir lini depan dengan sistem **Pengendalian Persediaan Cerdas (*Smart Inventory Control*)**.

Aplikasi ini dirancang untuk menyelesaikan dua permasalahan fundamental pada bisnis ritel aksesoris dan produk telekomunikasi multi-lokasi:
1. **Kehabisan Stok (*Stockout*)**: Mengakibatkan hilangnya potensi omset penjualan (*lost sales*) pada barang-barang yang sangat laris (*fast-moving*).
2. **Penumpukan Stok Berlebih (*Overstock*)**: Mengakibatkan pembekuan modal kerja toko (*dead capital*) dan risiko voucher fisik kedaluwarsa pada barang yang lambat laku (*slow-moving*).

Solusi yang diterapkan adalah penggabungan peramalan deret waktu **Single Exponential Smoothing (SES)** dengan sistem alarm persediaan **Dynamic Buffer Early Warning System (EWS)** berbasis **Analisis FSN (*Fast, Medium, Slow-Moving*)** dan **Siklus Pengadaan 7 Hari (*Lead Time*)**.

---

## 🚀 Fitur Unggulan (Core Features)

### 🏪 1. Multi-Branch & Central Warehouse Management
- **Struktur Hierarki Logistik**: Koordinasi antara **Gudang Pusat** (pengadaan skala besar dari supplier) dengan **Toko Cabang** (gerai penjualan lini depan).
- **Mutasi Stok Antar-Cabang**: Pengiriman dan transfer barang antar-cabang dengan mekanisme verifikasi persetujuan (*two-way confirmation approval*).
- **Purchase Order (PO) Supplier**: Manajemen pemesanan barang ke pemasok terintegrasi dengan pencatatan barang masuk (*inbound tracking*).

### ⚡ 2. Offline-First Progressive Web App (PWA)
- **Operasional Tanpa Hambatan Internet**: Kasir tetap dapat melakukan transaksi penjualan saat koneksi internet terputus berkat implementasi **Service Worker**, **Cache Storage API**, dan **IndexedDB (W3C)** di sisi klien.
- **Background Synchronization**: Data transaksi offline secara otomatis disinkronisasikan ke peladen pusat saat koneksi internet pulih tanpa risiko duplikasi data.

### 📈 3. Laboratorium Riset Single Exponential Smoothing (SES)
- **Model Matematis Adaptif**: Menghitung proyeksi kebutuhan stok periode mendatang dengan meredam fluktuasi permintaan masa lalu:
  $$F_{t+1} = \alpha Y_t + (1 - \alpha) F_t$$
- **Optimasi Parameter Otomatis (*Grid Search*)**: Sistem secara otomatis menguji 9 variasi konstanta pemulusan $\alpha \in \{0.1, 0.2, \dots, 0.9\}$ dan memilih nilai $\alpha$ terbaik berdasarkan deviasi kesalahan terendah (*Mean Absolute Deviation* / MAD).
- **Standar Evaluasi Akurasi Internasional (C.D. Lewis, 1982)**: Evaluasi galat *Mean Absolute Percentage Error* (MAPE) diklasifikasikan secara transparan:
  - $\text{MAPE} < 10\%$: **Sangat Akurat (*Highly Accurate*)**
  - $10\% \le \text{MAPE} \le 20\%$: **Baik / Akurat (*Good Forecasting*)**
  - $20\% < \text{MAPE} \le 50\%$: **Layak / Cukup (*Reasonable Forecasting*)**
  - $\text{MAPE} > 50\%$: **Kurang Akurat (*Inaccurate*)**
- **Ketahanan Terhadap Hari Libur / Zero-Sales**: Secara matematis peramalan mengalami peredaman eksponensial halus (*smooth exponential decay*) saat toko libur tanpa terjadi *crash* pembagian dengan nol.

### 🛡️ 4. Dynamic Buffer Early Warning System (EWS)
- **Bukan Buffer Statis**: Berbeda dengan sistem konvensional yang mematok angka batas pengaman tetap sama rata, sistem ini menghitung **Batas Kritis EWS secara dinamis**:
  $$\text{Batas Kritis EWS} = \text{Laju Tren Harian (SES)} \times \text{Lead Time Pengadaan (7 Hari)}$$
- **Cadangan Adaptif Berbasis Analisis FSN**:
  - **Fast-Moving**: Penjualan tinggi $\rightarrow$ Buffer pengaman **30%** untuk menahan lonjakan pembeli mendadak.
  - **Medium-Moving**: Penjualan stabil $\rightarrow$ Buffer pengaman **20%** proporsional standar ritel.
  - **Slow-Moving**: Penjualan lambat $\rightarrow$ Buffer ditekan **10%** agar dana modal tidak tertimbun.
- **Rekomendasi Restok Presisi**:
  $$\text{Rekomendasi Restok} = \max\Big(0, \big(\text{Prediksi Kebutuhan Siklus} + \text{Safety Stock}\big) - \text{Sisa Stok Fisik}\Big)$$
- **Aksi Cepat Restok (*Quick Action Deep-Link*)**: Tombol tindakan sekali klik langsung mengarahkan ke form PO Supplier (untuk Gudang Pusat) atau Kirim Mutasi Stok (untuk Cabang) dengan jumlah barang yang telah terisi otomatis.

### 💰 5. Pengendalian Keuangan & Blind Closing
- **Kas Multi-Akun**: Pemisahan pencatatan kas tunai laci (*cash drawer*), rekening bank, serta saldo aplikasi layanan pihak ketiga (Digipos, dsb.).
- **Pengendalian Kas Internal (*Blind Closing*)**: Kasir wajib menghitung dan menginput fisik uang tunai tanpa melihat saldo kalkulasi sistem untuk mencegah manipulasi data kasir (*skimming*). Selisih kas terdeteksi secara otomatis.
- **Laporan Laba Rugi Riil & Proyeksi Laba**: Analisis profitabilitas berbasis Harga Pokok Penjualan (HPP) per transaksi.

---

## 🏗️ Arsitektur Teknologi (Tech Stack)

| Lapisan | Teknologi | Deskripsi |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 11.x | PHP 8.2+ MVC Framework, Eloquent ORM, RESTful API |
| **Frontend UI** | Blade + Tailwind CSS 3.x | Desain antarmuka responsif, modern, dan ringan |
| **Interaktivitas UI** | Alpine.js 3.x | Manajemen state reaktif di sisi antarmuka pengguna |
| **Visualisasi Data** | ApexCharts.js | Grafik kurva *Goodness of Fit* data aktual vs prediksi SES |
| **Basis Data Klien** | IndexedDB & Cache Storage | Penyimpanan transaksi offline pada peramban kasir (PWA) |
| **Basis Data Peladen** | MySQL / SQLite | Penyimpanan data relasional terpusat dengan indexing optimal |
| **Mesin Dokumen** | DomPDF (`barryvdh/laravel-dompdf`) | Ekspor laporan kalkulasi SES dan rekapitulasi buffer EWS ke format PDF |
| **Ikonografi & Font** | FontAwesome 6 & Google Outfit | Tipografi dan ikon visual modern |

---

## 📂 Struktur Direktori Utama

```text
aplikasi-pos/
├── app/
│   ├── Http/Controllers/        # Pengendali alur logika (POS, Owner, EWS, Admin, Mutasi, Kas)
│   ├── Models/                 # Model data Eloquent (Barang, Cabang, Transaksi, Pengaturan EWS, dll.)
│   └── Services/               # Layanan logika bisnis inti:
│       ├── SesForecastingService.php   # Algoritma SES, Grid Search Alpha, MAD & MAPE
│       └── EwsService.php              # Logika Ambang Batas EWS & Klasifikasi FSN
├── database/
│   ├── migrations/             # Definisi skema tabel basis data
│   └── seeders/                # Data sampel transaksi riil 6 bulan & profil toko
├── public/
│   ├── service-worker.js       # Worker PWA untuk offline caching & background sync
│   └── manifest.json           # Manifest instalasi aplikasi PWA
├── resources/
│   └── views/                  # Tampilan antarmuka Blade:
│       ├── owner/              # Dashboard Owner, Laboratorium SES Debugger, Monitor EWS
│       ├── admin/              # Dashboard Admin Gudang Pusat, PO Pembelian Supplier
│       ├── kasir/              # Antarmuka Kasir POS & Blind Closing
│       └── distribusi/         # Modul Mutasi Logistik Antar-Cabang
└── routes/
    └── web.php                 # Rute navigasi dan otorisasi berbasis peran (Role-Based Access)
```

---

## 💻 Panduan Instalasi & Menjalankan Proyek

### Prasyarat Sistem
- PHP >= 8.2 (dengan ekstensi `pdo`, `mbstring`, `openssl`, `curl`, `gd`)
- Composer >= 2.x
- Node.js >= 18.x & NPM
- Git

### Langkah Instalasi

1. **Klon Repositori**:
   ```bash
   git clone https://github.com/Alwafaa70/Aplikasi-POS-RumahKuota.git
   cd Aplikasi-POS-RumahKuota
   ```

2. **Pasang Dependensi PHP (Composer)**:
   ```bash
   composer install
   ```

3. **Pasang Dependensi Frontend & Build Aset**:
   ```bash
   npm install
   npm run build
   ```

4. **Konfigurasi Berkas Lingkungan (`.env`)**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Migrasi dan Seed Data Awal Basis Data**:
   ```bash
   php artisan migrate --seed
   ```
   *(Opsional) Untuk memuat data simulasi historis penjualan 6 bulan penuh:*
   ```bash
   php artisan db:seed --class=CompleteSalesHistorySeeder
   php artisan ews:auto-calculate
   ```

6. **Jalankan Peladen Lokal**:
   ```bash
   php artisan serve
   ```
   Buka peramban dan akses: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🔑 Akun Bawaan untuk Pengujian (Default Credentials)

| Role Pengguna | Username | Password | Lingkup Hak Akses |
| :--- | :---: | :---: | :--- |
| **Owner (Pemilik)** | `owner` | `password` | Akses penuh seluruh cabang, Laboratorium SES, Monitoring EWS, Keuangan Global |
| **Admin Gudang** | `admin` | `password` | Manajemen stok Gudang Pusat, PO Supplier, Pengiriman Mutasi Cabang |
| **Kasir Cabang** | `kasir1` | `password` | Transaksi POS Cabang 1, Setoran Kasir, Blind Closing Harian |

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah lisensi terbuka [MIT License](LICENSE).

---

<p align="center">
  Dikembangkan dengan dedikasi untuk memajukan efisiensi industri ritel multi-cabang di Indonesia 🇮🇩
</p>
