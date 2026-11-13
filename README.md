# 🛒 Rumah Kuota POS — Multi-Branch Retail Information System & Smart Inventory Management

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11" />
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js" />
  <img src="https://img.shields.io/badge/PWA-Offline--First-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white" alt="PWA Offline First" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License MIT" />
</p>

<p align="center">
  <a href="#-english-version"><b>English</b></a> • <a href="#-versi-bahasa-indonesia"><b>Bahasa Indonesia</b></a>
</p>

---

## 🌐 English Version

### 📌 Executive Summary

**Rumah Kuota POS** is an enterprise-grade multi-branch retail management and Point of Sale (POS) platform integrated with an **Intelligent Inventory Control System**. 

The application is engineered to resolve two fundamental inventory risks in multi-location telecommunications and electronics accessories retail:
1. **Stockouts (Lost Sales)**: Prevented on high-velocity (*Fast-Moving*) items through real-time predictive demand threshold alerts.
2. **Overstocking (Dead Capital)**: Eliminated on low-velocity (*Slow-Moving*) items, preserving cash flow and preventing expiration of physical vouchers.

The system combines statistical **Single Exponential Smoothing (SES)** time-series forecasting with a **Dynamic Buffer Early Warning System (EWS)** governed by **FSN Analysis (*Fast, Medium, Slow-Moving*)** and empirical **7-Day Supply Chain Lead Time**.

---

### 🚀 Core Architecture & Key Features

#### 🏪 1. Multi-Branch & Central Warehouse Distribution
- **Hub-and-Spoke Logistics Architecture**: Centralized warehouse coordinates bulk vendor procurement, centralized stock storage, and inter-branch distribution across 9 operational retail outlets.
- **Inter-Branch Stock Transfer with Two-Way Verification**: Real-time stock mutation with dispatch and reception acknowledgment to maintain end-to-end ledger integrity.
- **Supplier Procurement (PO)**: Automated Purchase Order lifecycle from shortage triggers to goods receiving (*inbound processing*).

#### ⚡ 2. Offline-First Progressive Web App (PWA)
- **Zero-Downtime Offline Checkout**: POS terminals remain operational during internet disruptions via **Service Worker**, **Cache Storage API**, and client-side **IndexedDB (W3C Standard)**.
- **Automated Background Sync**: Offline sales are cryptographically validated and synced with the central cloud database immediately upon network restoration.

#### 📈 3. Single Exponential Smoothing (SES) Research Lab
- **Adaptive Mathematical Demand Forecasting**: Computes expected demand by exponentially discounting historical sales observations:
  $$F_{t+1} = \alpha Y_t + (1 - \alpha) F_t$$
- **Automated Parameter Optimization (Grid Search)**: Iterates across 9 smoothing values $\alpha \in \{0.1, 0.2, \dots, 0.9\}$ to automatically select the optimal parameter yielding the lowest Mean Absolute Deviation (MAD).
- **Global Accuracy Benchmarking (C.D. Lewis, 1982)**: Evaluates forecast precision against international Mean Absolute Percentage Error (MAPE) criteria:
  - $\text{MAPE} < 10\%$: **Highly Accurate**
  - $10\% \le \text{MAPE} \le 20\%$: **Good Forecasting**
  - $20\% < \text{MAPE} \le 50\%$: **Reasonable Forecasting**
  - $\text{MAPE} > 50\%$: **Inaccurate / Extreme Volatility**
- **Robustness Against Zero-Sales Days (Store Closures)**: Mathematical graceful decay prevents zero-division runtime errors and dampens predictions smoothly during scheduled closures or inventory audits.

#### 🛡️ 4. Dynamic Buffer Early Warning System (EWS)
- **Dynamic Thresholding**: Replaces arbitrary manual minimum levels with mathematically calculated triggers:
  $$\text{EWS Critical Threshold} = \text{Daily Velocity (SES)} \times \text{Procurement Lead Time (7 Days)}$$
- **Adaptive FSN Buffer Allocation**:
  - **Fast-Moving**: High velocity $\rightarrow$ **30% Safety Stock** buffer absorbs unexpected demand surges.
  - **Medium-Moving**: Standard velocity $\rightarrow$ **20% Safety Stock** balance between availability and holding costs.
  - **Slow-Moving**: Low velocity $\rightarrow$ **10% Safety Stock** minimized to protect working capital.
- **Precision Reorder Calculation**:
  $$\text{Restock Recommendation} = \max\Big(0, \big(\text{Cycle Demand} + \text{Safety Stock}\big) - \text{Physical On-Hand Stock}\Big)$$
- **Deep-Linked One-Click Restock**: Direct shortcuts automatically pre-fill supplier PO forms (for Central Warehouse) or transfer dispatches (for Retail Branches).

#### 💰 5. Financial Governance & Blind Closing Audit
- **Multi-Source Cash Flow**: Segregates physical drawer cash, bank transfers, QRIS, and third-party digital gateway balances.
- **Blind Closing Protocol**: Cashiers submit physical drawer counts without viewing theoretical system totals. Discrepancies (*cash variance*) are automatically computed to prevent internal skimming.
- **Automated PDF Reports**: Official calculation sheets, audit trails, and executive summaries generated via DomPDF.

---

### 🏗️ Tech Stack

| Domain | Technology | Description |
| :--- | :--- | :--- |
| **Backend** | Laravel 11.x (PHP 8.2+) | MVC Architecture, Eloquent ORM, RESTful API |
| **Frontend** | Blade + Tailwind CSS 3.x | Lightweight, responsive utility-first UI |
| **Reactivity** | Alpine.js 3.x | Declarative client-side UI state management |
| **Data Visualization** | ApexCharts.js | Goodness-of-Fit curves comparing actual sales vs fitted forecasts |
| **Client Storage** | IndexedDB & Cache API | Offline PWA transactional database |
| **Server Storage** | MySQL / SQLite | Relational database with optimized indexing |
| **Reporting Engine**| DomPDF | Official audit report & EWS summary PDF generator |

---

## 🇮🇩 Versi Bahasa Indonesia

### 📌 Ringkasan Eksekutif

**Rumah Kuota POS** adalah platform sistem informasi manajemen ritel dan kasir (*Point of Sale*) multi-cabang terpadu yang memadukan operasional kasir lini depan dengan sistem **Pengendalian Persediaan Cerdas (*Smart Inventory Control*)**.

Aplikasi ini dirancang untuk menyelesaikan dua permasalahan fundamental pada bisnis ritel aksesoris dan produk telekomunikasi multi-lokasi:
1. **Kehabisan Stok (*Stockout*)**: Mengakibatkan hilangnya potensi omset penjualan (*lost sales*) pada barang-barang yang sangat laris (*fast-moving*).
2. **Penumpukan Stok Berlebih (*Overstock*)**: Mengakibatkan pembekuan modal kerja toko (*dead capital*) dan risiko voucher fisik kedaluwarsa pada barang yang lambat laku (*slow-moving*).

Solusi yang diterapkan adalah penggabungan peramalan deret waktu **Single Exponential Smoothing (SES)** dengan sistem alarm persediaan **Dynamic Buffer Early Warning System (EWS)** berbasis **Analisis FSN (*Fast, Medium, Slow-Moving*)** dan **Siklus Pengadaan 7 Hari (*Lead Time*)**.

---

### 🚀 Fitur Unggulan

#### 🏪 1. Multi-Branch & Central Warehouse Management
- **Struktur Hierarki Logistik**: Koordinasi antara **Gudang Pusat** (pengadaan skala besar dari supplier) dengan **Toko Cabang** (gerai penjualan lini depan).
- **Mutasi Stok Antar-Cabang**: Pengiriman dan transfer barang antar-cabang dengan mekanisme verifikasi persetujuan dua arah (*two-way confirmation approval*).
- **Purchase Order (PO) Supplier**: Manajemen pemesanan barang ke pemasok terintegrasi dengan pencatatan barang masuk (*inbound tracking*).

#### ⚡ 2. Offline-First Progressive Web App (PWA)
- **Operasional Tanpa Hambatan Internet**: Kasir tetap dapat melakukan transaksi penjualan saat koneksi internet terputus berkat implementasi **Service Worker**, **Cache Storage API**, dan **IndexedDB (W3C)** di sisi klien.
- **Background Synchronization**: Data transaksi offline secara otomatis disinkronisasikan ke peladen pusat saat koneksi internet pulih tanpa risiko duplikasi data.

#### 📈 3. Laboratorium Riset Single Exponential Smoothing (SES)
- **Model Matematis Adaptif**: Menghitung proyeksi kebutuhan stok periode mendatang dengan meredam fluktuasi permintaan masa lalu:
  $$F_{t+1} = \alpha Y_t + (1 - \alpha) F_t$$
- **Optimasi Parameter Otomatis (*Grid Search*)**: Sistem secara otomatis menguji 9 variasi konstanta pemulusan $\alpha \in \{0.1, 0.2, \dots, 0.9\}$ dan memilih nilai $\alpha$ terbaik berdasarkan deviasi kesalahan terendah (*Mean Absolute Deviation* / MAD).
- **Standar Evaluasi Akurasi Internasional (C.D. Lewis, 1982)**: Evaluasi galat *Mean Absolute Percentage Error* (MAPE) diklasifikasikan secara transparan:
  - $\text{MAPE} < 10\%$: **Sangat Akurat (*Highly Accurate*)**
  - $10\% \le \text{MAPE} \le 20\%$: **Baik / Akurat (*Good Forecasting*)**
  - $20\% < \text{MAPE} \le 50\%$: **Layak / Cukup (*Reasonable Forecasting*)**
  - $\text{MAPE} > 50\%$: **Kurang Akurat (*Inaccurate*)**
- **Ketahanan Terhadap Hari Libur / Zero-Sales**: Secara matematis peramalan mengalami peredaman eksponensial halus (*smooth exponential decay*) saat toko libur tanpa terjadi *crash* pembagian dengan nol.

#### 🛡️ 4. Dynamic Buffer Early Warning System (EWS)
- **Bukan Buffer Statis**: Berbeda dengan sistem konvensional yang mematok angka batas pengaman tetap sama rata, sistem ini menghitung **Batas Kritis EWS secara dinamis**:
  $$\text{Batas Kritis EWS} = \text{Laju Tren Harian (SES)} \times \text{Lead Time Pengadaan (7 Hari)}$$
- **Cadangan Adaptif Berbasis Analisis FSN**:
  - **Fast-Moving**: Penjualan tinggi $\rightarrow$ Buffer pengaman **30%** untuk menahan lonjakan pembeli mendadak.
  - **Medium-Moving**: Penjualan stabil $\rightarrow$ Buffer pengaman **20%** proporsional standar ritel.
  - **Slow-Moving**: Penjualan lambat $\rightarrow$ Buffer ditekan **10%** agar dana modal tidak tertimbun.
- **Rekomendasi Restok Presisi**:
  $$\text{Rekomendasi Restok} = \max\Big(0, \big(\text{Prediksi Kebutuhan Siklus} + \text{Safety Stock}\big) - \text{Sisa Stok Fisik}\Big)$$
- **Aksi Cepat Restok (*Quick Action Deep-Link*)**: Tombol tindakan sekali klik langsung mengarahkan ke form PO Supplier (untuk Gudang Pusat) atau Kirim Mutasi Stok (untuk Cabang) dengan jumlah barang yang telah terisi otomatis.

#### 💰 5. Pengendalian Keuangan & Blind Closing
- **Kas Multi-Akun**: Pemisahan pencatatan kas tunai laci (*cash drawer*), rekening bank, serta saldo aplikasi layanan pihak ketiga (Digipos, dsb.).
- **Pengendalian Kas Internal (*Blind Closing*)**: Kasir wajib menghitung dan menginput fisik uang tunai tanpa melihat saldo kalkulasi sistem untuk mencegah manipulasi data kasir (*skimming*). Selisih kas terdeteksi secara otomatis.
- **Laporan Laba Rugi Riil & Proyeksi Laba**: Analisis profitabilitas berbasis Harga Pokok Penjualan (HPP) per transaksi.

---

## 📂 Struktur Direktori Utama (Project Structure)

```text
aplikasi-pos/
├── app/
│   ├── Http/Controllers/        # Logic controllers (POS, Owner, EWS, Admin, Mutasi, Kas)
│   ├── Models/                 # Eloquent ORM models (Barang, Cabang, Transaksi, Pengaturan EWS, etc.)
│   └── Services/               # Core domain services:
│       ├── SesForecastingService.php   # SES Algorithm, Alpha Grid Search, MAD & MAPE
│       └── EwsService.php              # EWS Threshold Engine & FSN Classification
├── database/
│   ├── migrations/             # Database schema migrations
│   └── seeders/                # 6-Month historical sales dataset & store profiles
├── public/
│   ├── service-worker.js       # PWA offline cache engine & background sync
│   └── manifest.json           # Web App Manifest
├── resources/
│   └── views/                  # Blade templates:
│       ├── owner/              # Owner Dashboard, SES Research Lab, EWS Monitor
│       ├── admin/              # Central Warehouse Dashboard, Supplier PO
│       ├── kasir/              # POS Checkout & Daily Blind Closing
│       └── distribusi/         # Inter-Branch Logistics Transfer
└── routes/
    └── web.php                 # Role-based route definitions
```

---

## 💻 Panduan Instalasi & Menjalankan Proyek (Getting Started)

### Prerequisites
- PHP >= 8.2 (`pdo`, `mbstring`, `openssl`, `curl`, `gd` extensions enabled)
- Composer >= 2.x
- Node.js >= 18.x & NPM
- Git

### Quick Setup

```bash
# 1. Clone repository
git clone https://github.com/Alwafaa70/Aplikasi-POS-RumahKuota.git
cd Aplikasi-POS-RumahKuota

# 2. Install PHP dependencies
composer install

# 3. Install frontend packages & build assets
npm install
npm run build

# 4. Environment configuration
cp .env.example .env
php artisan key:generate

# 5. Database setup & initial seeding
php artisan migrate --seed

# (Optional) Seed realistic 6-month historical dataset and calculate EWS:
php artisan db:seed --class=CompleteSalesHistorySeeder
php artisan ews:auto-calculate

# 6. Run local development server
php artisan serve
```

Visit: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🔑 Default Credentials

| Role | Username | Password | Scope |
| :--- | :---: | :---: | :--- |
| **Owner** | `owner` | `password` | Full enterprise control, SES Research Lab, EWS Engine, Global Financials |
| **Admin Gudang** | `admin` | `password` | Central Warehouse inventory, Supplier Purchase Orders, Logistics dispatch |
| **Kasir Cabang** | `kasir1` | `password` | Point of Sale (POS) Cabang 1, Daily Blind Closing |

---

## 📄 License

This software is licensed under the open-source [MIT License](LICENSE).

---

<p align="center">
  Crafted with dedication for modern retail enterprise excellence 🇮🇩
</p>
